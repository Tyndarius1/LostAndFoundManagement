<?php

namespace App\Http\Requests\LostItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreLostItemRequest extends FormRequest
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
            'category_id' => ['required', 'exists:categories,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'color' => ['nullable', 'string', 'max:100'],
            'last_seen_location' => ['required', 'string', 'max:255'],
            'date_lost' => ['required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
