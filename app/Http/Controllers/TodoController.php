<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TodoController extends Controller
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = storage_path('todos.json');
    }

    private function readTodos()
    {
        if (!File::exists($this->filePath)) {
            File::put($this->filePath, json_encode([]));
        }
        return json_decode(File::get($this->filePath), true);
    }

    private function writeTodos($todos)
    {
        File::put($this->filePath, json_encode($todos, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $todos = $this->readTodos();
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $todos
        ]);
    }

    public function show($id)
    {
        $todos = $this->readTodos();
        $todo = collect($todos)->firstWhere('id', (int)$id);

        if (!$todo) {
            return response()->json([
                'status' => 'error',
                'message' => 'To-do with the given ID not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $todo
        ]);
    }

    public function store(Request $request)
    {
        $todos = $this->readTodos();

        $newTodo = [
            'id' => count($todos) > 0 ? end($todos)['id'] + 1 : 1,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'completed' => false,
            'dueDate' => $request->input('dueDate'),
            'createdAt' => now()->toISOString()
        ];

        $todos[] = $newTodo;
        $this->writeTodos($todos);

        return response()->json([
            'status' => 'success',
            'message' => 'To-do created successfully',
            'data' => $newTodo
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $todos = $this->readTodos();
        $index = collect($todos)->search(fn ($todo) => $todo['id'] == (int)$id);

        if ($index === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'To-do with the given ID not found'
            ], 404);
        }

        $todos[$index] = array_merge($todos[$index], $request->only(['title', 'description', 'completed', 'dueDate']));
        $this->writeTodos($todos);

        return response()->json([
            'status' => 'success',
            'message' => 'To-do updated successfully',
            'data' => $todos[$index]
        ]);
    }

    public function destroy($id)
    {
        $todos = $this->readTodos();
        $index = collect($todos)->search(fn ($todo) => $todo['id'] == (int)$id);

        if ($index === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'To-do with the given ID not found'
            ], 404);
        }

        $deleted = $todos[$index];
        array_splice($todos, $index, 1);
        $this->writeTodos($todos);

        return response()->json([
            'status' => 'success',
            'message' => 'To-do deleted successfully',
            'data' => $deleted
        ]);
    }
}