<?php

namespace App\Http\Requests\ClaimRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClaimRequestRequest extends FormRequest
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
            'proof_details' => ['sometimes', 'required', 'string'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'approved', 'rejected', 'released'])],
        ];
    }
}
