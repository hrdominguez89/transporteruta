<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTravelCertificateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'number' => [
                'nullable',
                'integer',
                // allow same number for the record being updated
                Rule::unique('travel_certificates', 'number')->ignore($this->route('id')),
            ],
            'date' => ['required','date'],
            'destiny' => ['required','string'],
            'clientId' => ['required','exists:clients,id'],
            'driverId' => ['required','exists:drivers,id'],
            'commission_type' => ['required','string'],
        ];
    }
}
