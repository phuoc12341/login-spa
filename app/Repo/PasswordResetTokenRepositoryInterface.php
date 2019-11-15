<?php

namespace App\Repo;

interface PasswordResetTokenRepositoryInterface extends BaseRepositoryInterface
{
    public function getObjectToken($token);

    public function deleteByToken(string $token);

    public function deleteTokenExistingByEmail(string $email);

    public function findTokenByEmail(string $email);
}
