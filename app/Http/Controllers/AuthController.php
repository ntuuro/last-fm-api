<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
    Redirect to Google's authentication page using Socialite.

    @return JsonResponse A JSON response containing the URL to the authentication page.
    */
    public function redirectToAuth(): JsonResponse
    {
        // Use Socialite to generate the URL for Google's authentication page and get the target URL.
        // &&
        // Return a JSON response containing the URL to the authentication page.
        return response()->json([
            'url' => Socialite::driver('google')
                         ->stateless()
                         ->redirect()
                         ->getTargetUrl(),
        ]);
    }


    /**
     Handle authentication callback from Google OAuth.
     @return JsonResponse
     */
    public function handleAuthCallback(): JsonResponse
    {
        try {
             /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver('google')
                                ->stateless()
                                ->user();
        } catch (ClientException $e) {
            // Return error response for invalid credentials
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        /** 
         * Find or create user based on their email address, and update their profile information with 
         * Google OAuth data.
         * 
         * @var User $user
         */
        $user = User::query()
            ->firstOrCreate(
                [
                    'email' => $socialiteUser->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $socialiteUser->getName(),
                    'google_id' => $socialiteUser->getId(),
                    'avatar' => $socialiteUser->getAvatar(),
                ]
            );
        // Return response with user data and access token
        return response()->json([
            'user' => $user,
            'access_token' => $user->createToken('google-token')->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**

    Logout the user and revoke all access tokens.

    @return JsonResponse
    */
    public function logout() {
        // Get all access tokens of the authenticated user and delete them.
        Auth::user()->tokens->each(function($token) {
            $token->delete();
        });
        // Return response with message of logging out successfully
        return response()->json('Successfully logged out');
    }
}
