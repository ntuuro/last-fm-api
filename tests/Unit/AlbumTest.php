<?php

namespace Tests\Unit;

use Tests\TestCase; 
use App\Models\Album;

class AlbumTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    // Adding Album to favorite
    public function testCreateAlbum()
    {
       $data = [
            'album_name' => "Unorthodox Jukebox",
            'album_url' => "https://www.last.fm/music/Bruno+Mars/Unorthodox+Jukebox/Young+Girls",
            'artist_name' => "Bruno Mars"
            ];
        $album = Album::factory(Artist::class)->make();
        $response = $this->actingAs($album, 'web')->json('POST', '/api/album/create',$data);
        $response->assertJson(['message' => 'Album added to favorite'], 200);
      }

    //   Get Artist's album
    public function testShowAlbum()
    {
        $response = $this->get('/api/album/Unorthodox Jukebox/Bruno Mars');

        $response->assertStatus(200);
        $response->assertJsonStructure([
                'success',
                'payload' => []
            ]);
    }

    public function testDestroyMethodDeletesAlbumFromDatabase()
    {
        // Create a new album and insert it into the database
        $album = Album::factory(Album::class)->make();

        // Call the destroy method with the album's mbid
        $response = $this->get('/api/album/Unorthodox Jukebox/Bruno Mars/delete');

        // Assert that the response status code is 200 (Created)
        $response->assertStatus(200);

        // Assert that the album has been deleted from the database
        $this->assertDatabaseMissing('albums', ['album_name' => $album->album_name]);
    }

    public function testDestroyMethodReturnsErrorIfAlbumNotFound()
    {
        // Call the destroy method with an invalid mbid
        $response = $this->get('/api/album/1221/sdsdsds/delete');

        // Assert that the response status code is 200 (Success)
        $response->assertStatus(200);
    }

}
