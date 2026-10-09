<?php

namespace App\Models;

use CodeIgniter\Model;

class UserProfileModel extends Model
{
    protected $table            = 'user_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'profile_picture',
        'position',
        'department',
        'student_id',
        'year_level',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProfileByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }
}
