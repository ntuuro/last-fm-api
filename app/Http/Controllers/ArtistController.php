<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateArtistRequest;
use App\Models\Artist;
use GuzzleHttp\Client;

class ArtistController extends Controller
{

    public $client;

    public function __construct(){
    $this->client =new Client([
        // Base URI is used with relative requests
        'base_uri' => env('LAST_FM_BASE_URL')
    ]);
    }
    /**
     * Display a listing of the resource.
     */

    // This method is used to retrieve the top 10 artists and top 10 tracks from Last.fm API
    public function index()
    {
        
            // It uses the GuzzleHttp client to make asynchronous GET requests for the two endpoints  
            // The API key is retrieved from the .env file using Laravel's env function
            $promises = [
                $this->client->getAsync('?method=chart.gettopartists&api_key=' . env('LAST_FM_API_KEY') . '&format=json&limit=100'),
                $this->client->getAsync('?method=chart.gettoptracks&api_key=' . env('LAST_FM_API_KEY') . '&format=json&limit=100'),
            ];

            // The responses from the API are settled using the GuzzleHttp Promise library
            $responses = \GuzzleHttp\Promise\Utils::settle(
                \GuzzleHttp\Promise\Utils::unwrap($promises),
            )->wait();
            
            // The settled responses are then decoded from JSON format into arrays using json_decode function
            $top_artists = json_decode($responses[0]['value']->getBody()->getContents(), true);
            $top_tracks = json_decode($responses[1]['value']->getBody()->getContents(), true);
            
            // The arrays containing the top artists and tracks are returned as a JSON response in Laravel format
            return response()->json(['success' => true, 'payload' => [$top_artists, $top_tracks]]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
{
    // Define validation rules
    $rules = array(
        'mbid' => 'required|max:255',
    );

    // Validate request data against the defined rules
    $validator = $request->validate($rules); {

        // If validation fails, return an error response
        if (!$validator) {
            response()->json(['status' => 'error',
            'message' => 'An error occurred!'], 500);

        // If validation passes, save the data to the database and return a success response
        } else {
            $mbid = $request->input('mbid');
            // $google_id = Auth::user()->google_id; 
            $google_id = '104642348109057475179'; 

            $app = new Artist;
            $app->mbid = $mbid;
            $app->google_id = $google_id;
            $app->save();
    
            return response()->json([
            'message' => 'Artist added to favorite'], 200);
        }
    }
}


    /**
     * Show artist by mbid
     *
     * @param string $mbid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($mbid)
    {

        // Query for artist record by mbid
        // $artist = DB::select("SELECT * FROM artists WHERE mbid = '$mbid'");

        // Make a GET request to Last.fm API to fetch artist info by mbid
        $promise = [
            $this->client->getAsync('?method=artist.getInfo&api_key=' . env('LAST_FM_API_KEY') . '&mbid=' . $mbid . '&format=json&limit=10'),
        ];

        // Wait for the request to finish
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();

        // Decode response to array
        $artist_info = json_decode($response[0]['value']->getBody()->getContents(), true);

        // Check if the artist exists in Last.fm API
        if (!$artist_info) {
            return response()->json(['success' => false, 'message' => 'Artist does not exist']);
        }

        // Return artist info as JSON response
        return response()->json(['success' => true, 'payload' => $artist_info]);
    }


     /**
     * Search the specified resource.
     */
    public function search($artist)
    {
        // Make an asynchronous HTTP GET request to the Last.fm API using GuzzleHttp\Client
        $promise = [
            $this->client->getAsync('?method=artist.search&api_key=' . env('LAST_FM_API_KEY').'&artist=' . $artist . '&format=json&limit=10'),
        ];
        
        // Wait for all the asynchronous requests to complete using GuzzleHttp\Promise\Utils::settle()
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();
        
        // Decode the response JSON string to an associative array using json_decode()
        $artist = json_decode($response[0]['value']->getBody()->getContents(), true);

        // Check if the artist does not exist in the response
        if (!$artist) {
            // Return a JSON response with status false and message 'User does not exist'
            return response()->json(['success' => false, 'message' => 'User does not exist']);
        }

        // Return a JSON response with status true and the artist information as the payload
        return response()->json(['success' => true, 'payload' => $artist]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Artist $artist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArtistRequest $request, Artist $artist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    // The $mbid parameter represents the MusicBrainz identifier of the artist to be removed.
    public function destroy($mbid)
    {
        // The DB::delete method is used to execute a SQL query that deletes the artist with the specified $mbid from the artists table in the database.
        $delete = DB::delete("DELETE FROM artists WHERE mbid = '$mbid'");
        // If the deletion was successful, the function returns a JSON response with a success message and a status code of 201 (Created).
        if ($delete) {
            return response()->json([
                'message' => 'Artist removed from favorite'], 201);
        }
        // If the deletion failed, the function returns a JSON response with an error message and a status code of 500 (Internal Server Error).
        else {
            return response()->json([
                'message' => 'Artist can not be found'], 500);
        }
    }
}
