<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineBot extends Model
{
    protected $table = 'line_bots';

    public static function get_bot_by_name($name) {
        return self::where('name', $name)->first();
    }

    public static function get_bot_by_id($id) {
        return self::where('id', $id)->first();
    }

    public function create_bot($name, $access_token, $channel_token) {
        $this->name = $name;
        $this->access_token = $access_token;
        $this->channel_token = $channel_token;
        $this->save();
    }
}
