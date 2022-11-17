<?php

namespace App\Models;

use CodeIgniter\Model;

class UserDetailModel extends Model
{
    protected $table = "user_detail";
    protected $primaryKey = "user_detail_id";
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'user_detail_user_id',
        'user_detail_user_address',
        'user_detail_user_mobilephone',
        'user_detail_user_code'
    ];

    // Validation
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    protected $validationRules = [
        "user_detail_id" => "required|is_natural_no_zero",
        "user_detail_user_id" => "required|is_natural_no_zero",
        "user_detail_user_address" => "required|max_length[255]|alpha_numeric_punct",
        "user_detail_user_mobilephone" => "required|max_length[20]",
        "user_detail_user_code" => "required|min_length[2]|max_length[20]",
    ];
    
    protected $allowCallbacks = true;
}
