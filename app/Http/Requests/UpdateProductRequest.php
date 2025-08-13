<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'product_type' => 'required|in:simple,variable',
            'price' => 'nullable|numeric',
            'stock_quantity' => 'nullable|integer',
            'image' => 'nullable|string',
        ];
    }
}

