<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class SearchFlightsRequest extends FormRequest
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
            'departureAirportIataCode' => ['required', 'string', 'size:3',],
            'destinationAirportIataCode' => ['string', 'size:3'],
            'departureDate' => ['required', 'date'],
            'returnDate' => ['date'],
            'adultCount' => ['required', 'integer', 'min:1'],
            'childCount' => ['required', 'integer', 'min:0'],
            'infantCount' => ['required', 'integer', 'min:0'],
        ];
    }
}
