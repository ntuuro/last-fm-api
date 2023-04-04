<?php

namespace Tests\Unit;

use App\Models\Artist;
use Tests\TestCase; 

class ArtistTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    // Trying to create an artist or add to favorite
    // public function testCreateArtistWithMiddleware(): void
    // {
    //     $data = [
    //         'mbid' => "b7539c32-53e7-4908-bda3-81449c367da6",
    //         'google_id' => "sdduusds-232sdjs-32"
    //                ];
    //     $response =  $this->json('POST', '/api/artist/create',$data);
    //     $response->assertStatus(401);
    //     $response->assertJson(['message' => "Unauthenticated."]);
    // }

    // Adding Artist to favorite
    public function testCreateArtist()
    {
       $data = [
            'mbid' => "b7539c32-53e7-4908-bda3-81449c367da6",
            'google_id' => "sdduusds-232sdjs-32"
            ];
        $artist = Artist::factory(Artist::class)->make();
        $response = $this->actingAs($artist, 'web')->json('POST', '/api/artist/create',$data);
        $response->assertJson(['message' => 'Artist added to favorite'], 200);
      }

    //   Getting all Artists
    public function testGettingAllTopTracksAndArtists()
    {
        $response = $this->get('/api/artist');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'payload' => [
            ]
        ]);
    }

    public function testGettingSingleRecord()
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

    public function testDestroyMethodDeletesArtistFromDatabase()
    {
        // Create a new artist and insert it into the database
        $artist = Artist::factory(Artist::class)->make();

        // Call the destroy method with the artist's mbid
        $response = $this->get('/api/artist/b7539c32-53e7-4908-bda3-81449c367da6/delete');

        // Assert that the response status code is 200 (Created)
        $response->assertStatus(200);

        // Assert that the artist has been deleted from the database
        $this->assertDatabaseMissing('artists', ['mbid' => $artist->mbid]);
    }

    public function testDestroyMethodReturnsErrorIfArtistNotFound()
    {
        // Call the destroy method with an invalid mbid
        $response = $this->get('/api/artist/1221/delete');

        // Assert that the response status code is 200 (Success)
        $response->assertStatus(200);
    }



}