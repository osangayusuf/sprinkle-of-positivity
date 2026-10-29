<?php

namespace App\Http\Requests\Groups;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupMessageRequest extends FormRequest
{
    /**
     * Only the message's author, while still a participant, can edit it.
     */
    public function authorize(): bool
    {
        return $this->user()->can('participate', $this->route('group'))
            && $this->route('message')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
        ];
    }
}
