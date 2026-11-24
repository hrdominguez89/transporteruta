<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
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
            'number' => 'required|integer|between:1,2147483647|unique:receipts,number',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'number.required' => 'Debe ingresar el numero del recibo.',
            'number.integer'  => 'El numero del recibo debe ser un numero entero.',
            'number.between'  => 'El numero del recibo no es valido.',
            'number.unique'   => 'El numero ingresado ya esta siendo usado por otro recibo.',
        ];
    }
}
