<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignPetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email:filter|max:255',
            'city' => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:1000',
            'consent' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'consent.accepted' => 'Anda harus menyetujui penggunaan data.',
            'comment.max' => 'Komentar maksimal 1000 karakter.',
        ];
    }
}
