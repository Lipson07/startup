<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\GitHubProjectController;
use App\Http\Controllers\SubscriptionController;
Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");

Route::prefix("users")->group(function () {
    Route::post("/register", [AuthController::class, "create"]);
    Route::post("/login", [AuthController::class, "login"]);
    Route::get("/check-user", [AuthController::class, "checkUser"]);

    Route::get("/", [AuthController::class, "getAllUsers"]);
    Route::get("/{id}", [AuthController::class, "getUser"]);
    Route::put("/{id}", [AuthController::class, "updateUser"]);
    Route::delete("/{id}", [AuthController::class, "deleteUser"]);
});
Route::prefix("projects")->group(function () {
    Route::post("/create", [ProjectController::class, "create"]);
    Route::get("/", [ProjectController::class, "show"]);
    Route::get("/{id}", [ProjectController::class, "showById"]);
    Route::put("/{id}", [ProjectController::class, "update"]);
    Route::delete("/delete", [ProjectController::class, "destroy"]);
    Route::post("/{projectId}/members", [
        ProjectController::class,
        "addMember",
    ]);
    Route::delete("/{projectId}/members/{userId}", [
        ProjectController::class,
        "removeMember",
    ]);
    Route::put("/{projectId}/members/{userId}/role", [
        ProjectController::class,
        "updateMemberRole",
    ]);
    Route::get("/{projectId}/tasks", [ProjectController::class, "getTasks"]);
    Route::get("/{projectId}/statistics", [
        ProjectController::class,
        "getStatistics",
    ]);
});
Route::prefix("tasks")->group(function () {
    Route::get("/", [TaskController::class, "index"]);
    Route::post("/", [TaskController::class, "store"]);
    Route::get("/search", [TaskController::class, "search"]);
    Route::get("/upcoming", [TaskController::class, "upcoming"]);
    Route::get("/{id}", [TaskController::class, "show"]);
    Route::put("/{id}", [TaskController::class, "update"]);
    Route::delete("/{id}", [TaskController::class, "destroy"]);
    Route::post("/{id}/complete", [TaskController::class, "complete"]);
});

Route::prefix("tasks/{task}/attachments")->group(function () {
    Route::get("/", [TaskAttachmentController::class, "index"]);
    Route::post("/", [TaskAttachmentController::class, "store"]);
    Route::post("/multiple", [
        TaskAttachmentController::class,
        "storeMultiple",
    ]);
    Route::get("/{attachment}", [TaskAttachmentController::class, "show"]);
    Route::put("/{attachment}", [TaskAttachmentController::class, "update"]);
    Route::delete("/{attachment}", [
        TaskAttachmentController::class,
        "destroy",
    ]);
    Route::get("/{attachment}/download", [
        TaskAttachmentController::class,
        "download",
    ]);
    Route::get("/{attachment}/thumbnail", [
        TaskAttachmentController::class,
        "thumbnail",
    ]);
});
Route::prefix("github-projects")->group(function () {
    Route::get("/", [GitHubProjectController::class, "index"]);
    Route::get("/{id}", [GitHubProjectController::class, "show"]);
    Route::post("/", [GitHubProjectController::class, "store"]);
    Route::put("/{id}", [GitHubProjectController::class, "update"]);
    Route::delete("/{id}", [GitHubProjectController::class, "destroy"]);
    Route::post("/bulk-import", [GitHubProjectController::class, "bulkImport"]);
    Route::post("/sync/{id}", [
        GitHubProjectController::class,
        "syncWithGitHub",
    ]);
    Route::get("/search", [GitHubProjectController::class, "search"]);
    Route::get("/filter", [GitHubProjectController::class, "filter"]);
    Route::get("/language/{language}", [
        GitHubProjectController::class,
        "byLanguage",
    ]);
    Route::get("/owner/{ownerLogin}", [
        GitHubProjectController::class,
        "byOwner",
    ]);
    Route::get("/popular", [GitHubProjectController::class, "popular"]);
    Route::get("/recent", [GitHubProjectController::class, "recentlyUpdated"]);
    Route::get("/trending", [GitHubProjectController::class, "trending"]);
    Route::get("/{id}/users", [GitHubProjectController::class, "getUsers"]);
    Route::post("/{id}/users", [GitHubProjectController::class, "addUser"]);
    Route::delete("/{id}/users/{userId}", [
        GitHubProjectController::class,
        "removeUser",
    ]);
    Route::put("/{id}/users/{userId}/role", [
        GitHubProjectController::class,
        "updateUserRole",
    ]);
    Route::get("/{id}/tags", [GitHubProjectController::class, "getTags"]);
    Route::post("/{id}/tags", [GitHubProjectController::class, "addTag"]);
    Route::delete("/{id}/tags/{tag}", [
        GitHubProjectController::class,
        "removeTag",
    ]);
    Route::get("/tag/{tag}", [GitHubProjectController::class, "byTag"]);
    Route::get("/stats/overall", [
        GitHubProjectController::class,
        "overallStats",
    ]);
    Route::get("/stats/languages", [
        GitHubProjectController::class,
        "languageStats",
    ]);
    Route::get("/stats/top-projects", [
        GitHubProjectController::class,
        "topProjects",
    ]);
    Route::get("/stats/activity", [
        GitHubProjectController::class,
        "activityStats",
    ]);
    Route::get("/user/{userId}/projects", [
        GitHubProjectController::class,
        "getUserProjects",
    ]);
    Route::get("/my/{userId}", [GitHubProjectController::class, "myProjects"]);
    Route::post("/{id}/archive", [GitHubProjectController::class, "archive"]);
    Route::post("/{id}/restore", [GitHubProjectController::class, "restore"]);
    Route::get("/github/repo", [
        GitHubProjectController::class,
        "getRepoFromGitHub",
    ]);
});
Route::prefix("subscriptions")->group(function () {
    Route::post("/", [SubscriptionController::class, "create"]);
    Route::get("/", [SubscriptionController::class, "getAll"]);
    Route::get("/default", [SubscriptionController::class, "getDefault"]);
    Route::get("/{id}", [SubscriptionController::class, "get"]);
    Route::put("/{id}", [SubscriptionController::class, "update"]);
    Route::delete("/{id}", [SubscriptionController::class, "delete"]);
    Route::get("/{id}/users", [SubscriptionController::class, "getUsers"]);
});
