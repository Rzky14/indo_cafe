<?php

declare(strict_types=1);

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'category' => ['required', 'string', Rule::in(['coffee', 'tea', 'snack', 'main_course', 'dessert'])],
            'image_url' => ['nullable', 'string', 'url', 'max:500'],
            'is_available' => ['nullable', 'boolean'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi',
            'price.required' => 'Harga wajib diisi',
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga tidak boleh negatif',
            'category.required' => 'Kategori wajib diisi',
            'category.in' => 'Kategori tidak valid',
            'stock.required' => 'Stok wajib diisi',
            'stock.integer' => 'Stok harus berupa angka',
            'stock.min' => 'Stok tidak boleh negatif',
        ];
    }
}
