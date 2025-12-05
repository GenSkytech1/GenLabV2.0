<?php

namespace App\Jobs;

use App\Models\MarketingPersonDeviceToken;
use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMarketingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $marketingUser;
    public $title;
    public $body;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($marketingUser, $title, $body, array $data = [])
    {
        $this->marketingUser = $marketingUser;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;

        Log::info("SendMarketingNotificationJob initialized", [
            'marketing_user_id' => $marketingUser->id,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(FCMService $fcmService): void
    {
        Log::info("SendMarketingNotificationJob started", [
            'marketing_user_id' => $this->marketingUser->id,
        ]);

        // Get device tokens
        $tokens = MarketingPersonDeviceToken::where('marketing_person_id', $this->marketingUser->id)
            ->pluck('device_token')
            ->unique()
            ->toArray();

        Log::info("Marketing tokens fetched", [
            'count' => count($tokens),
            'tokens' => $tokens,
        ]);

        if (empty($tokens)) {
            Log::warning("No device tokens found for marketing user", [
                'marketing_user_id' => $this->marketingUser->id,
            ]);
            return;
        }

        // Send notification
        foreach ($tokens as $token) {
            try {
                $fcmService->sendNotification(
                    $token,
                    $this->title,
                    $this->body,
                    $this->data
                );

                Log::info("Notification sent successfully", [
                    'token' => $token,
                    'title' => $this->title,
                    'body' => $this->body,
                    'data' => $this->data,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send notification", [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("SendMarketingNotificationJob completed");
    }
}
