<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkSetDailyVersesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(Role::ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Either a grid of rows or a CSV file must be submitted, never both.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rows' => ['required_without:file', 'array', 'min:1'],
            'rows.*.date' => ['required_with:rows', 'date'],
            'rows.*.reference' => ['required_with:rows', 'string', 'max:255'],
            'rows.*.text' => ['required_with:rows', 'string'],
            'rows.*.image' => ['nullable', 'image', 'max:2048'],
            'file' => ['required_without:rows', 'file', 'mimes:csv,txt'],
        ];
    }
}
