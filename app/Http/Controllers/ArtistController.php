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
    public function index()
    {

            $promises = [
                $this->client->getAsync('?method=chart.gettopartists&api_key=' . env('LAST_FM_API_KEY') . '&format=json&limit=10'),
                $this->client->getAsync('?method=chart.gettoptracks&api_key=' . env('LAST_FM_API_KEY') . '&format=json&limit=10'),
            ];
            
            $responses = \GuzzleHttp\Promise\Utils::settle(
                \GuzzleHttp\Promise\Utils::unwrap($promises),
            )->wait();
            
            $top_artists = json_decode($responses[0]['value']->getBody()->getContents(), true);
            $top_tracks = json_decode($responses[1]['value']->getBody()->getContents(), true);
            
            return response()->json(['success' => true, 'payload' => [$top_artists, $top_tracks]]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Rules
        $rules = array(
            'mbid' => 'required|max:255',
        );

        $validator = $request->validate($rules); {
            if (!$validator) {

                response()->json(['status' => 'error',
                'message' => 'An error occurred!'], 500);

            } else {
                $mbid = $request->input('mbid');
                $google_id = Auth::user()->google_id; 

                    $app = new Artist;
                    $app->mbid = $mbid;
                    $app->google_id = $google_id;
                    $app->save();
        
                    return response()->json([
                    'message' => 'Artist added to favorite'], 201);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($mbid)
    {

        $mbid = DB::select("SELECT * FROM artists WHERE mbid = '$mbid'");
        $promise = [
            $this->client->getAsync('?method=artist.getInfo&api_key=' . env('LAST_FM_API_KEY').'&mbid=' . $mbid[0]->mbid . '&format=json&limit=10'),
        ];
        
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();
        
        $artist = json_decode($response[0]['value']->getBody()->getContents(), true);
        if (!$artist) {
            return response()->json(['success' => false, 'message' => 'User does not exist']);
        }

        return response()->json(['success' => true, 'payload' => $artist]);
    }

     /**
     * Search the specified resource.
     */
    public function search($artist)
    {
        $promise = [
            $this->client->getAsync('?method=artist.search&api_key=' . env('LAST_FM_API_KEY').'&artist=' . $artist . '&format=json&limit=10'),
        ];
        
        $response = \GuzzleHttp\Promise\Utils::settle(
            \GuzzleHttp\Promise\Utils::unwrap($promise),
        )->wait();
        
        $artist = json_decode($response[0]['value']->getBody()->getContents(), true);
        if (!$artist) {
            return response()->json(['success' => false, 'message' => 'User does not exist']);
        }

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
    public function destroy($mbid)
    {
        $delete = DB::delete("DELETE FROM artists WHERE mbid = '$mbid'");
        if ($delete) {
            return response()->json([
                'message' => 'Artist removed from favorite'], 201);
        }else {
            return response()->json([
                'message' => 'Artist can not be found'], 500);
        }
    }
}
