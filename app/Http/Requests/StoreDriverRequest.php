<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza inputs antes de validar:
     * - vehicleId: '' → null
     * - percent: reemplaza coma por punto
     * - dni: quita todo lo que no sea dígito
     * - phone: quita espacios
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicleId' => $this->filled('vehicleId') ? $this->vehicleId : null,
            'percent'   => $this->has('percent') ? str_replace(',', '.', $this->percent) : null,
            'dni'       => $this->has('dni') ? preg_replace('/\D+/', '', $this->dni) : null,
            'phone'     => $this->has('phone') ? preg_replace('/\s+/', '', $this->phone) : null,
        ]);
    }

   public function rules(): array
    {
    return [
        'name'      => ['nullable','required','string','max:191'],
        'dni'       => ['nullable','string','max:10'],
        'address'   => ['nullable','string','max:191'],
        'city'      => ['nullable','string','max:191'],
        'phone'     => ['nullable','string','max:191'],
        'type'      => [ Rule::in(['PROPIO','TERCERO'])],

        // 👇 Solo permitido en TERCERO; si el tipo es PROPIO, se prohíbe que venga rellenado
        'percent'   => ['nullable','numeric','between:0,100','required_if:type,TERCERO','prohibited_unless:type,TERCERO'],

        'vehicleId' => ['nullable','integer','exists:vehicles,id'],
    ];
    }

    public function messages(): array
    {
    return [
        'type.in'                     => 'El tipo debe ser PROPIO o TERCERO.',
        'percent.required_if'         => 'El porcentaje es obligatorio cuando el tipo es TERCERO.',
        'percent.numeric'             => 'El porcentaje debe ser numérico.',
        'percent.between'             => 'El porcentaje debe estar entre 0 y 100.',
        'percent.prohibited_unless'   => 'El porcentaje solo puede completarse cuando el tipo es TERCERO.',
        'vehicleId.exists'            => 'El vehículo seleccionado no existe.',
    ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'nombre',
            'dni'       => 'DNI/CUIT',
            'address'   => 'dirección',
            'city'      => 'ciudad',
            'phone'     => 'teléfono',
            'type'      => 'tipo',
            'percent'   => 'porcentaje de la agencia',
            'vehicleId' => 'vehículo',
        ];
    }
}
