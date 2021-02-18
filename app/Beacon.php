<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Beacon extends Model
{
    protected $table = 'beacon';

    public static function create_beacon($user_id, $name) {

        $beaconReplay = new Beacon();
        $beaconReplay->user_id = $user_id;
        $beaconReplay->name = $name;
        $beaconReplay->save();
        return $beaconReplay;
    }

    public static function getBeaconToday($user_id)
    {
        $now = Carbon::now();
        return self::where('user_id', $user_id)->whereDate('created_at', $now)->first();
    }
}
