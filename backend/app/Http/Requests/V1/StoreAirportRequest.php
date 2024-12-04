<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
            'iata_code' => 'required|string|max:3',
            'airport_name' => 'required|string|max:255',
            'city_name' => 'required|string|max:255',
            'country_name' => 'required|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'iata_code' => Str::upper($this->iata_code),
            'airport_name' => Str::ucfirst($this->airport_name),
            'city_name' => Str::ucfirst($this->city_name),
            'country_name' => Str::ucfirst($this->country_name),
        ]);
    }
}
