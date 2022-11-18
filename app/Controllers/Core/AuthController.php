<?php

// Namespace
namespace App\Controllers\Core;

// Load BaseController
use App\Controllers\Core\DataController;

class AuthController extends DataController {

    protected function checkUserExist($userData) {
        $builder = $this->userModel;
        $builder->select('user_id AS id');
        $builder->where('user_username', $userData);
        $builder->orWhere('user_email', $userData);
        $builder->limit(1);
        $builder = $builder->get();
        $user    = $builder->getResultArray();

        if (count($user) < 0) {
            return false;
        }
        return true;
    }

    protected function getUserData($userData) {
        $builder = $this->userModel;
        $builder->select('user_id AS id');
        $builder->select('user_name AS name');
        $builder->select('user_username AS username');
        $builder->select('user_email AS email');
        $builder->select('user_password AS password');
        $builder->where('user_username', $userData);
        $builder->orWhere('user_email', $userData);
        $user = $builder->first();

        return $user;
    }

    protected function checkSessionExist() {
        $session = session()->get();
        if (empty($session)) {
            return false;
        }
        return true;
    }
}