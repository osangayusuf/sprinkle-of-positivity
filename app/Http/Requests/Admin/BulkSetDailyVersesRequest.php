<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

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

    /**
     * The submitted grid rows, each paired with its optional image upload.
     *
     * @return array<int, array{date: string, reference: string, text: string, image: UploadedFile|null}>
     */
    public function verseRows(): array
    {
        $rows = [];

        foreach (array_keys($this->array('rows')) as $key) {
            $image = $this->file("rows.$key.image");

            $rows[] = [
                'date' => $this->string("rows.$key.date")->value(),
                'reference' => $this->string("rows.$key.reference")->value(),
                'text' => $this->string("rows.$key.text")->value(),
                'image' => $image instanceof UploadedFile ? $image : null,
            ];
        }

        return $rows;
    }
}
