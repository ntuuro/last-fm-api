<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TrackController extends Controller
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
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($name, $artist)
    {
        $promise = [
            $this->client->getAsync('?method=track.getInfo&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&track='.$name.'&format=json'),
            $this->client->getAsync('?method=artist.getSimilar&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&format=json&limit=6'),
            $this->client->getAsync('?method=track.getSimilar&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&track='.$name.'&format=json&limit=10'),
            $this->client->getAsync('?method=artist.getTopAlbums&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&format=json&limit=6'),
        ];
        
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();
        
        $track = json_decode($response[0]['value']->getBody()->getContents(), true);
        $similar_artists = json_decode($response[1]['value']->getBody()->getContents(), true);
        $similar_tracks = json_decode($response[2]['value']->getBody()->getContents(), true);
        $artist_top_albums = json_decode($response[3]['value']->getBody()->getContents(), true);
        if (!$track) {
            return response()->json(['success' => false, 'message' => 'Track does not exist']);
        }

        return response()->json(['success' => true, 'payload' => [$track, $similar_artists, $similar_tracks, $artist_top_albums]]);
    }

    public function search($name)
    {
        $promise = [
            $this->client->getAsync('?method=track.search&api_key='. env('LAST_FM_API_KEY').'&track='.$name.'&format=json&limit=20'),
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Track $track)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Track $track)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Track $track)
    {
        //
    }
}
