<?php

namespace App\Http\Requests\SharpFleet\Admin\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],

            // Always present because we send a hidden 0 + checkbox 1
            'is_road_registered' => ['required', 'boolean'],

            // Only required when road registered = 1
            'registration_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf(fn () => $this->input('is_road_registered') == 1),
            ],

            'tracking_mode' => ['required', Rule::in(['distance', 'hours', 'none'])],

            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],

            // Optional descriptive field
            'vehicle_type' => ['nullable', 'string'],

            'vehicle_class' => ['nullable', 'string', 'max:100'],

            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_number.required' => 'The registration number field is required when the asset is road registered.',
            'tracking_mode.in' => 'Usage tracking must be one of: distance, hours, or none.',
        ];
    }
}
