<?php

namespace App\Controllers\Auth;

use App\Controllers\Core\AuthController;

class Login extends AuthController {

    public function login() {
        // Form validator
        if(!$this->validate([
            'userData' => [
                'rules' => 'required|alpha_numeric_punct',
                'errors' => [
                    'required' => 'Tidak boleh kosong',
                    'alpha_numeric_punct' => 'tidak sesuai format',
                ]
            ],
            'password' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Password harus diisi',
                ]
            ]
        ])) {
            $validation = \Config\Services::validation();
            return redirect()->back()->withInput()->with('validation', $validation);
        }
        

        // get admin data by userData
        $userData = $this->post('userData');
        $password = $this->post('password');

        if (!$this->checkUserExist($userData)) {
            session()->setFlashdata('failed', 'Username atau Email tidak terdaftar');
            return redirect()->back()->withInput();
        }
        
        // get user data
        $user_arr = $this->getUserData($userData);

        // password verify
        $userPassword = $user_arr['password'];
        $checkpwd = password_verify($password, $userPassword);

        if($checkpwd === false) {
            session()->setFlashdata('failed', 'Password yang anda masukkan salah');
            return redirect()->back()->withInput();
        }

        // session data config
        $dataSession = [];
        foreach($user_arr as $admin) {
            $dataSession = [
                'name' => $admin['name'],
                'username' => $admin['username'],
                'email' => $admin['email'],
            ];
        }
        // set session
        session()->set($dataSession);
    }

    public function logout() {
        session()->destroy();
    }
}