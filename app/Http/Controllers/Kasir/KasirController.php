<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Redemption;
use App\Models\User;
use App\Services\PointEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function __construct(protected PointEngine $pointEngine) {}

    public function index()
    {
        if (!session('admin_user_id')) {
            return redirect()->route('admin.login');
        }

        $user = User::find(session('admin_user_id'));
        if (!$user || !$user->isKasir()) {
            return redirect()->route('admin.login');
        }

        $brands = Brand::where('is_active', true)->get();
        return view('kasir.index', compact('brands'));
    }

    public function cariMember(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $phone = $this->normalizePhone($request->phone);
        $user  = User::where('phone', $phone)->with('brandProfiles.brand')->first();

        if (!$user) {
            return response()->json(['found' => false, 'message' => 'Member tidak ditemukan.']);
        }

        return response()->json([
            'found' => true,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'phone'  => $user->phone,
                'brands' => $user->brandProfiles->map(fn($p) => [
                    'brand_id'     => $p->brand_id,
                    'brand_name'   => $p->brand->name,
                    'member_code'  => $p->member_code,
                    'total_points' => $p->total_points,
                    'tier'         => $p->tier,
                ]),
            ],
        ]);
    }

    public function prosesTransaksi(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'brand_id' => 'required|exists:brands,id',
            'amount'   => 'required|numeric|min:1000',
        ]);

        $invoice     = 'KSR-' . strtoupper(Str::random(10));
        $transaction = $this->pointEngine->earnPoints(
            userId: $request->user_id,
            brandId: $request->brand_id,
            amount: $request->amount,
            source: 'instore',
            invoiceNumber: $invoice,
        );

        $user = User::find($request->user_id);

        return response()->json([
            'success'       => true,
            'message'       => 'Transaksi berhasil!',
            'invoice'       => $invoice,
            'points_earned' => $transaction->points_earned,
            'total_points'  => $user->getPointsForBrand($request->brand_id),
        ]);
    }

    public function verifikasiRedemption(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $redemption = Redemption::where('redemption_code', strtoupper($request->code))
            ->with(['user', 'reward.brand'])->first();

        if (!$redemption) {
            return response()->json(['valid' => false, 'message' => 'Kode tidak ditemukan.']);
        }
        if ($redemption->status === 'claimed') {
            return response()->json(['valid' => false, 'message' => 'Kode sudah pernah digunakan.']);
        }
        if ($redemption->status === 'expired' || $redemption->expires_at < now()) {
            return response()->json(['valid' => false, 'message' => 'Kode sudah kadaluarsa.']);
        }

        return response()->json([
            'valid'      => true,
            'redemption' => [
                'id'         => $redemption->id,
                'code'       => $redemption->redemption_code,
                'reward'     => $redemption->reward->name,
                'brand'      => $redemption->reward->brand->name,
                'member'     => $redemption->user->name,
                'phone'      => $redemption->user->phone,
                'expires_at' => $redemption->expires_at,
            ],
        ]);
    }

    public function claimRedemption(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $redemption = Redemption::where('redemption_code', strtoupper($request->code))
            ->with(['user', 'reward'])->first();

        if (!$redemption || $redemption->status !== 'pending' || $redemption->expires_at < now()) {
            return response()->json(['success' => false, 'message' => 'Kode tidak valid atau sudah kadaluarsa.']);
        }

        $redemption->update(['status' => 'claimed', 'claimed_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Reward '{$redemption->reward->name}' berhasil di-claim untuk {$redemption->user->name}.",
        ]);
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
