<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * The onboarding details are optional here so accounts that never filled
     * them in (partners, older users) can still save their name and email,
     * but a WhatsApp number that's already on file can't be blanked out.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            'whatsapp_number' => ['sometimes', Rule::requiredIf($this->user()->whatsapp_number !== null), 'nullable', 'string', 'max:20'],
            'goals' => ['nullable', 'array', 'min:1'],
            'goals.*' => ['required', 'string', 'max:255'],
            'birthday_day' => ['nullable', 'integer', 'between:1,31'],
            'birthday_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
