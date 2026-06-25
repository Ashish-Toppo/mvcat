<?php

namespace App\Controllers;

use Core\Classes\Controller;
use Core\Classes\Request;
use Core\Classes\Response;

use App\Models\User as UserModel;

class User extends Controller {
    
    public function search_users(Request $req, Response $res) {
        $search_name = $req->query('q');
        
        $userModel = new UserModel;

        $users = $userModel->findByName($search_name, false, false);

        return $res->json($users);

    }
}