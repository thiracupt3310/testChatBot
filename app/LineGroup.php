<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineGroup extends Model
{
    protected $table = 'groups';

    public static function create_group($group_id, $bot_id) {
        try{
            $lineGroup = self::where('group_id', $group_id)->where('bot_id', $bot_id)->first();

            if (is_null($lineGroup)) {

                $lineUser = new LineGroup();
                $lineUser->group_id = $group_id;
                $lineUser->bot_id = $bot_id;
                $lineUser->save();
                return $lineGroup;
            }
            else{
                return $lineGroup;
            }
        }
        catch(\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function get_group($group_id, $bot_id) {
        try{
            $lineGroup = self::where('group_id', $group_id)->where('bot_id', $bot_id)->first();

           return $lineGroup;
        }
        catch(\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function set_group_name($name) {
        $this->name = $name;
        $this->save();
    }
}
