<?php

namespace App\Http\Requests\LostItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLostItemRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'color' => ['nullable', 'string', 'max:100'],
            'last_seen_location' => ['sometimes', 'required', 'string', 'max:255'],
            'date_lost' => ['sometimes', 'required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'matched', 'claimed', 'closed'])],
        ];
    }
}
