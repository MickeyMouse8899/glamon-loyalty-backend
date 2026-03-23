<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\PendingPoint;
use App\Models\User;
use App\Models\WebstoreIntegration;
use App\Services\PointEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MokaPosController extends Controller
{
    public function __construct(protected PointEngine $pointEngine) {}

    public function handle(Request $request, string $brandSlug)
    {
        $brand = Brand::where('slug', $brandSlug)->where('is_active', true)->first();

        if (!$brand) {
            return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
        }

        $integration = WebstoreIntegration::where('brand_id', $brand->id)
            ->where('platform', 'mokapos')
            ->where('is_active', true)
            ->first();

        if ($integration && $integration->webhook_secret) {
            if (!$this->verifySignature($request, $integration->webhook_secret)) {
                Log::warning("MokaPOS webhook signature invalid untuk brand: {$brandSlug}");
                return response()->json(['message' => 'Invalid signature.'], 401);
            }
        }

        $payload = $request->all();
        $event   = $request->header('X-GoBiz-Event') ?? $payload['event'] ?? null;

        Log::info("MokaPOS webhook diterima", [
            'brand'  => $brandSlug,
            'event'  => $event,
            'payload'=> $payload,
        ]);

        if (!in_array($event, ['transaction.completed', 'payment.success'])) {
            return response()->json(['message' => 'Event diabaikan.'], 200);
        }

        return $this->processTransaction($payload, $brand);
    }

    private function processTransaction(array $payload, Brand $brand)
    {
        $orderId    = (string) ($payload['transaction_id'] ?? $payload['id'] ?? '');
        $orderTotal = (float) ($payload['total_price'] ?? $payload['amount'] ?? 0);
        $phone      = $payload['customer']['phone'] ?? $payload['phone'] ?? '';
        $email      = strtolower($payload['customer']['email'] ?? $payload['email'] ?? '');

        if ($orderTotal <= 0) {
            return response()->json(['message' => 'Total tidak valid.'], 200);
        }

        $user = $this->findUser($email, $phone);

        if (!$user) {
            PendingPoint::create([
                'brand_id'         => $brand->id,
                'wc_order_id'      => 'MOKA-' . $orderId,
                'order_total'      => $orderTotal,
                'customer_email'   => $email ?: null,
                'customer_phone'   => $this->normalizePhone($phone),
                'points_to_credit' => 0,
                'status'           => 'pending',
                'expires_at'       => now()->addDays(30),
            ]);

            return response()->json(['message' => 'User tidak ditemukan, poin pending.'], 200);
        }

        $transaction = $this->pointEngine->earnPoints(
            userId: $user->id,
            brandId: $brand->id,
            amount: $orderTotal,
            source: 'instore',
            invoiceNumber: 'MOKA-' . $orderId,
            meta: ['moka_transaction_id' => $orderId, 'platform' => 'mokapos'],
        );

        return response()->json([
            'message'       => 'Poin berhasil ditambahkan.',
            'points_earned' => $transaction->points_earned,
            'user'          => $user->name,
        ], 200);
    }

    private function findUser(string $email, string $phone): ?User
    {
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) return $user;
        }

        if ($phone) {
            $normalized = $this->normalizePhone($phone);
            $user = User::where('phone', $normalized)->first();
            if ($user) return $user;
        }

        return null;
    }

    private function verifySignature(Request $request, string $secret): bool
    {
        $signature = $request->header('X-GoBiz-Signature') ?? $request->header('X-Moka-Signature');
        if (!$signature) return false;

        $payload  = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    private function normalizePhone(string $phone): string
    {
        if (empty($phone)) return '';
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return '+' . $phone;
    }
}
