<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiDeviceSettingRequest extends FormRequest
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
            'device_id' => 'required|integer|exists:devices,id',
            'device_name' => 'nullable|string|max:255',
            'plant_type' => 'nullable|string|max:255',
            'mode' => 'nullable|integer|in:2,4',
            'sensor_min' => 'nullable|integer|min:0|max:4095',
            'sensor_max' => 'nullable|integer|min:0|max:4095',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'device_id.required' => 'Device ID harus disediakan',
            'device_id.exists' => 'Device tidak ditemukan',
            'device_name.max' => 'Nama device maksimal 255 karakter',
            'plant_type.max' => 'Tipe tanaman maksimal 255 karakter',
            'mode.in' => 'Mode harus 2 (AI Fuzzy) atau 4 (Manual)',
            'sensor_min.max' => 'Sensor min tidak boleh lebih dari 4095',
            'sensor_max.max' => 'Sensor max tidak boleh lebih dari 4095',
        ];
    }
}
