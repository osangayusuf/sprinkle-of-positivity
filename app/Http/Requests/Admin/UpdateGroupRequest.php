<?php

namespace App\Http\Requests\Admin;

use App\Enums\GroupStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('group'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'starts_on' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(GroupStatus::class)],
            'is_private' => ['boolean'],
            'manager_id' => ['required', 'integer', 'exists:users,id'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
