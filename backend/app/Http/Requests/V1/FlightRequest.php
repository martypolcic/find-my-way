<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class FlightRequest extends FormRequest
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
            'flight_key' => [
                $sometimesRequired,
                'string',
                'max:255',
            ],
            'flight_number' => [
                $sometimesRequired,
                'string',
                'max:255',
            ],
            'departure_airport_iata_code' => [
                $sometimesRequired,
                'string',
                'max:3',
                'uppercase',
            ],
            'arrival_airport_iata_code' => [
                $sometimesRequired,
                'string',
                'max:3',
                'uppercase',
            ],
            'departure_date' => [
                $sometimesRequired,
                'date',
            ],
            'arrival_date' => [
                $sometimesRequired,
                'date',
            ],
        ];
    }
}
