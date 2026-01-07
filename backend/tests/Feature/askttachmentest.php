<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Task_attachment;

class TaskAttachmentPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_post_route_exists_and_works()
    {
        // 1. Создаем тестового пользователя
        $user = User::create([
            "name" => "Test User",
            "email" => "test@example.com",
            "password" => bcrypt("password123"),
        ]);

        // 2. Создаем проект
        $project = Project::create([
            "name" => "Test Project",
            "description" => "Test Description",
            "created_by" => $user->id,
        ]);

        // 3. Создаем задачу
        $task = Task::create([
            "title" => "Test Task",
            "description" => "Task for testing attachments",
            "project_id" => $project->id,
            "created_by" => $user->id,
            "status" => "pending",
            "priority" => "medium",
        ]);

        // 4. Авторизуем пользователя
        $loginResponse = $this->postJson("/api/users/login", [
            "email" => "test@example.com",
            "password" => "password123",
        ]);

        $token = $loginResponse->json("token");

        // 5. Тестируем POST роут с файлом
        Storage::fake("local");

        $file = UploadedFile::fake()->image("test-image.jpg", 100, 100);

        $response = $this->withHeaders([
            "Authorization" => "Bearer " . $token,
        ])->post("/api/tasks/{$task->id}/attachments", [
            "file" => $file,
            "title" => "Test File",
            "description" => "Test description",
            "is_public" => true,
        ]);

        // 6. Проверяем ответ
        if ($response->status() === 404) {
            $this->fail("Route not found! Check your routes in routes/api.php");
        }

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                "success",
                "message",
                "data" => ["id", "task_id", "original_filename"],
            ]);

        // 7. Проверяем, что файл создан в базе
        $this->assertDatabaseHas("task_attachments", [
            "task_id" => $task->id,
            "user_id" => $user->id,
            "original_filename" => "test-image.jpg",
        ]);
    }

    /** @test */
    public function test_simple_post_test_without_auth()
    {
        // Простейший тест - проверяем, что роут вообще существует
        $response = $this->post("/api/tasks/1/attachments");

        // Должен вернуть 401 (Unauthorized) или 404 (если роут не существует)
        // Если 404 - значит роут не зарегистрирован
        if ($response->status() === 404) {
            $this->fail(
                "POST route /api/tasks/{task}/attachments does not exist!",
            );
        }

        $this->assertNotEquals(
            404,
            $response->status(),
            "Route not found. Check your routes in routes/api.php",
        );
    }

    /** @test */
    public function test_debug_route_registration()
    {
        // Проверяем, что контроллер существует
        $controllerExists = class_exists(
            "App\Http\Controllers\TaskAttachmentController",
        );
        $this->assertTrue(
            $controllerExists,
            "TaskAttachmentController does not exist",
        );

        // Проверяем, что метод существует
        $controller = new \App\Http\Controllers\TaskAttachmentController();
        $methodExists = method_exists($controller, "store");
        $this->assertTrue(
            $methodExists,
            "Method store() does not exist in TaskAttachmentController",
        );

        // Проверяем GET роут (он должен работать)
        $user = User::create([
            "name" => "Test User 2",
            "email" => "test2@example.com",
            "password" => bcrypt("password123"),
        ]);

        $project = Project::create([
            "name" => "Test Project 2",
            "created_by" => $user->id,
        ]);

        $task = Task::create([
            "title" => "Test Task 2",
            "project_id" => $project->id,
            "created_by" => $user->id,
        ]);

        $loginResponse = $this->postJson("/api/users/login", [
            "email" => "test2@example.com",
            "password" => "password123",
        ]);

        $token = $loginResponse->json("token");

        // Тестируем GET роут (должен работать)
        $getResponse = $this->withHeaders([
            "Authorization" => "Bearer " . $token,
        ])->get("/api/tasks/{$task->id}/attachments");

        // GET должен работать
        $this->assertNotEquals(
            404,
            $getResponse->status(),
            "GET route also returns 404! Check your route definition",
        );
    }
}
