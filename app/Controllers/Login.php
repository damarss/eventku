<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;
use Firebase\JWT\JWT;

class Login extends ResourceController
{
    public function index() {
        $model = new UserModel();
        $usernameInput = $this->request->getVar('username');
        $passwordInput = $this->request->getVar('password');
        try {
            $login = $model->where('username', $usernameInput)->first();
            $verified = password_verify($passwordInput, $login['password']);
            if (!$verified) {
                return $this->fail('Wrong credential');
            }

            $key = getenv('JWT_SECRET');
            $iat = time();
            $exp = $iat + 86400;  // akan expired dalam 1 hari

            $payload = [
                'iat' => $iat,
                'nbf' => $iat, 
                'exp' => $exp,
                'uid' => $login['id'],
                'email' => $login['email'],             
                'role' => $login['role'],
            ];

            $token = JWT::encode($payload, $key, 'HS256');

            $response = [
                'message' => 'Login succesful',
                'name'    => $login['name'],
                'role'    => $login['role'],
                'uid'     => $login['id'],
                'token'   => $token,
            ];
            return $this->respond($response);
        } catch (Exception $e) {
            return $this->fail('Wrong credential');
        }
    }
}