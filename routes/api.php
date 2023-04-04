<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ProfileController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::group(['middleware' => ['web']], function () {
    Route::get('auth', [AuthController::class, 'redirectToAuth']);
    Route::get('auth/callback', [AuthController::class, 'handleAuthCallback']);
// });


// Artist Routes
    // Get top Artists and Tracks
    Route::get('artist', [ArtistController::class, 'index']);
    // Get Single Artist
    Route::get('artist/{mbid}', [ArtistController::class, 'show']);
    // Search Artist
    Route::get('artist/search/{artist}', [ArtistController::class, 'search']);


// Track Routes
    // Get single track of an artist
    Route::get('track/{name}/{artist}', [TrackController::class, 'show']);
    // Search tracks
    Route::get('track/search/{name}', [TrackController::class, 'search']);


//  Album Routes
    // Search albums
    Route::get('album/search/{name}', [AlbumController::class, 'search']);
    // Get Single Album of an artist
    Route::get('album/{name}/{artist}', [AlbumController::class, 'show']);
    // Get User's albims
    Route::get('albums', [AlbumController::class, 'getTopAlbums']);


// Route::group(['middleware' => ['auth:sanctum', 'verified']], function() {;
    // Logout Route
    Route::get('logout', [AuthController::class, 'logout']);

    // Add Artist to favorite
    Route::post('artist/create', [ArtistController::class, 'create']);
    // Remove Artist to favorite
    Route::get('artist/{mbid}/delete', [ArtistController::class, 'destroy']);

    // Add Album to favotite
    Route::post('album/create', [AlbumController::class, 'create']);
    // Remove Artist to favorite
    Route::get('album/{name}/{artist}/delete', [AlbumController::class, 'destroy']);

    //  Profile Routes
    // Get saved artists
    Route::get('savedArtists', [ProfileController::class, 'favorateArtists']);
    Route::get('savedAlbums', [ProfileController::class, 'favorateAlbums']);

//   });
