<?php

namespace App\Http\Requests\Groups;

use App\Models\Quiz;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Editing is only allowed while the quiz has zero responses — once
     * someone has answered, changing the question/options/correct answer
     * would invalidate what they already saw and answered.
     */
    public function authorize(): bool
    {
        $quiz = $this->route('quiz');

        return $quiz instanceof Quiz
            && $this->user()->can('manage', $this->route('group'))
            && $quiz->responses()->doesntExist();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct_index' => [
                'required',
                'integer',
                Rule::in(range(0, count($this->input('options', [])) - 1)),
            ],
        ];
    }
}
