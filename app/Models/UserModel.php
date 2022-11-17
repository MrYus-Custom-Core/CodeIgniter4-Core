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
        'user_name',
        'user_email',
        'user_password',
        'user_active_bool',
    ];

    // Dates
    protected $createdField  = 'user_created_datetime';
    protected $updatedField  = 'user_updated_datetime';
    protected $deletedField  = 'user_deleted_datetime';

    // Validation
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    protected $validationRules = [
        "user_name" => ["label" => "Nama", "rules" => ["required"]],
        "user_email" => ["label" => "Nama", "rules" => ["required", "is_unique[user.user_email]", "valid_email"]],
        "user_password" => ["label" => "Nama", "rules" => ["required", "min_length[8]", "max_length[32]", "alpha_numeric_punct"]],
        "user_active_bool" => ["label" => "Nama", "rules" => ["required", "in_list[0, 1]"]],
    ];
}