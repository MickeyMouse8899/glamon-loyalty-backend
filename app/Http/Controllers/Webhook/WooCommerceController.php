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

class WooCommerceController extends Controller
{
    public function __construct(protected PointEngine $pointEngine) {}

    public function handle(Request $request, string $brandSlug)
    {
        $brand = Brand::where('slug', $brandSlug)->where('is_active', true)->first();

        if (!$brand) {
            return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
        }

        $integration = WebstoreIntegration::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->first();

        if ($integration && $integration->webhook_secret) {
            if (!$this->verifySignature($request, $integration->webhook_secret)) {
                Log::warning("WooCommerce webhook signature invalid untuk brand: {$brandSlug}");
                return response()->json(['message' => 'Invalid signature.'], 401);
            }
        }

        $topic = $request->header('X-WC-Webhook-Topic');

        if ($topic !== 'order.completed') {
            return response()->json(['message' => 'Topic diabaikan.'], 200);
        }

        $payload = $request->all();

        Log::info("WooCommerce webhook diterima", [
            'brand'    => $brandSlug,
            'order_id' => $payload['id'] ?? null,
            'total'    => $payload['total'] ?? null,
        ]);

        return $this->processOrder($payload, $brand);
    }

    private function processOrder(array $payload, Brand $brand)
    {
        $orderId    = (string) ($payload['id'] ?? '');
        $orderTotal = (float) ($payload['total'] ?? 0);
        $email      = strtolower($payload['billing']['email'] ?? '');
        $phone      = $payload['billing']['phone'] ?? '';

        if ($orderTotal <= 0) {
            return response()->json(['message' => 'Total order tidak valid.'], 200);
        }

        $user = $this->findUser($email, $phone);

        if (!$user) {
            PendingPoint::create([
                'brand_id'         => $brand->id,
                'wc_order_id'      => $orderId,
                'order_total'      => $orderTotal,
                'customer_email'   => $email,
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
            source: 'webstore',
            invoiceNumber: 'WC-' . $orderId,
            meta: ['wc_order_id' => $orderId, 'platform' => 'woocommerce'],
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
        $signature = $request->header('X-WC-Webhook-Signature');
        if (!$signature) return false;

        $payload   = $request->getContent();
        $expected  = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($expected, $signature);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return '+' . $phone;
    }
}
