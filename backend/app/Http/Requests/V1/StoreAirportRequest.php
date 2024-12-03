<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAirportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'iataCode' => 'required|string|max:3',
            'airportName' => 'required|string|max:255',
            'cityName' => 'required|string|max:255',
            'countryName' => 'required|string|max:255',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'iata_code' => strtoupper($this->iataCode),
            'airport_name' => ucwords($this->airportName),
            'city_name' => ucwords($this->cityName),
            'country_name' => ucwords($this->countryName),
        ]);
    }
}
