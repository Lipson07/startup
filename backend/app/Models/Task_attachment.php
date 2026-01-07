<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task_attachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "task_id",
        "user_id",
        "original_filename",
        "storage_path",
        "disk",
        "mime_type",
        "size",
        "type",
        "width",
        "height",
        "thumbnail_path",
        "title",
        "description",
        "download_count",
        "is_public",
    ];

    protected $casts = [
        "size" => "integer",
        "download_count" => "integer",
        "width" => "integer",
        "height" => "integer",
        "is_public" => "boolean",
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, "task_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function attachments()
    {
        return $this->hasMany(Task_attachment::class, "task_id");
    }
}
