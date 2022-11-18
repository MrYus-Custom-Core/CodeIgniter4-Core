<?php

namespace App\Controllers;

use App\Controllers\Core\AuthController;

class User extends AuthController
{
    public function session_get() {
        $session = $this->account;
        
        print_r($session); die;
    }
    public function index_get()
    {
        $query['data'] = 'user';
        $query['join'] = [
            'user_detail' => 'user_detail_user_id = user_id'
        ];
        $query['select'] = [
            'user_name'                => 'name',
            'user_email'               => 'email',
            'user_created_datetime'    => 'created_datetime',
            'user_active_bool'         => 'active_bool',
            'user_detail_user_address' => 'address'
        ];
        $query['order']  = array('-user_name');
        $query['search'] = [
            'user_name',
            'user_email'
        ];

        $data = generateListData($this->get(), $query, $this->userModel);
        echo view('/User/user', $data);
    }

    public function index_post() {
        $this->userModel->transBegin();
        $this->userDetailModel->transBegin();
        $is_error = false;
        $error_code = '';
        $data = [];
        try{
            $user = [];
            $user['user_username'] = $this->post('username');
            $user['user_name'] = $this->post('name');
            $slug = generateSlug($user['user_name']);
            $user['user_slug'] = $slug;
            $user['user_email'] = $this->post('email');
            $user['user_password'] = $this->post('password');
            $user['user_active_bool'] = $this->post('active_bool');
            
            if (!$this->userModel->save($user)) {
                throw new \Exception('Error Validation', 1);
                return redirect()->back()->withInput()->with('validation', $this->userModel->errors());
            }
            
            if ($this->userModel->affectedRows() < 0) {
                throw new \Exception('Error Insert Database', 1);
            }
            $user['id'] = (string) $this->userModel->insertID();

            $mobilephone = sanitizePhoneNumber($this->post('mobilephone'));
            $code = generateCode($this->userDetailModel, 'user_detail_user_code', true, 'U');
            $userDetail = [];
            $userDetail['user_detail_user_detail_id'] = $user['id'];
            $userDetail['user_detail_user_address'] = $this->post('address');
            $userDetail['user_detail_user_mobilephone'] = $mobilephone;
            $userDetail['user_detail_user_code'] = $code;
            
            if (!$this->userDetailModel->save($userDetail)) {
                throw new \Exception('Error Validation', 1);
                return redirect()->back()->withInput()->with('validation', $this->userDetailModel->errors());
            }
            
            if ($this->userDetailModel->affectedRows() < 0) {
                throw new \Exception('Error Insert Database', 1);
            }
            $userDetail['id'] = (string) $this->userDetailModel->insertID();
        } catch (\Throwable $error) {
            $is_error = true;
            $error_code = $error->getMessage();
        }
        if ($is_error) {
            $this->userModel->transRollback();
            $this->userDetailModel->transRollback();
            session()->setFlashdata('failed', 'Gagal tambah data');
        } else {
            if ($this->userModel->transStatus() === false || $this->userDetailModel->transStatus() === false) {
                $this->userModel->transRollback();
                $this->userDetailModel->transRollback();
                session()->setFlashdata('failed', 'Gagal tambah data');
            }else {
                $this->userModel->transCommit();
                $this->userDetailModel->transCommit();
                session()->setFlashdata('success', 'Berhasil tambah data');
            }
        }
        return redirect()->to('/user');
    }
}