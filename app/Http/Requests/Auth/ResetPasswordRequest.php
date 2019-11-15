<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckValidPasswordResetToken;
use App\Rules\CheckExistUser;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'bail',
                'required',
                'email',
                new CheckExistUser($this),
            ],
            'password' => 'bail|required|min:8|confirmed',
            'token' => [
                'bail',
                'required',
                new CheckValidPasswordResetToken($this),
            ],
        ];
    }
}
