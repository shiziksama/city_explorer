<?php

namespace App\Services;

use App\Models\Token;
use App\Models\TrackGetter;

class StravaService
{
    public function refreshToken(Token $token)
    {
        $provider = new \League\OAuth2\Client\Provider\Strava([
            'clientId'     => config('services.strava.client_id'),
            'clientSecret' => config('services.strava.client_secret'),
            'redirectUri'  => config('services.strava.redirect_uri'),
        ]);
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->refresh_token,
        ]);
        $token->access_token = $newAccessToken->getToken();
        $token->refresh_token = $newAccessToken->getRefreshToken();
        $token->expires_time = $newAccessToken->getExpires();
        $token->save();
        return $token;
    }

    public function syncActivities($token_id)
    {
        $token = Token::find($token_id);
        if ($token->expires_time < time()) {
            $token = $this->refreshToken($token);
        }
        $getter = TrackGetter::firstOrNew([
            'user_id' => $token->user_id,
            'service' => $token->service,
        ]);
        $enddate = $getter->getData('enddate');
        if (empty($enddate)) {
            $options = [];
            $enddate = '1990-01-01T00:00:00Z';
        } else {
            $s = new \DateTime($enddate);
            $options = ['after' => $s->format('U')];
        }
        $options['page'] = 1;
        $headers = ['Authorization: Bearer ' . $token->access_token];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $url = 'https://www.strava.com/api/v3/athlete/activities?';
        curl_setopt($curl, CURLOPT_URL, $url . http_build_query($options));
        $response = json_decode(curl_exec($curl), true);
        while (!empty($response)) {
            foreach ($response as $activity) {
                if ($activity['start_date_local'] > $enddate) {
                    $enddate = $activity['start_date_local'];
                }
                \App\Jobs\TrackgetStravaSingle::dispatch($token->id, $activity['id'])->onQueue('parsers');
            }
            $options['page']++;
            curl_setopt($curl, CURLOPT_URL, $url . http_build_query($options));
            $response = json_decode(curl_exec($curl), true);
        }
        $getter->setData('enddate', $enddate);
        $getter->save();
        curl_close($curl);
    }

    public function fetchSingleActivity($token_id, $track_id)
    {
        $token = Token::find($token_id);
        $headers = ['Authorization: Bearer ' . $token->access_token];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $url = 'https://www.strava.com/api/v3/activities/' . $track_id;
        curl_setopt($curl, CURLOPT_URL, $url);
        $response = json_decode(curl_exec($curl), true);
        $geometry = resolve('geometry');
        $points = \Polyline::decode($response['map']['polyline']);
        $points = array_chunk($points, 2);
        $points_backup = $points;
        $points = array_map(function ($item) {
            return implode(' ', $item);
        }, $points);
        $points_backup = array_map(function ($item) {
            return ['lat' => $item[0], 'lng' => $item[1]];
        }, $points_backup);
        $points = array_values(array_filter($points));
        $points = implode(',', $points);
        $w = $geometry->parseWkt('MultiLineString((' . $points . '))');
        $track = new \App\Models\Track();
        $track->track_original = $w->toWkb();
        $track->track_simple = $w->toWkb();
        $track->remove_big_lines();
        $track->simplification_version = 255;
        $track->external_id = 'strava_' . $track_id;
        $track->uid = $token->user_id;
        $date = new \DateTime($response['start_date_local']);
        $track->date = $date->format('Y-m-d H:i:s');
        if (\DB::table('tracks')->where('external_id', $track->external_id)->count() == 0) {
            $track->save();
        }
        \App\Jobs\RemoveTilesJob::dispatch($token->user_id, $points_backup)->onQueue('tiles');
        curl_close($curl);
    }
}
