<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;

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
        $validator = $this->validateArticleRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => $validator->errors()
            ]);
        }

        $article = Article::findOrFail($id);
        $fileName = $this->storeImage($request->file('file')); // Change here
        $article->update($request->except(['file', 'category']));
        $article->update(['file' => $fileName]);
        $categories = $this->syncCategories($article, $request->category);

        return response()->json([
            'status' => 'true',
            'message' => 'Article mis à jour avec succès',
            'categories' => $categories,
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
    // Stockage du fichier dans le dossier images(storage) et renvoie d'une url de l'image
    private function storeImage($file)
    {
        $fileName = time() . '.' . $file->extension();
        $filePath = $file->storeAs('public/images', $fileName);

        // Use public_path to get the public URL
        $publicPath = asset(str_replace('public/', 'storage/', $filePath));

        return $publicPath;
    }
    // Synchronisation des catégories
    private function syncCategories(Article $article, $categories)
    {
        $article->categories()->sync($categories);

        $article->load('categories');
        return $article->categories;
    }
}
