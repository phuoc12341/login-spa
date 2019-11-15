<?php

namespace App\Repo;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param string $email
     *
     * @return \App\Models\User $model
     */
    public function getUserByEmail(string $email);
}
