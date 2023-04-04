<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TrackController extends Controller
{
    // a class with a public property $client and a constructor method __construct().
    public $client;

    public function __construct(){

    // The constructor initializes the $client property with a new instance of the GuzzleHttp\Client class, which is a popular HTTP client for PHP.
    $this->client =new Client([
        // Base URI is used with relative requests
        'base_uri' => env('LAST_FM_BASE_URL')
    ]);
    }

    /**
     * Display the specified resource.
     */
    // This function receives the name of a track and the name of the artist that created the track as parameters.
    public function show($name, $artist)
    {
        // An array of promises is created, with each promise representing a request to the Last.fm API.
        $promise = [
            // Get information about the track.
            $this->client->getAsync('?method=track.getInfo&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&track='.$name.'&format=json'),
            // Get similar artists to the given artist.
            $this->client->getAsync('?method=artist.getSimilar&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&format=json&limit=6'),
            // Get similar tracks to the given track.
            $this->client->getAsync('?method=track.getSimilar&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&track='.$name.'&format=json&limit=10'),
            // Get the top albums of the given artist.
            $this->client->getAsync('?method=artist.getTopAlbums&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&format=json&limit=6'),
        ];
        
        // The GuzzleHttp Promise library is used to resolve all promises asynchronously.
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();
        
        // The response for each promise is retrieved and decoded from JSON to an array.
        $track = json_decode($response[0]['value']->getBody()->getContents(), true);
        $similar_artists = json_decode($response[1]['value']->getBody()->getContents(), true);
        $similar_tracks = json_decode($response[2]['value']->getBody()->getContents(), true);
        $artist_top_albums = json_decode($response[3]['value']->getBody()->getContents(), true);

        // If the track information is not found, the function returns a JSON response with an error message.
        if (!$track) {
            return response()->json(['success' => false, 'message' => 'Track does not exist']);
        }

         // The function returns a JSON response with a success message and the retrieved data.
        return response()->json(['success' => true, 'payload' => [$track, $similar_artists, $similar_tracks, $artist_top_albums]]);
    }

    public function search($name)
    {
        $promise = [
            $this->client->getAsync('?method=track.search&api_key='. env('LAST_FM_API_KEY').'&track='.$name.'&format=json'),
        ];
        
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();
        
        $tracks = json_decode($response[0]['value']->getBody()->getContents(), true);
        if (!$tracks) {
            return response()->json(['success' => false, 'message' => 'Track does not exist']);
        }

        return response()->json(['success' => true, 'payload' => [$tracks]]);
    }
}
