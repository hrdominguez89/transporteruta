<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool { return true; }

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
            'name'      => ['required','string','max:191'],
            'dni'       => ['required','string','max:191'],
            'address'   => ['required','string','max:191'],
            'city'      => ['required','string','max:191'],
            'phone'     => ['required','string','max:191'],
            'type'      => ['required', Rule::in(['PROPIO','TERCERO'])],
            'percent'   => ['nullable','numeric','between:0,100','required_if:type,TERCERO'],
            'vehicleId' => ['nullable','integer','exists:vehicles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'                 => 'El tipo debe ser PROPIO o TERCERO.',
            'percent.required_if'     => 'El porcentaje es obligatorio cuando el tipo es TERCERO.',
            'percent.numeric'         => 'El porcentaje debe ser numérico.',
            'percent.between'         => 'El porcentaje debe estar entre 0 y 100.',
            'vehicleId.exists'        => 'El vehículo seleccionado no existe.',
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
// Explicación:
// 1. La clase UpdateDriverRequest extiende FormRequest, lo que permite manejar la  validación de datos de forma centralizada.
// 2. El método authorize() permite que cualquier usuario pueda hacer esta solicitud.                               