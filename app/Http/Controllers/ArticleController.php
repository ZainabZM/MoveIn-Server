<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;
use App\Models\Category;

class ArticleController extends Controller
{
    /**
     * Validate the request data for creating or updating an article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
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
            'category' => 'required|array', // Assuming categories are submitted as an array
        ]);
    }

    /**
     * Store the image and return the file name.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    private function storeImage($file)
    {
        $fileName = time() . '.' . $file->extension();
        $file->storeAs('public/images', $fileName);

        return $fileName;
    }

    /**
     * Store a newly created article in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = $this->validateArticleRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => $validator->errors()
            ]);
        }

        $fileName = $this->storeImage($request->file);

        $article = $this->storeArticle($request, $fileName);

        $categories = $this->syncCategories($article, $request->category);

        return response()->json([
            'status' => 'true',
            'message' => 'Article créé avec succès',
            'categories' => $categories,
            'article' => $article,
        ]);
    }

    /**
     * Display the specified article.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);

        return response()->json([
            'status' => 'true',
            'article' => $article,
        ]);
    }

    /**
     * Show the form for editing the specified article.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $article = Article::findOrFail($id);

        return response()->json([
            'status' => 'true',
            'article' => $article,
        ]);
    }

    /**
     * Update the specified article in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
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
        $fileName = $this->storeImage($request->file);
        $article->update($request->except(['file', 'category']));
        $article->update(['file' => $fileName]);
        $categories = $this->syncCategories($article, $request->category);

        return response()->json([
            'status' => 'true',
            'message' => 'Lieu mis à jour avec succès',
            'categories' => $categories,
            'article' => $article,
        ]);
    }

    /**
     * Remove the specified article from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'status' => 'true',
            'message' => 'Article supprimé avec succès',
        ]);
    }

    /**
     * Store the article in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $fileName
     * @return \App\Models\Article
     */
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

    /**
     * Synchronize article categories in the database.
     *
     * @param  \App\Models\Article  $article
     * @param  array  $categories
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function syncCategories(Article $article, $categories)
    {
        $article->categories()->detach(); // Remove existing categories
        $article->categories()->attach($categories); // Attach new categories
        $article->load('categories'); // Reload the categories relationship

        return $article->categories;
    }
}
