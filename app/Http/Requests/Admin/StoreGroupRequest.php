<?php

namespace App\Http\Requests\Admin;

use App\Enums\PartnerStatus;
use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Group::class);
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
            'is_private' => ['boolean'],
            'manager_ids' => ['required', 'array', 'min:1'],
            'manager_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('partner_status', PartnerStatus::Approved->value)],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
