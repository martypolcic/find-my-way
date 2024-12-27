<?php 
namespace App\Integrations\OurAirports;

use Illuminate\Support\Facades\Validator;

class OurAirportsValidator {
    public function getRules(): array {
        return [
            'iata_code' => 'required|string|max:3|min:3',
            'name' => 'required|string|max:255|min:1',
            'iso_country' => 'required|string|max:255|min:1',
            'municipality' => 'required|string|max:255|min:1',
            'latitude_deg' => 'required|numeric|between:-90,90',
            'longitude_deg' => 'required|numeric|between:-180,180',
            'scheduled_service' => 'required|string|in:yes',
            'type' => 'required|string|regex:/airport/i',
        ];
    }

    public function validate(array $data): bool {
        if ($data['iata_code'] === '') {
            return false;
        }


        $validator = Validator::make($data, $this->getRules());
        
        return $validator->passes();
    }
}