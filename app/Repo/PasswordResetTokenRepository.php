<?php

namespace App\Repo;

use App\Repo\PasswordResetTokenRepositoryInterface;
use App\Models\PasswordResetToken;

class PasswordResetTokenRepository extends BaseRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(PasswordResetToken $model)
    {
        parent::__construct($model);
    }

    public function getObjectToken($token)
    {
        return $this->model->where('token', $token)->first();
    }
    
    public function deleteByToken(string $token)
    {
        return $this->model->where('token', $token)->delete();
    }

    public function deleteTokenExistingByEmail(string $email)
    {
        return $this->model->where('email', $email)->delete();
    }

    public function findTokenByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }
}
