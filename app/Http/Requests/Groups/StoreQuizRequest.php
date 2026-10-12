<?php

namespace App\Http\Requests\Groups;

use App\Models\Quiz;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('group'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::in([Quiz::TYPE_QUIZ, Quiz::TYPE_POLL])],
            'question' => ['required', 'string', 'max:1000'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct_index' => [
                Rule::requiredIf(! $this->isPoll()),
                'nullable',
                'integer',
                Rule::in(range(0, count($this->input('options', [])) - 1)),
            ],
        ];
    }

    /**
     * Polls have no correct answer, so they skip that field.
     */
    public function isPoll(): bool
    {
        return $this->input('type') === Quiz::TYPE_POLL;
    }
}
