<?php

namespace App\Http\Requests\SharpFleet\Trips;

use Illuminate\Foundation\Http\FormRequest;

class StartTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Ensure optional select placeholders don't fail validation.
            'customer_id' => $this->input('customer_id') === '' ? null : $this->input('customer_id'),
            'customer_name' => $this->input('customer_name') === '' ? null : $this->input('customer_name'),
            'client_present' => $this->input('client_present') === '' ? null : $this->input('client_present'),
            'client_address' => $this->input('client_address') === '' ? null : $this->input('client_address'),
        ]);
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer'],
            'trip_mode'  => ['required', 'string'],
            'start_km'   => ['required', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'distance_method' => ['nullable', 'string'],
            'client_present' => ['nullable', 'in:0,1'],
            'client_address' => ['nullable', 'string'],
        ];
    }
}
