<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Repo\UserRepositoryInterface;
use Illuminate\Http\Request;
use Password;

class CheckExistUser implements Rule
{
    /**
     * Initialization to add elements to the request
     */
    protected $request;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = app(UserRepositoryInterface::class)->getUserByEmail($this->request->email);
        $this->request->merge(['userInstance' => $user]);

        return $user;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __(Password::INVALID_USER);
    }
}
