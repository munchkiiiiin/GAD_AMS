<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'email', 'password', 'reset_token', 'reset_token_expires_at', 'role', 'full_name', 'student_id', 'office_id', 'last_login', 'first_name', 'middle_name', 'last_name', 'profile_role', 'year_level'];

    // Dates
    protected $useTimestamps = false;

    /**
     * Find user by username, email or student_id
     */
    public function findByIdentity(string $identity)
    {
        return $this->where('username', $identity)
                    ->orWhere('email', $identity)
                    ->orWhere('student_id', $identity)
                    ->first();
    }
}

