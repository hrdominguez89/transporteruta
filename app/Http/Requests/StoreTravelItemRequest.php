<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTravelItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        // Reglas base (siempre)
        $rules = [
            'type'        => ['required', Rule::in([
                'REMITO','HORA','KILOMETRO','PEAJE','ADICIONAL','FIJO','MULTIDESTINO','DESCARGA','DESCUENTO'
            ])],
            'description' => ['nullable','string','max:255'],
        ];

        // Si es REMITO: pedimos el número y NO pedimos price
        if ($type === 'REMITO') {
            $rules['remito_number'] = ['required','string','max:50'];

            // (Opcional) evitar remitos duplicados dentro de la misma constancia:
            // $tcId = $this->route('travelCertificateId') ?? $this->route('id');
            // if ($tcId) {
            //   $rules['remito_number'][] = Rule::unique('travel_items','remito_number')
            //       ->where('travelCertificateId', $tcId);
            // }

        } else {
            // Para el resto de los tipos: price es obligatorio
            $rules['price'] = ['required','numeric'];
        }

        // Campos específicos por tipo (desde tu UI):
        if ($type === 'HORA') {
            $rules['totalHours']   = ['required','integer','min:0'];
            // tus <option> son '00','15','30','45' => validamos como strings
            $rules['totalMinutes'] = ['required', Rule::in(['00','15','30','45'])];
        }

        if ($type === 'KILOMETRO') {
            $rules['distance'] = ['required','numeric','min:0'];
        }

        if ($type === 'ADICIONAL') {
            $rules['porcentaje'] = ['required','numeric','min:0','max:100'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required'          => 'Seleccioná un tipo de ítem.',
            'type.in'                => 'El tipo seleccionado no es válido.',
            'remito_number.required' => 'El N° de Remito es obligatorio.',
            'price.required'         => 'El precio es obligatorio.',
            'totalHours.required'    => 'Las horas son obligatorias.',
            'totalMinutes.required'  => 'Los minutos son obligatorios.',
            'totalMinutes.in'        => 'Los minutos deben ser 00, 15, 30 o 45.',
            'distance.required'      => 'La distancia es obligatoria.',
            'porcentaje.required'    => 'El porcentaje es obligatorio.',
        ];
    }
}
