<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function editProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            // 'birthday' => 'nullable|date',
            // 'country' => 'nullable|string|max:255',
            // 'city' => 'nullable|string|max:255',
            // 'description' => 'nullable|string',
        ]);

        // Update the user's information
        $user->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil modifié avec succès',
            'user' => $user->fresh(), // Return updated user data
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Check if the current password matches
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mot de passe incorrect',
            ], 401);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe modifié avec succès',
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
