<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\WebstoreIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class IntegrationController extends Controller
{
    public function index()
    {
        $brands       = Brand::with('webstoreIntegration')->get();
        $integrations = WebstoreIntegration::with('brand')->get();
        return view('admin.integrations.index', compact('brands', 'integrations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id'       => 'required|exists:brands,id',
            'platform'       => 'required|in:woocommerce,mokapos,custom',
            'store_url'      => 'nullable|url',
            'consumer_key'   => 'nullable|string',
            'consumer_secret'=> 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'outlet_id'      => 'nullable|string',
            'app_id'         => 'nullable|string',
        ]);

        WebstoreIntegration::updateOrCreate(
            ['brand_id' => $request->brand_id, 'platform' => $request->platform],
            [
                'store_url'       => $request->store_url ?? '',
                'consumer_key'    => $request->consumer_key ? Crypt::encryptString($request->consumer_key) : '',
                'consumer_secret' => $request->consumer_secret ? Crypt::encryptString($request->consumer_secret) : '',
                'webhook_secret'  => $request->webhook_secret ? Crypt::encryptString($request->webhook_secret) : '',
                'outlet_id'       => $request->outlet_id,
                'app_id'          => $request->app_id,
                'is_active'       => true,
            ]
        );

        return back()->with('success', 'Integrasi berhasil disimpan.');
    }

    public function toggle(WebstoreIntegration $integration)
    {
        $integration->update(['is_active' => !$integration->is_active]);
        return back()->with('success', 'Status integrasi diubah.');
    }
}
