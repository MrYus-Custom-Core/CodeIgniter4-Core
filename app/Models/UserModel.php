<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model 
{
    protected $table = "user";
    protected $primaryKey = "user_id";
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields = [
        'user_username',
        'user_slug',
        'user_name',
        'user_email',
        'user_password',
        'user_active_bool',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'user_created_datetime';
    protected $updatedField  = 'user_updated_datetime';
    protected $deletedField  = 'user_deleted_datetime';
}