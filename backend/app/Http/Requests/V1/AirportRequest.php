<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AirportRequest extends FormRequest
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
        $sometimesRequired = $this->method() === 'PATCH' ? 'sometimes' : 'required';
        return [
            'iata_code' => [
                $sometimesRequired,
                'string',
                'max:3',
                'uppercase',
            ],
            'airport_name' => [
                $sometimesRequired,
                'string',
                'max:255',
            ],
            'country_name' => [
                $sometimesRequired,
                'string',
                'max:255',
            ],
            'latitude_deg' => [
                $sometimesRequired,
                'decimal:0,8',
                'between:-90,90',
            ],
            'longitude_deg' => [
                $sometimesRequired,
                'decimal:0,8',
                'between:-180,180',
            ],
        ];
    }
}
