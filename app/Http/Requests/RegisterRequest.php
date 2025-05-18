<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules()
    {
        return [
            'full_name'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users,email',
            'password'=>'required|string|min:8',
            'phone' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'full_name.required' => 'Full name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'profile_image.image' => 'The profile image must be a valid image file.',
            'profile_image.mimes' => 'Only jpeg, png, jpg, and gif formats are allowed.',
            'profile_image.max' => 'The profile image must not exceed 2MB.',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => false,
            'message' => 'Validation failed. Please check the input fields. ',
            'errors' => $validator->errors()
        ], 422);
        throw new ValidationException($validator, $response);
    }
}
