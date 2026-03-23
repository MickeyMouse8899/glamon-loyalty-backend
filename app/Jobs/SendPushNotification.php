<?php

namespace App\Jobs;

use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
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

        try {
            $accessToken = $this->getAccessToken();
            $projectId   = $this->getProjectId();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token'        => $user->fcm_token,
                    'notification' => [
                        'title' => $this->title,
                        'body'  => $this->body,
                    ],
                    'data'         => array_map('strval', $this->data),
                    'android'      => [
                        'notification' => [
                            'sound'        => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('FCM V1 gagal', [
                    'user_id'  => $this->userId,
                    'response' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('FCM V1 exception', [
                'user_id' => $this->userId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function getAccessToken(): string
    {
        $credentialsPath = storage_path('app/firebase-credentials.json');
        $scopes          = ['https://www.googleapis.com/auth/firebase.messaging'];

        $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);
        $token       = $credentials->fetchAuthToken();

        return $token['access_token'];
    }

    private function getProjectId(): string
    {
        $credentials = json_decode(
            file_get_contents(storage_path('app/firebase-credentials.json')),
            true
        );

        return $credentials['project_id'];
    }
}
