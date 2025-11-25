<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class AuthController
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => '',
            'email' => '',
            'password' => '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
public function checkUser(Request $request)
{
    $email = $request->query('email');
    $name = $request->query('name');

    $emailExists = User::where('email', $email)->exists();
    $nameExists = User::where('name', $name)->exists();

    return response()->json([
        'email_exists' => $emailExists,
        'name_exists' => $nameExists
    ]);
}
}

