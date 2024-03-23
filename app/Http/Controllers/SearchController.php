<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        // Perform the search for articles
        $articles = Article::where('title', 'like', '%' . $request->input('query') . '%')->get();
        // Modify the file attribute of each article to include the asset URL
        $articles->each(function ($article) {
            $article->file = asset('storage/images/' . $article->file);
        });


        return response()->json([
            'status' => 'success',
            'articles' => $articles
        ]);
    }

    public function filterByCategory(Request $request, $categoryName)
    {
        // Retrieve the category by name
        $category = Category::where('category', $categoryName)->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Retrieve articles associated with the category using the pivot table
        $articles = $category->articles()->get();
        // Modify the file attribute of each article to include the asset URL
        $articles->each(function ($article) {
            $article->file = asset('storage/images/' . $article->file);
        });

        return response()->json([
            'status' => 'success',
            'articles' => $articles
        ]);
    }
}
