<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{

    // Récupération de tous les articles
    public function index()
    {
        $articles = Article::all();

        // Add full URL for each image
        $articles->transform(function ($article) {
            $article->file = asset("storage/images/{$article->file}");
            return $article;
        });

        return response()->json([
            'status' => 'true',
            'articles' => $articles,
        ]);
    }
    // Création d'un post
    public function create(Request $request)
    {

        $validator = $this->validateArticleRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => $validator->errors()
            ]);
        }

        $fileName = time() . '.' . $request->file->extension();

        $request->file->storeAs('public/images', $fileName);

        $article = $this->storeArticle($request, $fileName);

        $categories = $this->syncCategories($article, $request->category);

        return response()->json([
            'status' => 'true',
            'message' => 'Article créé avec succès',
            'categories' => $categories,
            'article' => $article,
        ]);
    }

    // Récupération d'un article spécifique
    public function show($id)
    {
        $article = Article::findOrFail($id);

        $article->file = asset('storage/images/' . $article->file);

        return response()->json([
            'status' => 'true',
            'article' => $article,
        ]);
    }
    // Modification d'un article
    public function edit($id)
    {
        $article = Article::findOrFail($id);

        return response()->json([
            'status' => 'true',
            'article' => $article,
        ]);
    }
    // Mise à jour d'un article
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'brand' => 'required',
            'color' => 'required',
            'state' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category' => 'required',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => $validator->errors()
            ]);
        }

        // Retrieve the article by ID
        $article = Article::findOrFail($id);

        // Check if the request contains a file
        if ($request->hasFile('file')) {
            // Validate the file
            $fileValidator = Validator::make($request->all(), [
                'file' => 'image|mimes:jpeg,png,jpg,gif,svg|max:3000',
            ]);

            // Check if file validation fails
            if ($fileValidator->fails()) {
                return response()->json([
                    'status' => 'false',
                    'data' => $fileValidator->errors()
                ]);
            }

            // Store the image file
            $fileName = time() . '.' . $request->file('file')->extension();
            $filePath = $request->file('file')->storeAs('public/images', $fileName);

            // Log the file name before updating
            Log::info('Old file name: ' . $article->file);

            // Update article's file field
            $article->file = $fileName;

            // Log the file name after updating
            Log::info('New file name: ' . $article->file);

            // Delete old file if it exists
            if ($article->file) {
                $oldFilePath = 'public/images/' . $article->file;
                if (Storage::exists($oldFilePath)) {
                    Storage::delete($oldFilePath);
                }
            }

            // Update article's file field
            $article->file = $fileName;
        }

        // Update other article fields
        $article->update($request->only(['title', 'brand', 'color', 'state', 'description', 'price']));


        // Sync article categories
        $this->syncCategories($article, $request->category);

        // Return success response
        return response()->json([
            'status' => 'true',
            'message' => 'Article mis à jour avec succès',
            'article' => $article,
        ]);
    }
    // Suppression d'un article
    public function destroy($id, Request $request)
    {
        $article = Article::findOrFail($id);
        // Check if the user ID matches the article's user ID
        if (Auth::id() !== $article->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this article.',
            ], 403); // Forbidden status code
        }
        $article->delete();

        return response()->json([
            'status' => 'true',
            'message' => 'Article deleted successfully',
        ]);
    }
    // Récupère les articles de l'utilisateur connecté
    public function userArticles()
    {
        $user_id = Auth::id();

        $articles = Article::where('user_id', $user_id)->get();

        $articles->transform(function ($article) {
            $article->file = asset("storage/images/{$article->file}");
            return $article;
        });

        return response()->json([
            'status' => 'true',
            'articles' => $articles,
        ]);
    }
    // Vérification du bon renseignement des champs requis 
    private function validateArticleRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required',
            'brand' => 'required',
            'color' => 'required',
            'state' => 'required',
            'description' => 'required',
            'price' => 'required',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:3000',
            'category' => 'required',
        ]);
    }
    // Création d'un article et stockage dans la db
    private function storeArticle(Request $request, $fileName)
    {
        $user_id = Auth::id();

        return Article::create([
            'title' => $request->title,
            'brand' => $request->brand,
            'color' => $request->color,
            'state' => $request->state,
            'description' => $request->description,
            'price' => $request->price,
            'file' => $fileName,
            'user_id' => $user_id,
        ]);
    }

    // Synchronisation des catégories
    private function syncCategories(Article $article, $categories)
    {
        $article->categories()->sync($categories);

        $article->load('categories');
        return $article->categories;
    }
}
