<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use Illuminate\Http\Request;
use App\Models\Album;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlbumController extends Controller
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
    public function create(Request $request)
    {
         // Rules
         $rules = array(
            'artist_name' => 'required|max:255',
            'album_name' => 'required|max:255',
            'album_url' => 'required|max:255',
        );

        $validator = $request->validate($rules); {
            if (!$validator) {

                response()->json(['status' => 'error',
                'message' => 'An error occurred!'], 500);

            } else {
                $artist_name = $request->input('artist_name');
                $album_name = $request->input('album_name');
                $album_url = $request->input('album_url');
                $google_id = Auth::user()->google_id; 

                    $app = new Album();
                    $app->artist_name = $artist_name;
                    $app->album_name = $album_name;
                    $app->album_url = $album_url;
                    $app->google_id = $google_id;
                    $app->save();
        
                    return response()->json([
                    'message' => 'Album added to favorite'], 201);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlbumRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($name, $artist)
    {
        $promises = [
            $this->client->getAsync('?method=album.getInfo&api_key=' . env('LAST_FM_API_KEY').'&artist='. $artist .'&album='. $name . '&format=json'),
            $this->client->getAsync('?method=artist.getSimilar&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&format=json&limit=6'),
            $this->client->getAsync('?method=artist.getTopAlbums&api_key='. env('LAST_FM_API_KEY').'&artist='.$artist.'&format=json&limit=6'),
        ];
        
        $responses = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promises),
        )->wait();
        
        $album = json_decode($responses[0]['value']->getBody()->getContents(), true);
        $similar_artists = json_decode($responses[1]['value']->getBody()->getContents(), true);
        $artist_top_albums = json_decode($responses[2]['value']->getBody()->getContents(), true);

        if (!$album) {
            return response()->json(['success' => false, 'message' => 'Album does not exist']);
        }

        return response()->json(['success' => true, 'payload' => [$album, $similar_artists, $artist_top_albums]]);
    }

        /**
     * Display the specified resource.
     */
    public function search($name)
    {
        $promises = [
            $this->client->getAsync('?method=album.search&api_key=' . env('LAST_FM_API_KEY').'&album='. $name . '&format=json'),
        ];
        
        $responses = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promises),
        )->wait();
        
        $album = json_decode($responses[0]['value']->getBody()->getContents(), true);

        if (!$album) {
            return response()->json(['success' => false, 'message' => 'Album does not exist']);
        }

        return response()->json(['success' => true, 'payload' => $album]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Album $album)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($name, $artist)
    {
        $delete = DB::delete("DELETE FROM albums WHERE album_name = '$name' && artist_name = '$artist' ");
        if ($delete) {
            return response()->json([
                'message' => 'Album removed from favorite'], 201);
        }else {
            return response()->json([
                'message' => 'Album can not be found'], 500);
        }
    }
}