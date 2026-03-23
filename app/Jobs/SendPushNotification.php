<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user || !$user->fcm_token) {
            return;
        }

        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('FCM server key tidak dikonfigurasi.');
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to'           => $user->fcm_token,
            'notification' => [
                'title' => $this->title,
                'body'  => $this->body,
                'sound' => 'default',
            ],
            'data' => $this->data,
        ]);

        if (!$response->successful()) {
            Log::error('FCM gagal kirim notifikasi', [
                'user_id'  => $this->userId,
                'response' => $response->body(),
            ]);
        }
    }
}
