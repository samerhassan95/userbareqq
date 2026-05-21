<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'product_role' => 'required|in:one_time,strategy',
            'total_price' => 'required|numeric|min:0',
            'duration' => 'required_if:product_role,strategy|in:month,year',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'product_role.required' => 'Product role is required.',
            'product_role.in' => 'Product role must be one_time or strategy.',
            'duration.required_if' => 'Duration is required for strategy products.',
            'duration.in' => 'Duration must be month or year.',
        ];
    }
}
