<?php
namespace App\Http\Controllers\Api\V1;
use App\Models\ProviderService;
use App\Http\Controllers\Controller;

class ProviderServiceController extends Controller
{
    public function index()
    {
        return ProviderService::all();
    }

    public function toggle($id)
    {
        $provider = ProviderService::findOrFail($id);
        $provider->active = !$provider->active;
        $provider->save();

        return response()->json(['success' => true]);
    }
}
