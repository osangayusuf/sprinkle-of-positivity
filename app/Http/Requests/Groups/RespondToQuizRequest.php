<?php

namespace App\Http\Requests\Groups;

use App\Models\Quiz;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondToQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user()->can('participate', $this->route('group'))) {
            return false;
        }

        /** @var Quiz $quiz */
        $quiz = $this->route('quiz');

        return ! $quiz->responses()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Quiz $quiz */
        $quiz = $this->route('quiz');

        return [
            'quiz_option_id' => [
                'required',
                Rule::exists('quiz_options', 'id')->where('quiz_id', $quiz->id),
            ],
        ];
    }
}
