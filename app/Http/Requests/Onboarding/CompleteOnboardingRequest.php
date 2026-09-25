<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteOnboardingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'goals' => ['required', 'array', 'min:1'],
            'goals.*' => ['required', 'string', 'max:255'],
            'birthday_day' => ['nullable', 'integer', 'between:1,31'],
            'birthday_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
