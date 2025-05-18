<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;


class VerifyOtpRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
                'otp_code' => 'required|integer|digits:6',
                'token' => 'required|string|uuid',
            ];
    }
    public function messages()
    {
        return [
            'otp_code.required' => 'Verification code is required.',
            'otp_code.integer' => 'Verification code must be a number.',
            'otp_code.digits' => 'Verification code must be 6 digits.',
            'token.required' => 'Token is required.',
            'token.uuid' => 'Invalid token format.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => false,
            'message' => 'Validation failed. Please check the input fields.',
            'errors' => $validator->errors()
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
