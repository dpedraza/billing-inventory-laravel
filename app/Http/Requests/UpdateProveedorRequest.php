<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proveedor'));
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:255'],
            'cuit_dni' => ['required', 'string', 'max:20', Rule::unique('proveedores', 'cuit_dni')->ignore($this->route('proveedor'))],
            'condicion_iva_id' => ['nullable', 'exists:condiciones_iva,id'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string'],
        ];
    }
}
