<?php

namespace App\Helpers;

/**
 * PusherHelper - Kirim event ke Pusher tanpa SDK/Composer
 * Cukup pakai cURL yang sudah built-in di semua server PHP
 */
class PusherHelper
{
    private string $appId;
    private string $appKey;
    private string $appSecret;
    private string $cluster;

    public function __construct()
    {
        $this->appId     = env('PUSHER_APP_ID',      '');
        $this->appKey    = env('PUSHER_APP_KEY',     '');
        $this->appSecret = env('PUSHER_APP_SECRET',  '');
        $this->cluster   = env('PUSHER_APP_CLUSTER', 'ap1');
    }

    /**
     * Kirim event ke channel Pusher
     *
     * @param string $channel  Nama channel, misal: 'patrol-locations'
     * @param string $event    Nama event, misal: 'LocationUpdated'
     * @param array  $data     Data yang dikirim (akan di-encode ke JSON)
     */
    public function trigger(string $channel, string $event, array $data): bool
    {
        if (empty($this->appId) || empty($this->appKey) || empty($this->appSecret)) {
            return false; // Pusher belum dikonfigurasi
        }

        // Body JSON
        $bodyData = json_encode([
            'name'     => $event,
            'data'     => json_encode($data),   // Pusher minta data sebagai string JSON
            'channels' => [$channel],
        ]);

        $bodyMd5   = md5($bodyData);
        $timestamp = time();
        $path      = '/apps/' . $this->appId . '/events';

        // Parameter query — WAJIB urut alphabetical untuk tanda tangan
        $params = [
            'auth_key'       => $this->appKey,
            'auth_timestamp' => $timestamp,
            'auth_version'   => '1.0',
            'body_md5'       => $bodyMd5,
        ];
        ksort($params);
        $queryString = http_build_query($params);

        // Buat tanda tangan HMAC-SHA256
        $stringToSign = "POST\n{$path}\n{$queryString}";
        $signature    = hash_hmac('sha256', $stringToSign, $this->appSecret);

        // URL lengkap
        $url = "https://api-{$this->cluster}.pusher.com{$path}"
             . "?{$queryString}&auth_signature={$signature}";

        // Kirim dengan cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $bodyData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($bodyData),
            ],
            CURLOPT_TIMEOUT        => 3,    // Maksimal 3 detik
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}