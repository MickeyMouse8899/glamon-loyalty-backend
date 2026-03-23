<?php

namespace App\Services;

use App\Jobs\SendPushNotification;
use App\Models\Brand;
use App\Models\BrandPointRule;
use App\Models\PointLedger;
use App\Models\TierRule;
use App\Models\Transaction;
use App\Models\UserBrandProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PointEngine
{
    public function calculatePoints(float $amount, int $brandId, string $source): int
    {
        $rule = BrandPointRule::where('brand_id', $brandId)
            ->where('source', $source)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->first();

        if (!$rule || $amount < $rule->min_transaction) {
            return 0;
        }

        return (int) floor(floor($amount / $rule->rp_per_point) * $rule->multiplier);
    }

    public function earnPoints(int $userId, int $brandId, float $amount, string $source, string $invoiceNumber, array $meta = []): Transaction
    {
        return DB::transaction(function () use ($userId, $brandId, $amount, $source, $invoiceNumber, $meta) {
            $points = $this->calculatePoints($amount, $brandId, $source);

            $transaction = Transaction::create([
                'user_id'        => $userId,
                'brand_id'       => $brandId,
                'invoice_number' => $invoiceNumber,
                'amount'         => $amount,
                'points_earned'  => $points,
                'source'         => $source,
                'status'         => 'completed',
                'wc_order_id'    => $meta['wc_order_id'] ?? null,
                'meta'           => $meta,
            ]);

            if ($points > 0) {
                $balance = $this->recordLedger(
                    $userId, $brandId, $transaction->id,
                    $points, 'earn',
                    "Earn dari {$source}: {$invoiceNumber}"
                );

                $brand = Brand::find($brandId);
                SendPushNotification::dispatch(
                    $userId,
                    "Poin Masuk! 🎉",
                    "+{$points} poin dari {$brand->name}. Total: {$balance} poin.",
                    ['type' => 'earn_points', 'brand_id' => $brandId, 'points' => $points]
                );
            }

            return $transaction;
        });
    }

    public function redeemPoints(int $userId, int $brandId, int $points, string $description): void
    {
        DB::transaction(function () use ($userId, $brandId, $points, $description) {
            $profile = UserBrandProfile::where('user_id', $userId)
                ->where('brand_id', $brandId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($profile->total_points < $points) {
                throw new \Exception('Poin tidak mencukupi.');
            }

            $balance = $this->recordLedger($userId, $brandId, null, -$points, 'redeem', $description);

            $brand = Brand::find($brandId);
            SendPushNotification::dispatch(
                $userId,
                "Redeem Berhasil! 🎁",
                "Kamu telah menukar {$points} poin {$brand->name}. Sisa: {$balance} poin.",
                ['type' => 'redeem_points', 'brand_id' => $brandId, 'points' => $points]
            );
        });
    }

    private function recordLedger(int $userId, int $brandId, ?int $transactionId, int $points, string $type, string $description): int
    {
        $profile = UserBrandProfile::firstOrCreate(
            ['user_id' => $userId, 'brand_id' => $brandId],
            ['member_code' => $this->generateMemberCode($brandId), 'total_points' => 0]
        );

        $newBalance = max(0, $profile->total_points + $points);
        $profile->update(['total_points' => $newBalance]);

        PointLedger::create([
            'user_id'        => $userId,
            'brand_id'       => $brandId,
            'transaction_id' => $transactionId,
            'points'         => $points,
            'type'           => $type,
            'description'    => $description,
            'balance_after'  => $newBalance,
        ]);

        $this->updateTier($profile, $newBalance, $brandId);

        return $newBalance;
    }

    private function updateTier(UserBrandProfile $profile, int $totalPoints, int $brandId): void
    {
        $tierRules = TierRule::where('brand_id', $brandId)
            ->orderByDesc('min_points')
            ->get();

        if ($tierRules->isEmpty()) {
            $tier = match(true) {
                $totalPoints >= 50000 => 'platinum',
                $totalPoints >= 20000 => 'gold',
                $totalPoints >= 5000  => 'silver',
                default               => 'bronze',
            };
        } else {
            $tier = $tierRules->first(fn($r) => $totalPoints >= $r->min_points)?->tier ?? $tierRules->last()->tier;
        }

        if ($profile->tier !== $tier) {
            $profile->update(['tier' => $tier]);
        }
    }

    private function generateMemberCode(int $brandId): string
    {
        $brand  = Brand::find($brandId);
        $prefix = strtoupper(substr($brand->slug ?? 'MBR', 0, 3));
        return $prefix . '-' . strtoupper(Str::random(8));
    }
}
