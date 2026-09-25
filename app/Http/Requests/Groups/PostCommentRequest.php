<?php

namespace App\Http\Requests\Groups;

use App\Models\Insight;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('participate', $this->route('group'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Insight $insight */
        $insight = $this->route('insight');

        return [
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => [
                'nullable',
                Rule::exists('comments', 'id')->where('commentable_type', $insight->getMorphClass())->where('commentable_id', $insight->id),
            ],
        ];
    }
}
