<?php

use GeoIp2\Database\Reader;
use App\Services\PerspectiveService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

function getUserRealIp()
{
    $response = Http::withoutVerifying()->get('https://api64.ipify.org?format=json');

    return $response->successful() ? $response->json()['ip'] : '8.8.8.8';
}

function getCountryFromIp()
{
    $ip = request()->ip();
    if ($ip == '127.0.0.1' || $ip == '::1') {
        $ip = getUserRealIp();
    }
    $dbPath = storage_path('app/public/geolite/GeoLite2-Country.mmdb');

    if (!file_exists($dbPath)) {
        return response()->json(['error' => 'GeoIP database not found'], 401);
    }
    try {
        $reader = new Reader($dbPath);
        $record = $reader->country($ip);
        $country = $record->country->name;
    } catch (\Throwable $th) {
        $country = 'Unknown';
    }
    return $country;
}

function getLocation($city, $country)
{
    $response = Http::get("https://api.geoapify.com/v1/geocode/search?city={$city}&country={$country}&limit=1&format=json&apiKey=".env('GEOAPI_KEY'));
    // return $response->json();
    if (!$response->successful() || empty($response->json()['results'])) {
        return ['lon' => 0, 'lat' => 0]; // Default location if not found
    }
    return collect($response->json()['results'][0])->only(['lon', 'lat'])->all();
}

function screenInput($input)
{
    $perspectiveService = new PerspectiveService;
    $result = $perspectiveService->analyzeText($input);
    if($result['SEVERE_TOXICITY'] >= 0.6 || $result['IDENTITY_ATTACK'] >= 0.6 || $result['THREAT'] >= 0.6) return false;
    return true;
}

function getSpotifyToken()
{
    if (Cache::has('spotify_token')) {
        return Cache::get('spotify_token');
    }

    $response = Http::withoutVerifying()->asForm()->post('https://accounts.spotify.com/api/token', [
        'grant_type'    => 'client_credentials',
        'client_id'     => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    ]);

    $token = $response->json()['access_token'];
    $expiresIn = $response->json()['expires_in']; // Typically 3600 seconds (1 hour)

    // Store in cache for the duration of token validity
    Cache::put('spotify_token', $token, now()->addSeconds($expiresIn - 60)); // Subtract 1 minute for safety
    return $token;
}

function moderateImage($path, $temp_path = false)
{
    $response = Http::attach(
        'media', file_get_contents($temp_path ? $path : storage_path('app/public/'.$path)), 'image.jpg'
    )->post('https://api.sightengine.com/1.0/check-workflow.json', [
        'workflow' => env('SIGHT_ENGINE_IMAGE_WORKFLOW_ID'),
        'api_user' => env('SIGHT_ENGINE_API_USER'),
        'api_secret' => env('SIGHT_ENGINE_API_KEY'),
    ]);
    // return $response->json();
    if ($response->json()['summary']['action'] === 'accept') return ['status' => true];
    else return [
        'status' => false,
        'reason' => $response->json()['summary']['reject_reason'][0]['text']
    ];
}

function moderateVideo($path)
{
    $response = Http::attach(
        'media', file_get_contents(storage_path('app/public/'.$path)), 'video.jpg'
    )->post('https://api.sightengine.com/1.0/video/check-workflow.json', [
        'workflow' => env('SIGHT_ENGINE_VIDEO_WORKFLOW_ID'),
        'callback_url' => 'http://white-dogfish-514196.hostingersite.com/public/api/sightengine/video-moderation/callback',
        // 'callback_url' => route('sightengine.video-moderation.callback'),
        'api_user' => env('SIGHT_ENGINE_API_USER'),
        'api_secret' => env('SIGHT_ENGINE_API_KEY'),
    ]);
    // return $response->json()['status'];
    if ($response->json()['status'] == 'success') {
        return $response->json()['media']['id'];
    }
    else return null;
}

function formatNumber($num)
{
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return round($num / 1000, 1) . 'k';
    }
    return $num;
}
