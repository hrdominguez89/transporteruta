<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;                 // REFACT: para regla unique con where
use App\Models\Invoice;                         // REFACT: usamos el modelo en el closure
use Illuminate\Support\Facades\Schema;          // REFACT: detectar columnas reales en la DB

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /* =========================================================================
     * TEAM: Compatibilidad de inputs ANTES de validar
     * - Aceptamos tanto `pointOfSale` (camel) como `point_of_sale` (snake) del form.
     * - Normalizamos y dejamos AMBAS llaves en el input para no romper controladores/vistas.
     * ========================================================================= */
    protected function prepareForValidation(): void
    {
        $pv = $this->input('pointOfSale', $this->input('point_of_sale', null));
        $pv = ($pv === '' || $pv === null) ? null : (int) $pv;

        $this->merge([
            'pointOfSale'   => $pv, // para código que use camelCase
            'point_of_sale' => $pv, // para código que use snake_case
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * REFACT:
     * - Unicidad por (number, punto de venta) pero detectando la COLUMNA real:
     *      point_of_sale  (entorno A)  ó  pointOfSale (entorno B)
     * - Si ninguna existe (esquema viejo), validamos por number solo y avisamos en el msg del closure.
     */
    public function rules()
    {
        // Obtenemos PV ya normalizado por prepareForValidation()
        $pv = $this->input('point_of_sale', $this->input('pointOfSale', 3));

        // Detectamos columna real del PV en la tabla invoices (para no romper entre entornos)
        $pvCol = null;
        if (Schema::hasColumn('invoices', 'point_of_sale')) {
            $pvCol = 'point_of_sale';
        } elseif (Schema::hasColumn('invoices', 'pointOfSale')) {
            $pvCol = 'pointOfSale';
        }

        // Construimos la regla unique base
        $uniqueRule = Rule::unique('invoices', 'number');
        // Si hay columna de PV, la aplicamos al unique. Si no, dejamos unique por number solo.
        if ($pvCol) {
            $uniqueRule = $uniqueRule->where(fn($q) => $q->where($pvCol, $pv));
        }

        return [
            'number' => [
                'required',
                'integer',
                'min:1',

                // REFACT: unicidad (number, PV si existe columna)
                $uniqueRule,

                // LEGACY (tu cierre original) PERO ahora tolerante a columnas distintas
                function ($attribute, $value, $fail) use ($pv, $pvCol) {
                    $exists = Invoice::where('number', $value)
                        ->when($pvCol, fn($q) => $q->where($pvCol, $pv))
                        ->exists();

                    if ($exists) {
                        if ($pvCol) {
                            $fail("Ya existe una factura con el número {$value} y punto de venta {$pv}.");
                        } else {
                            // Esquema sin columna de PV: dejamos constancia en el mensaje
                            $fail("Ya existe una factura con el número {$value}. (No se pudo verificar el Punto de Venta por diferencias de esquema)");
                        }
                    }
                },
            ],

            // Aceptamos ambos nombres del campo; pedimos al menos uno
            'point_of_sale' => 'required_without:pointOfSale|integer|min:1',
            'pointOfSale'   => 'sometimes|nullable|integer|min:1',

            'date'     => 'required|date',
            'clientId' => 'required|exists:clients,id',
        ];
    }

    /**
     * Mensajes custom para que sean más claros.
     */
    public function messages()
    {
        return [
            'number.unique' => 'Ya existe una factura con ese número para el Punto de Venta seleccionado.',
            'point_of_sale.required_without' =>
                'Debes indicar el Punto de Venta (campo point_of_sale o pointOfSale).',
            'point_of_sale.integer' => 'El Punto de Venta debe ser un número entero.',
            'pointOfSale.integer'   => 'El Punto de Venta debe ser un número entero.',
        ];
    }
}

