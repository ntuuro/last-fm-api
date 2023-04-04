<?php

namespace Tests\Unit;

use App\Http\Controllers\ArtistController;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Artist;

class ProfileTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     * Test the favorateArtists method.
     *
     * @return void
     */
    public function testFavorateArtists()
    {
         // Retrieve the first record from the database
         $artist = Artist::first();

         // Make a GET request to the API endpoint for that record
         $response = $this->get('/api/artist/' . $artist->mbid);
 
         // Assert that the response status is 200
         $response->assertStatus(200);
 
         // Assert that the response JSON structure matches the expected structure
         $response->assertJsonStructure([
             'success',
             'payload' => [
                 'artist' => [
                     'name',
                     'mbid',
                     'url',
                     'image' => [],
                     'streamable',
                     'ontour',
                     'stats' => [],
                     'similar' =>[],
                     'tags' => [],
                     'bio' => [],
                 ]
             ]
         ]);
 
         // Assert that the response JSON matches the expected values
         $response->assertJson([
             'success' => true,
             'payload' => [
                 'artist' => [
                     'name' => 'Lana Del Rey',
                     'mbid' => 'b7539c32-53e7-4908-bda3-81449c367da6',
                     'url' => 'https://www.last.fm/music/Lana+Del+Rey',
                     'streamable' => '0',
                     'image' => [],
                     'ontour' => '1',
                     'stats' => [],
                     'similar' =>[],
                     'tags' => [],
                     'bio' => [],
                 ]
             ]
         ]);
    }


    public function testFavorateAlbum()
    {
        $response = $this->get('/api/album/Unorthodox Jukebox/Bruno Mars');

        $response->assertStatus(200);
        $response->assertJsonStructure([
                'success',
                'payload' => []
            ]);
    }



}
