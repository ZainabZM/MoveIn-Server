<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $userData = [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'birthday' => $user->birthday,
            'country' => $user->country,
            'city' => $user->city,
            'description' => $user->description,
        ];

        return response()->json([
            'status' => 'success',
            'user' => $userData,
        ]);
    }

    public function favorites($userId, $articleId)
    {
        // Find the user by ID
        $user = User::findOrFail($userId);

        // Add the article ID to the user's favorites list
        $user->favorites()->attach($articleId);

        return response()->json([
            'status' => 'success',
            'message' => 'Article added to favorites successfully',
        ]);
    }
}
