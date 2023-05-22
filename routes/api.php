<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/movie-lists', [MovieController::class, 'getMovieLists']);
Route::get('/movie-details/{id}', [MovieController::class, 'getMovieDetails']);

Route::middleware('auth:api')->group(function () {

	Route::get('/movie-create', [MovieController::class, 'createMovie']);
	Route::post('/movie-store', [MovieController::class, 'storeMovie']);
	Route::put('/movie-update/{id}', [MovieController::class, 'updateMovie']);
	Route::delete('/movie-delete/{id}', [MovieController::class, 'destoryMovie']);

	Route::post('/logout', [AuthController::class, 'logout']);
});


Route::post('/comment-store', [MovieController::class, 'storeComment']);
