<?php

namespace App\Controllers;

use App\Controllers\Core\DataController;

class User extends DataController
{
    public function index_get()
    {
        $query['data'] = 'user';
        $query['join'] = [
            'user_detail' => 'user_detail_user_id = user_id'
        ];
        $query['select'] = [
            'user_name' => 'name',
            'user_email' => 'email',
            'user_created_datetime' => 'created_datetime',
            'user_active_bool' => 'active_bool',
            'user_detail_user_address' => 'address'
        ];
        $query['order'] = array('-user_name');

        $data = $this->generateListData($this->get(), $query, $this->userModel);

        echo view('/User/user', $data);
    }

    public function index_post() {
        $this->db->transBegin();
        $is_error = false;
        $error_code = '';
        try{
            $insert_data = [];
            $insert_data['user_name'] = $this->post('name');
            $insert_data['user_email'] = $this->post('email');
            $insert_data['user_password'] = $this->post('password');
            $insert_data['user_active_bool'] = $this->post('active_bool');
            
            if (!$this->userModel->save($insert_data)) {
                throw new \Exception('Error Validation', 1);
                return redirect()->back()->withInput()->with('validation', $this->userModel->errors());
            }
            
            if ($this->userModel->affectedRows() < 0) {
                throw new \Exception('Error Insert Database', 1);
            }
            $insert_data['id'] = (string) $this->userModel->insertID();
        } catch (\Throwable $error) {
            $is_error = true;
            $error_code = $error->getMessage();
        }
        if ($is_error) {
            $this->db->transRollback();
            session()->setFlashdata('failed', 'Gagal tambah data');
        } else {
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                session()->setFlashdata('failed', 'Gagal tambah data');
            }else {
                $this->db->transCommit();
                session()->setFlashdata('success', 'Berhasil tambah data');
            }
        }
        return redirect()->to('/user');
    }
}