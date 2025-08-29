<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'brand' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'model_id' => 'sometimes|required|exists:models,id',
            'category_id' => 'sometimes|required|exists:part_categories,id',

            // Imagen opcional en update
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_image' => 'sometimes|boolean',

            // Códigos
            'codes' => 'sometimes|array|min:1',
            'codes.*.code' => 'required_with:codes|string|max:100',
            'codes.*.type' => 'required_with:codes|in:internal,oem,aftermarket,universal',
            'codes.*.is_primary' => 'boolean',
            'codes.*.is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser jpeg, jpg, png o webp.',
            'image.max' => 'La imagen no puede superar 5MB.',
        ];
    }
}
