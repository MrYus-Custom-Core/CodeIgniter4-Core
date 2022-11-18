<?php
// Namespace
namespace App\Controllers\Core;

// Extend Base Controller
use App\Controllers\Core\BaseController;

// Load Models
use App\Models\UserModel;
use App\Models\UserDetailModel;

class DataController extends BaseController {

    protected $userModel;
    protected $userDetailModel;
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userDetailModel = new UserDetailModel();
        $this->encryptData = \Config\Services::encrypter();
    }
}