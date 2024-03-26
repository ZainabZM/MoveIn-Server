<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    public function register(Request $request, User $user)
    {
        // Vérifie que tous les champs soient bien renseignés
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:11|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{11,}$/',
        ]);

        // S'il manque un ou plusieurs champs
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => $validator->errors()
            ]);

            // Si tous les champs sont bien renseignés
        } else {
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->lastname,
                'email' => $request->email,
                'password' => $request->password,
            ]);



            // Renvoie les informations du user en format json
            return response()->json([
                'status' => 'true',
                'message' => 'Utilisateur inscrit avec succès yeeesssss',
                'user' => $user,
            ]);
        }
    }
}
