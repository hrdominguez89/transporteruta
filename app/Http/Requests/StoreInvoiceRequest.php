<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $pointOfSale = request('pointOfSale', 3);
                    $exists = \App\Models\Invoice::where('number', $value)
                        ->where('pointOfSale', $pointOfSale)
                        ->exists();
                    
                    if ($exists) {
                        $fail("Ya existe una factura con el número {$value} y punto de venta {$pointOfSale}.");
                    }
                },
            ],
            'pointOfSale' => 'required|integer|min:1',
            'date' => 'required|date',
            'clientId' => 'required|exists:clients,id',
        ];
    }
}
