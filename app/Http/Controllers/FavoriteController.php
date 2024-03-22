<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        // Retrieve the currently authenticated user
        $user = auth()->user();

        // Retrieve favorites associated with the authenticated user and order them by ID
        $favorites = Favorite::where('user_id', $user->id)->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'favorites' => $favorites,
        ]);
    }

    public function create(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'article_id' => 'required|integer',
        ]);

        // Create a new favorite
        $favorite = Favorite::create($validatedData);

        return response()->json([
            'status' => 'success',
            'favorite' => $favorite,
            'message' => 'Article ajouté en favoris'
        ]);
    }

    public function destroy($id)
    {
        // Find the favorite by ID
        $favorite = Favorite::findOrFail($id);

        // Delete the favorite
        $favorite->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Favorite deleted successfully',
        ]);
    }
}
