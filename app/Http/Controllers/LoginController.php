<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        // Si l'email est présent dans la db ET si le mot de passe correspond
        if ($user && Hash::check($request->password, $user->password)) {
            // Utilisation de Sanctum pour créer un jeton d'accès
            $token = $user->createToken(time())->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'Vous êtes connecté(e)'
            ]);
        } else {
            return response()->json(['error' => 'Email ou mot de passe incorrect'], 401);
        }
    }
}
