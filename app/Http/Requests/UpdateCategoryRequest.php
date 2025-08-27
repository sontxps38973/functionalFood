<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy category ID từ route parameter
        $categoryId = $this->route('admin_category') ?? $this->route('category') ?? $this->route('id');
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('categories', 'name')->ignore($categoryId)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục không được để trống',
            'name.string' => 'Tên danh mục phải là chuỗi',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'name.min' => 'Tên danh mục phải có ít nhất 2 ký tự',
            'name.unique' => 'Tên danh mục này đã được sử dụng',
        ];
    }
}
