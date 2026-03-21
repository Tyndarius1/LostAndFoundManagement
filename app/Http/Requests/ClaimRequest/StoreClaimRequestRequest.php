<?php

namespace App\Http\Requests\ClaimRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequestRequest extends FormRequest
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
            'found_item_id' => ['required', 'exists:found_items,id'],
            'proof_details' => ['required', 'string'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];    
    }
}
