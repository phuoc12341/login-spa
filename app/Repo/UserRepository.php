<?php

namespace App\Repo;

use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $email
     *
     * @return User $model
     */
    public function getUserByEmail(string $email)
    {
        return $this->model
            ->where('email', $email)
            ->first();
    }
}
