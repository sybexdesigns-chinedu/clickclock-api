<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class MusicController extends Controller
{
    public function tracklist()
    {
        $country = getCountryFromIp();
        $token = getSpotifyToken();
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/search", [ //get the playlist with the latest songs from user country
            'q' => "trending $country songs",
            'type' => 'playlist',
            'limit' => 2,
            'fields' => 'playlists.items(id)'
        ]);
        $playlists = $response->json()['playlists']['items'];
        $playlist_id = $playlists[0]['id'] ?? $playlists[1]['id']; //get the playlist id
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/playlists/$playlist_id", [ //get the tracks in the playlist
            'limit' => 2,
            'include_external' => 'audio',
            'fields' => 'tracks.items.track(name,artists(name),external_urls(spotify),uri,album(images))'
        ]);
        return collect($response->json()['tracks']['items'])->map(fn($item) => [
            'name' => $item['track']['name'],
            'artists' => implode(', ', array_map(fn($artist) => $artist['name'], $item['track']['artists'])),
            'track_url' => $item['track']['external_urls']['spotify'],
            'spotify_uri' => $item['track']['uri'],
            'image' => $item['track']['album']['images'][2]['url'] ?? null,
        ]);
    }

    public function search()
    {
        $query = request()->query('q');
        $token = getSpotifyToken();
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/search", [
            'q' => $query,
            'type' => 'track',
            'limit' => 2,
            'include_external' => 'audio',
            'fields' => 'tracks.items(name,artists(name),external_urls(spotify),uri,album(images)'
        ]);
        return collect($response->json()['tracks']['items'])->map(fn($item) => [
            'name' => $item['name'],
            'artists' => implode(', ', array_map(fn($artist) => $artist['name'], $item['artists'])),
            'track_url' => $item['external_urls']['spotify'],
            'spotify_uri' => $item['uri'],
            'image' => $item['album']['images'][2]['url'] ?? null,
        ]);
    }
}
