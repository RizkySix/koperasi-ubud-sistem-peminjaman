<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|min:3',
            'phone_number' => 'required|string|min:11|max:14|unique:users',
            'password' => 'required|confirmed|string|min:6',
            'address' => 'required|string|min:6',
            'birth_date' => 'required|date'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            'validation_errors' => $validator->getMessageBag()
        ] , 400));
    }
}
