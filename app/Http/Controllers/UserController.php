<?php

namespace App\Http\Controllers;

use App\LineUser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get_line_user($user_name) {
        try{

            $user = LineUser::get_user_by_name($user_name);

            return response()->json($user, 200);
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function get_all_users() {
        try {
            $users = LineUser::get();
            return $users;
        }
        catch(\Exception $e) {
            return $e->getMessage();
        }
    }
}
