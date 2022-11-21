<?php
// Namespace
namespace App\Controllers\Core;

// Extend Base Controller
use App\Controllers\Core\BaseController;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

// Load Models
use App\Models\UserModel;
use App\Models\UserDetailModel;

class DataController extends BaseController {

    protected $userModel;
    protected $userDetailModel;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->userModel       = new UserModel();
        $this->userDetailModel = new UserDetailModel();
    }
}