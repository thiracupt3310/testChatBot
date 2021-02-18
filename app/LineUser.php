<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineUser extends Model
{
    protected $table = 'users';

    public static function create_user($name, $picture, $status, $user_id, $bot_id) {
        try{
            $lineUser = self::where('user_id', $user_id)->where('bot_id', $bot_id)->first();

            if (is_null($lineUser)) {

                $lineUser = new LineUser();
                $lineUser->name = $name;
                $lineUser->picture = $picture;
                $lineUser->status = $status;
                $lineUser->user_id = $user_id;
                $lineUser->bot_id = $bot_id;
                $lineUser->save();
                return $lineUser;
            }
            else{
                return $lineUser;
            }
        }
        catch(\Exception $exception) {
            return $exception->getMessage();
        }


    }

    public static function check_user($user_id) {
        $user = self::where('user_id', $user_id)->first();

        if (is_null($user))
            return true;
        else return false;
    }

    public static function get_user_by_name($user_name) {
        return self::where("name", $user_name)->first();
    }
}
