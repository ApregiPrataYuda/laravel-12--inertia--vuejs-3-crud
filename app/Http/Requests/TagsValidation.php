<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagsValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:50'
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'The category field is required.',
            'name.string' => 'The category must be a valid string.',
            'name.min' => 'The category must be at least 3 characters.',
            'name.max' => 'The category may not be greater than 50 characters.'
        ];
    }
    
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null
        ]);
    }
}
