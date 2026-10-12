<?php

if (!function_exists('apiCall')) {
    function apiCall(string $endpoint, string $method = 'GET', array $data = [])
    {
        $url = rtrim(env('IMS_APP_BASE_URL'), '/') . '/' . ltrim($endpoint, '/');

        // dd($data);

        $methodUpper = strtoupper($method);

        if ($methodUpper === 'GET' && !empty($data)) {
            $queryString = http_build_query($data);
            $url .= (strpos($url, '?') === false ? '?' : '&') . $queryString;
        }

        $curl = curl_init();
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST  => $methodUpper,
            CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Content-Type: application/json'],
        ];

        if ($methodUpper !== 'GET' && !empty($data)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $opts);
        $res = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // dd($res, $err);

        if ($err) {
            \Illuminate\Support\Facades\Log::error("Api call error [$method $url]: $err");
            return null;
        }

        return json_decode($res, true);
    }
}
