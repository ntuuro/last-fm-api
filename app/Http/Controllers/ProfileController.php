<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Artist;

class ProfileController extends Controller
{

    public $client;

    public function __construct(){
    $this->client =new Client([
        //  Base URI is used with relative requests
        'base_uri' => env('LAST_FM_BASE_URL')
    ]);
    }

    public function favorateArtists(){
        $saved_artists = DB::table('artists')
            ->select('artists.*')
            ->orderBy('artists.id', 'ASC')
            // ->WHERE('google_id', Auth::user()->google_id  )
            // ->WHERE('google_id', '104642348109057475179'  )
            ->get();

        for($i = 0; $i < count($saved_artists); $i++){
            $artists[$i] = $saved_artists[$i]->mbid;
        }

        $promises = [];
        foreach ($artists as $art)
        {

            $promises[] = [
                $this->client->getAsync('?method=artist.getInfo&api_key=' . env('LAST_FM_API_KEY').'&mbid=' . $art . '&format=json&limit=10'),
            ];
        }

        foreach($promises as $promise) {

            $responses = \GuzzleHttp\Promise\Utils::settle(
                \GuzzleHttp\Promise\Utils::unwrap($promise),
            )->wait();
            $favorite_artists[] = json_decode($responses[0]['value']->getBody()->getContents(), true);
        }
            
        return response()->json(['success' => true, 'payload' => $favorite_artists]);
    }

    public function favorateAlbums(){
        $saved_albums = DB::table('albums')
            ->select('albums.*')
            ->orderBy('albums.id', 'ASC')
            // ->WHERE('google_id', Auth::user()->google_id  )

            // ->WHERE('google_id', '104642348109057475179'  )
            ->get();

        for($i = 0; $i < count($saved_albums); $i++){
            $albums[$i] = $saved_albums[$i];
        }

        $promises = [];
        foreach ($albums as $album)
        {

            $promises[] = [
                $this->client->getAsync('?method=album.getInfo&api_key=' . env('LAST_FM_API_KEY').'&artist=' . $album->artist_name .'&album='.  $album->album_name . '&format=json&limit=10'),
            ];
        }

        foreach($promises as $promise) {

            $responses = \GuzzleHttp\Promise\Utils::settle(
                \GuzzleHttp\Promise\Utils::unwrap($promise),
            )->wait();
            $favorite_albums[] = json_decode($responses[0]['value']->getBody()->getContents(), true);
        }
            
        return response()->json(['success' => true, 'payload' => $favorite_albums]);
    }
    
}
