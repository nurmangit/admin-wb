<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleStoreRequest extends FormRequest
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
            'register_number' => 'required|max:255',
            'code' => 'required|max:255',
            'status' => 'required|in:active,inactive,pending',
            'type' => 'required|string|max:255',
            'vehicle_type_uuid' => 'required|uuid',
            'description' => 'nullable|string',
            'transporter_rate_uuid' => 'required|uuid',
            'transporter_uuid' => 'required|uuid',
            'ownership' => 'required|string',
        ];
    }
}
