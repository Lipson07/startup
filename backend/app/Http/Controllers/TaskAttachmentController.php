<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Task_attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachmentController
{
    public function index(Task $task)
    {
        $attachments = $task
            ->attachments()
            ->with("user")
            ->orderBy("created_at", "desc")
            ->paginate(20);

        return response()->json([
            "success" => true,
            "data" => $attachments,
        ]);
    }

    public function show(Task $task, Task_attachment $attachment)
    {
        if ($attachment->task_id !== $task->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Attachment not found for this task",
                ],
                404,
            );
        }

        $attachment->load("user");

        return response()->json([
            "success" => true,
            "data" => $attachment,
        ]);
    }

    public function store(Request $request, Task $task)
    {
        $request->validate([
            "file" => "required|file|max:10240",
            "title" => "nullable|string|max:255",
            "description" => "nullable|string",
            "is_public" => "boolean",
        ]);

        $file = $request->file("file");
        $user = Auth::user();

        $type = $this->determineFileType(
            $file->getMimeType(),
            $file->getClientOriginalExtension(),
        );

        $fileName = Str::uuid() . "." . $file->getClientOriginalExtension();
        $storagePath = "task_attachments/" . date("Y/m/d") . "/" . $fileName;

        $disk = config("filesystems.default", "local");
        Storage::disk($disk)->put($storagePath, file_get_contents($file));

        $width = null;
        $height = null;
        $thumbnailPath = null;

        if (str_starts_with($file->getMimeType(), "image/")) {
            [$width, $height] = getimagesize($file->getPathname());

            $thumbnailPath = $this->generateThumbnail($file, $disk);
        }

        $attachment = Task_attachment::create([
            "task_id" => $task->id,
            "user_id" => $user->id,
            "original_filename" => $file->getClientOriginalName(),
            "storage_path" => $storagePath,
            "disk" => $disk,
            "mime_type" => $file->getMimeType(),
            "size" => $file->getSize(),
            "type" => $type,
            "width" => $width,
            "height" => $height,
            "thumbnail_path" => $thumbnailPath,
            "title" =>
                $request->title ??
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            "description" => $request->description,
            "is_public" => $request->boolean("is_public", false),
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "File uploaded successfully",
                "data" => $attachment->load("user"),
            ],
            201,
        );
    }

    public function storeMultiple(Request $request, Task $task)
    {
        $request->validate([
            "files" => "required|array",
            "files.*" => "file|max:10240",
            "is_public" => "boolean",
        ]);

        $user = Auth::user();
        $uploadedAttachments = [];

        foreach ($request->file("files") as $file) {
            $type = $this->determineFileType(
                $file->getMimeType(),
                $file->getClientOriginalExtension(),
            );

            $fileName = Str::uuid() . "." . $file->getClientOriginalExtension();
            $storagePath =
                "task_attachments/" . date("Y/m/d") . "/" . $fileName;

            $disk = config("filesystems.default", "local");
            Storage::disk($disk)->put($storagePath, file_get_contents($file));

            $width = null;
            $height = null;
            $thumbnailPath = null;

            if (str_starts_with($file->getMimeType(), "image/")) {
                [$width, $height] = getimagesize($file->getPathname());

                $thumbnailPath = $this->generateThumbnail($file, $disk);
            }

            $attachment = Task_attachment::create([
                "task_id" => $task->id,
                "user_id" => $user->id,
                "original_filename" => $file->getClientOriginalName(),
                "storage_path" => $storagePath,
                "disk" => $disk,
                "mime_type" => $file->getMimeType(),
                "size" => $file->getSize(),
                "type" => $type,
                "width" => $width,
                "height" => $height,
                "thumbnail_path" => $thumbnailPath,
                "title" => pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME,
                ),
                "is_public" => $request->boolean("is_public", false),
            ]);

            $uploadedAttachments[] = $attachment;
        }

        return response()->json(
            [
                "success" => true,
                "message" =>
                    count($uploadedAttachments) .
                    " files uploaded successfully",
                "data" => $uploadedAttachments,
            ],
            201,
        );
    }

    public function update(
        Request $request,
        Task $task,
        Task_attachment $attachment,
    ) {
        if ($attachment->task_id !== $task->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Attachment not found for this task",
                ],
                404,
            );
        }

        $request->validate([
            "title" => "nullable|string|max:255",
            "description" => "nullable|string",
            "is_public" => "boolean",
        ]);

        $attachment->update([
            "title" => $request->title ?? $attachment->title,
            "description" => $request->description ?? $attachment->description,
            "is_public" => $request->has("is_public")
                ? $request->boolean("is_public")
                : $attachment->is_public,
        ]);

        return response()->json([
            "success" => true,
            "message" => "Attachment updated successfully",
            "data" => $attachment->load("user"),
        ]);
    }

    public function download(Task $task, Task_attachment $attachment)
    {
        if ($attachment->task_id !== $task->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Attachment not found for this task",
                ],
                404,
            );
        }

        if (!$attachment->is_public && Auth::id() !== $attachment->user_id) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "You do not have permission to download this file",
                ],
                403,
            );
        }

        if (
            !Storage::disk($attachment->disk)->exists($attachment->storage_path)
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "File not found on storage",
                ],
                404,
            );
        }

        $attachment->increment("download_count");

        return Storage::disk($attachment->disk)->download(
            $attachment->storage_path,
            $attachment->original_filename,
        );
    }

    public function thumbnail(Task $task, Task_attachment $attachment)
    {
        if ($attachment->task_id !== $task->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Attachment not found for this task",
                ],
                404,
            );
        }

        if (!$attachment->thumbnail_path) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Thumbnail not available for this file",
                ],
                404,
            );
        }

        if (
            !Storage::disk($attachment->disk)->exists(
                $attachment->thumbnail_path,
            )
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Thumbnail file not found",
                ],
                404,
            );
        }

        $mimeType = Storage::disk($attachment->disk)->mimeType(
            $attachment->thumbnail_path,
        );
        $fileContents = Storage::disk($attachment->disk)->get(
            $attachment->thumbnail_path,
        );

        return response($fileContents, 200)->header("Content-Type", $mimeType);
    }

    public function destroy(Task $task, Task_attachment $attachment)
    {
        if ($attachment->task_id !== $task->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Attachment not found for this task",
                ],
                404,
            );
        }

        if (
            Auth::id() !== $attachment->user_id &&
            !Auth::user()->hasRole("admin")
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "You do not have permission to delete this file",
                ],
                403,
            );
        }

        if (
            Storage::disk($attachment->disk)->exists($attachment->storage_path)
        ) {
            Storage::disk($attachment->disk)->delete($attachment->storage_path);
        }

        if (
            $attachment->thumbnail_path &&
            Storage::disk($attachment->disk)->exists(
                $attachment->thumbnail_path,
            )
        ) {
            Storage::disk($attachment->disk)->delete(
                $attachment->thumbnail_path,
            );
        }

        $attachment->delete();

        return response()->json([
            "success" => true,
            "message" => "Attachment deleted successfully",
        ]);
    }

    private function determineFileType(
        string $mimeType,
        string $extension,
    ): string {
        // Изображения
        if (str_starts_with($mimeType, "image/")) {
            return "image";
        }

        // Документы
        $documentMimes = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-powerpoint",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
            "text/plain",
            "text/html",
            "text/csv",
        ];

        $documentExtensions = [
            "pdf",
            "doc",
            "docx",
            "xls",
            "xlsx",
            "ppt",
            "pptx",
            "txt",
            "html",
            "csv",
        ];

        if (
            in_array($mimeType, $documentMimes) ||
            in_array(strtolower($extension), $documentExtensions)
        ) {
            return "document";
        }

        $archiveMimes = [
            "application/zip",
            "application/x-rar-compressed",
            "application/x-tar",
            "application/x-7z-compressed",
            "application/gzip",
        ];

        $archiveExtensions = ["zip", "rar", "tar", "7z", "gz"];

        if (
            in_array($mimeType, $archiveMimes) ||
            in_array(strtolower($extension), $archiveExtensions)
        ) {
            return "archive";
        }

        return "other";
    }

    private function generateThumbnail($file, string $disk): ?string
    {
        try {
            $image = null;

            switch ($file->getMimeType()) {
                case "image/jpeg":
                    $image = imagecreatefromjpeg($file->getPathname());
                    break;
                case "image/png":
                    $image = imagecreatefrompng($file->getPathname());
                    break;
                case "image/gif":
                    $image = imagecreatefromgif($file->getPathname());
                    break;
                case "image/webp":
                    $image = imagecreatefromwebp($file->getPathname());
                    break;
                default:
                    return null;
            }

            if (!$image) {
                return null;
            }

            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            $maxWidth = 200;
            $maxHeight = 200;

            $ratio = $originalWidth / $originalHeight;

            if ($maxWidth / $maxHeight > $ratio) {
                $newWidth = $maxHeight * $ratio;
                $newHeight = $maxHeight;
            } else {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            }

            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

            if (
                $file->getMimeType() === "image/png" ||
                $file->getMimeType() === "image/gif"
            ) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha(
                    $thumbnail,
                    255,
                    255,
                    255,
                    127,
                );
                imagefilledrectangle(
                    $thumbnail,
                    0,
                    0,
                    $newWidth,
                    $newHeight,
                    $transparent,
                );
            }

            imagecopyresampled(
                $thumbnail,
                $image,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            $tempPath = tempnam(sys_get_temp_dir(), "thumb_");

            switch ($file->getMimeType()) {
                case "image/jpeg":
                    imagejpeg($thumbnail, $tempPath, 85);
                    $extension = "jpg";
                    break;
                case "image/png":
                    imagepng($thumbnail, $tempPath, 8);
                    $extension = "png";
                    break;
                case "image/gif":
                    imagegif($thumbnail, $tempPath);
                    $extension = "gif";
                    break;
                case "image/webp":
                    imagewebp($thumbnail, $tempPath, 85);
                    $extension = "webp";
                    break;
            }

            $thumbnailFileName = Str::uuid() . "_thumb." . $extension;
            $thumbnailPath =
                "task_attachments/thumbnails/" .
                date("Y/m/d") .
                "/" .
                $thumbnailFileName;

            Storage::disk($disk)->put(
                $thumbnailPath,
                file_get_contents($tempPath),
            );

            imagedestroy($image);
            imagedestroy($thumbnail);

            unlink($tempPath);

            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::error("Thumbnail generation failed: " . $e->getMessage());
            return null;
        }
    }
}
