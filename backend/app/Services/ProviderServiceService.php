<?php
namespace App\Services;

use App\Models\ProviderService;

class ProviderServiceService
{
    public function dissableProviderService($providerServiceId)
    {
        $providerService = ProviderService::find($providerServiceId);
        if ($providerService) {
            $providerService->active = false;
            $providerService->save();
        }
    }

    public function enableProviderService($providerServiceId)
    {
        $providerService = ProviderService::find($providerServiceId);
        if ($providerService) {
            $providerService->active = true;
            $providerService->save();
        }
    }

    public function getActiveFlightProviders()
    {
        return ProviderService::query()
            ->where('service_type', 'flight')
            ->where('active', true)
            ->pluck('provider_id')
            ->unique();
    }

    public function getActiveAccomodationProviders()
    {
        return ProviderService::query()
            ->where('service_type', 'accomodation')
            ->where('active', true)
            ->pluck('provider_id')
            ->unique();
    }
}
