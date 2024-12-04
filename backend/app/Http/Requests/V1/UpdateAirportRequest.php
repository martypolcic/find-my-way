<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateAirportRequest extends FormRequest
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
        if ($this->method() == 'PUT') {
            return [
                'iata_code' => ['required', 'string', 'max:3'],
                'airport_name' => ['required', 'string', 'max:255'],
                'city_name' => ['required', 'string', 'max:255'],
                'country_name' => ['required', 'string', 'max:255'],
            ];
        } else {
            return [
                'iata_code' => ['sometimes', 'string', 'max:3'],
                'airport_name' => ['sometimes', 'string', 'max:255'],
                'city_name' => ['sometimes', 'string', 'max:255'],
                'country_name' => ['sometimes', 'string', 'max:255'],
            ];
        }
    }

    protected function prepareForValidation()
    {
        $data = [];

        if (isset($this->iata_code)) {
            $data['iata_code'] = Str::upper($this->iata_code);
        }
        
        if (isset($this->airport_name)) {
            $data['airport_name'] = Str::ucfirst($this->airport_name);
        }

        if (isset($this->city_name)) {
            $data['city_name'] = Str::ucfirst($this->city_name);
        }

        if (isset($this->country_name)) {
            $data['country_name'] = Str::ucfirst($this->country_name);
        }

        $this->merge($data);
    }
}