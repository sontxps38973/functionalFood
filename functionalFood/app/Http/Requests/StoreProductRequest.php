<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:0,1',
            'product_type'   => 'required|in:simple,variable',
            'price'          => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'nullable|integer|min:0',
            'category_id'    => 'required|exists:categories,id',

            // Validate mảng ảnh phụ
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            // Validate mảng biến thể
            'variants'       => 'nullable|array',
            'variants.*.name'  => 'required_with:variants|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.sku'   => 'nullable|string|max:255',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}

