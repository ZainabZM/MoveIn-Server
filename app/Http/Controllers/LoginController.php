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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
