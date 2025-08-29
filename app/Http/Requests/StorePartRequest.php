<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PartCode;

class StorePartRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'model_id' => 'required|exists:models,id',
            'category_id' => 'required|exists:part_categories,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'codes' => 'required|array|min:1',
            'codes.*.code' => 'required|string|max:100',
            'codes.*.type' => 'required|in:internal,oem,aftermarket,universal',
            'codes.*.is_primary' => 'boolean',
            'codes.*.is_active' => 'boolean',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del repuesto es obligatorio.',
            'brand.required' => 'La marca es obligatoria.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio debe ser mayor o igual a 0.',
            'model_id.required' => 'Debe seleccionar un modelo de vehículo.',
            'model_id.exists' => 'El modelo seleccionado no es válido.',
            'category_id.required' => 'Debe seleccionar una categoría.',
            'category_id.exists' => 'La categoría seleccionada no es válida.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png, webp.',
            'image.max' => 'La imagen no puede ser mayor a 5MB.',
            'codes.required' => 'Debe agregar al menos un código.',
            'codes.min' => 'Debe agregar al menos un código.',
            'codes.*.code.required' => 'El código es obligatorio.',
            'codes.*.code.max' => 'El código no puede tener más de 100 caracteres.',
            'codes.*.type.required' => 'El tipo de código es obligatorio.',
            'codes.*.type.in' => 'El tipo de código debe ser: interno, OEM, aftermarket o universal.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateUniqueCodes($validator);
            $this->validatePrimaryCode($validator);
        });
    }

    /**
     * Validar que no haya códigos duplicados
     */
    protected function validateUniqueCodes($validator)
    {
        $codes = collect($this->input('codes', []))
            ->pluck('code')
            ->map('trim')
            ->filter()
            ->toArray();

        // Verificar duplicados en el mismo formulario
        $duplicateCodesInForm = array_diff_assoc($codes, array_unique($codes));

        if (!empty($duplicateCodesInForm)) {
            $validator->errors()->add('codes',
                'No puede haber códigos duplicados: ' . implode(', ', array_unique($duplicateCodesInForm))
            );
            return;
        }

        // Verificar códigos que ya existen en la base de datos
        $existingCodes = PartCode::whereIn('code', $codes)
            ->with('part:id,name')
            ->get();

        if ($existingCodes->isNotEmpty()) {
            $codeErrors = [];
            foreach ($existingCodes as $existingCode) {
                $codeErrors[] = "{$existingCode->code} (usado en: {$existingCode->part->name})";
            }

            $validator->errors()->add('codes',
                'Los siguientes códigos ya están en uso: ' . implode(', ', $codeErrors)
            );
        }
    }

    /**
     * Validar que haya exactamente un código primary
     */
    protected function validatePrimaryCode($validator)
    {
        $codes = $this->input('codes', []);
        $primaryCodes = collect($codes)->where('is_primary', true);

        if ($primaryCodes->count() > 1) {
            $validator->errors()->add('codes', 'Solo puede haber un código principal.');
        }
    }

    /**
     * Preparar los datos para validación
     */
    protected function prepareForValidation()
    {
        // Asegurar que haya al menos un código primary
        $codes = $this->input('codes', []);

        if (empty($codes)) {
            return;
        }

        $primaryExists = collect($codes)->where('is_primary', true)->isNotEmpty();

        if (!$primaryExists) {
            $codes[0]['is_primary'] = true;
            $this->merge(['codes' => $codes]);
        }

        // Limpiar códigos vacíos
        $validCodes = collect($codes)->filter(function ($code) {
            return !empty(trim($code['code'] ?? ''));
        })->values()->toArray();

        if (!empty($validCodes)) {
            $this->merge(['codes' => $validCodes]);
        }
    }
}
