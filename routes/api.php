<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// INSCRIPTION CONNEXION
Route::post('/register', [RegisterController::class, 'register']);
Route::name('login')->post('/login', [LoginController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']); // Récupérer les catégories

// AFFICHAGE ARTICLE(S)
Route::get('/articles', [ArticleController::class, 'index']); // Récupérer tous les articles
Route::get('/articles/{id}', [ArticleController::class, 'show']); // Récupérer un article spécifique

// AUTHENTIFICATION REQUISE
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles', [ArticleController::class, 'create']); // Créer un post
    Route::put('/articles/{id}', [ArticleController::class, 'update']); // Mettre à jour un article
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']); // Supprimer un article
    Route::get('/dashboard/', [UserController::class, 'profile']); // Accéder à son profil
    Route::put('/profile/edit', [UserController::class, 'editProfile']); // Modifier profil
    Route::put('/profile/password', [UserController::class, 'updatePassword']);  // Modifier mot de passe
    Route::post('/user/{userId}/favorites/{articleId}', [UserController::class, 'addToFavorites']);
});
