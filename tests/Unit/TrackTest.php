<?php

namespace Tests\Unit;

use Tests\TestCase; 
use App\Models\Track;

class TrackTest extends TestCase
{
    public function testGettingTrackRecord()
    {

        // Make a GET request to the API endpoint for that record
        $response = $this->get('/api/track/Kill Bill/SZA');

        // Assert that the response status is 200
        $response->assertStatus(200);

        // Assert that the response JSON structure matches the expected structure
        $response->assertJsonStructure([
            'success',
            'payload' => [
                [
                'track' => [
                    'name',
                    'url',
                    'duration',
                    'streamable' => [],
                    'listeners',
                    'playcount',
                    'artist' => [],
                    'album' => [],
                    'toptags' => []
                ],
                ],
                [
                    'similarartists' => [],
                ],
                [
                    'similartracks' => [],
                ],
                [
                    'topalbums' => [],
                ],
            ]
        ]);
    }
}
