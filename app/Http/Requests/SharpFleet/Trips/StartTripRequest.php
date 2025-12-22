<?php

namespace App\Http\Requests\SharpFleet\Trips;

use Illuminate\Foundation\Http\FormRequest;

class StartTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer'],
            'trip_mode'  => ['required', 'string'],
            'start_km'   => ['required', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string'],
            'distance_method' => ['nullable', 'string'],
        ];
    }
}
