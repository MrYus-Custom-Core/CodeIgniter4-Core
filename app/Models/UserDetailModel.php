<?php

namespace App\Models;

use CodeIgniter\Model;

class UserDetailModel extends Model
{
    protected $table = "user_detail";
    protected $primaryKey = "user_detail_id";
    protected $allowedFields = [
        'user_detail_user_id',
        'user_detail_user_address',
        'user_detail_user_mobilephone',
        'user_detail_user_code'
    ];
}
