<?php
/**
 * Created by PhpStorm.
 * User: Pongp
 * Date: 9/24/2018
 * Time: 3:26 PM
 */

namespace App\Bot;


class BotController
{
    public static function getInstant($name = null){

        switch ($name) {
            case "ami":
                return AmiLineBot::getInstant();
            default:
                return AmiLineBot::getInstant();
        }

    }
}

