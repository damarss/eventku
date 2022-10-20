<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends ResourceController
{
    /**
     * Return users
     *
     * @return mixed
     */
    public function index()
    {
        $model = new UserModel();
        $data['users'] = $model->orderBy('role')->orderBy('name')->where('username <>', 'admin')->findAll();
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $model = new UserModel();
        $data = $model->where('id', $id)->first();
        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('Data user tidak ditemukan.');
        }
    }

    /**
     * Create a new user object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $model = new UserModel();
        $username = $this->request->getVar('username');
        $email = $this->request->getVar('email');

        // pengecekan username dan email yang sudah ada
        $userExist = $model->where('username', $username)->first();
        if ($userExist) return $this->failResourceExists('Username sudah digunakan');

        $userExist = $model->where('email', $email)->first();
        if ($userExist) return $this->failResourceExists('Email sudah digunakan');

        $name = $this->request->getVar('name');
        $password = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        $data = [
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'role' => 'user',
        ];

        $model->insert($data);
        $response = [
            'status' => 201,
            'error' => null,
            'messages' => [
                'success' => 'Data user berhasil ditambahkan.'
            ]
        ];
        return $this->respondCreated($response);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        // 
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $model = new UserModel();
        $email = $this->request->getVar('email');
        $name = $this->request->getVar("name");
        $role = $this->request->getVar("role");
        $password = $this->request->getVar("password");
        $currentUser = $model->where('id', $id)->first();

        $editedData = [];

        if (isset($name)) $editedData['name'] = $name;
        if (isset($email)) {
            $editedData['email'] = $email;
            // pengecekan email yang sudah ada
            $userExist = $model->where('email', $email)->first();
            if ($userExist && $currentUser != $userExist) return $this->failResourceExists('Email sudah ada');
        }
        if (isset($password)) $editedData['password'] = password_hash($password, PASSWORD_DEFAULT);
        if (isset($role)) $editedData['role'] = $role;


        try {
            $updated = $model->where('id', $id)->set($editedData)->update();
            if ($updated) {
                return $this->respondUpdated($updated);
            }
            return $this->failNotFound('Data user tidak ditemukan.');
        } catch (Exception $e) {
            return $this->failServerError('Gagal mengupdate data user.');
        }
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $model = new UserModel();
        try {
            $data = $model->where('id', $id)->first();
            if ($data) {
                $deleted = $model->where('id', $id)->delete();
                return $this->respondDeleted($deleted);
            }
            return $this->failNotFound('Data user tidak ditemukan.');
        } catch (Exception $e) {
            return $this->failServerError('Gagal menghapus data user.');
        }
    }
}
