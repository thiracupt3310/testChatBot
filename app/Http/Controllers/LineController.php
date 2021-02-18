<?php

namespace App\Http\Controllers;

use App\Bot\BotController;
use App\Chat;
use Carbon\Carbon;
use Illuminate\Http\Request;


class LineController extends Controller
{

    public $bot;
    public $http_client;
    public $reply_token;
    public $user_id;
    public $room_id;
    public $group_id;
    public $message_id;
    public $message_type;
    public $message_time;
    public $message_text;
    public $message_image;
    public $message_sticker;
    public $message_video;
    public $message_audio;
    public $message_location;

    public $image_path;
    public $user_name;
    public $user_picture;
    public $user_status;
    public $user;
    public $source_type;
    public $event_type;
    public $line_message;

    public $bot_name;


    public function webhook(Request $request) {

        \Log::debug(json_encode($request->all()));

        $this->bot_name = $request->has('bot_name') ? $request->input('bot_name') : null;

		\Log::debug($this->bot_name);



        $this->bot = BotController::getInstant($this->bot_name);

        // extract and setting up data
        $chat = $this->extract_line_data($request);



        if ($chat){
            $this->bot->setChatMessage($chat);
            $this->bot->action();
        }

        return response()->json(['success' => 'success'], 200);
    }


    public function send_message(Request $request) {
        \Log::debug('Send messge log: '.json_encode($request->all()));
        $this->bot_name = $request->has('bot_name') ? $request->input('bot_name') : null;
        $this->bot = BotController::getInstant($this->bot_name);
        $this->bot->sendMessage($request);
    }

    public function send_custom_message(Request $request) {
        \Log::debug('send_custom_message');
        $this->bot_name = $request->has('bot_name') ? $request->input('bot_name') : null;
        $this->bot = BotController::getInstant($this->bot_name);
        $this->bot->sendCustomMessage($request);
    }

    public function send_multi_custom_message(Request $request) {
        \Log::debug('send_multi_custom_message');
        $this->bot_name = $request->has('bot_name') ? $request->input('bot_name') : null;
        $this->bot = BotController::getInstant($this->bot_name);
        $this->bot->sendMultiCustomMessage($request);
    }

    public function request_user_profile(Request $request){
        $this->bot_name = $request->has('bot_name') ? $request->input('bot_name') : null;
        $this->bot = BotController::getInstant($this->bot_name);
        return $this->bot->request_user_profile($request);
    }


    public function send_to(Request $request) {
        \Log::debug('send_to');
        $this->bot_name = $request->has('bot_name') ? $request->input('bot_name') : null;
        $this->bot = BotController::getInstant($this->bot_name);
        $users = UserController::get_all_users();
        $this->bot->sendMessageToUsers($request,$users);
    }

    public function create_bot(Request $request) {
        try {
            $bot = new \App\LineBot;
            $bot->create_bot($request->name, $request->access_token, $request->channel_token);

            return response()->json('success', 200);
        }
        catch(\Exception $exception) {
            return response()->json($exception->getMessage(), 524);
        }
    }


    function extract_line_data(Request $request){

        $data = $request->all();

        if(isset($data["events"])){
            $data = $data["events"][0];
        }

        $this->event_type = $this->get_event_type($data);
        $this->reply_token = $this->get_reply_token($data);
        if ($this->reply_token === '00000000000000000000000000000000')
            return false;

        $this->user_id  = $this->get_user_id($data);
        $this->message_id  = $this->get_message_id($data);
        $this->message_type = $this->get_reply_type($data);
        $this->source_type = $this->get_source_message($data);
        $this->message_time = $this->get_message_time($data);

        if ($this->source_type == "group"){
            $this->group_id = $this->get_group_id($data);
        }
        elseif ($this->source_type == "room"){
            $this->room_id = $this->get_room_id($data);
        }

        if($this->event_type == "message") {
            $this->get_message($data);
        }
        elseif ($this->event_type == "beacon")
        {
            $this->message_text = $this->get_dm_message($data);
        }

        $this->get_message_content();
        return $this->save_message();

    }


    function get_reply_type ($data) {
        try{
            return $data['message']['type'];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_message($data) {
        switch ($this->message_type) {
            case "text":
                $this->message_text = $this->get_text_message($data);
                break;
            case "sticker":
                $this->message_sticker = $this->get_sticker_message($data);
                break;
            case "location":
                $this->message_location = $this->get_location_message($data);
                break;
        }
    }

    function get_reply_token ($data) {
        try{
            return $data["replyToken"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_message_time ($data) {
        try{
            return $data["timestamp"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_text_message($data) {
        try{
            return $data["message"]["text"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function hex2str($hex) {
        $str = '';
        for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
        return $str;
    }

    function get_dm_message($data) {
        try{
            return $this->hex2str($data["beacon"]["dm"]);
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_sticker_message($data) {
        try{
            return (object) ["package_id" => $data["message"]["packageId"],
                "sticker_id" => $data["message"]["stickerId"]];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_location_message($data) {
        try{
            $title = "";

            if(isset($data["message"]["title"])){
                $title = $data["message"]["title"];
            }

            return (object) ["title" => $title,
                "address"   => $data["message"]["address"],
                "latitude"  => $data["message"]["latitude"],
                "longitude"  => $data["message"]["longitude"],
            ];
        }catch (\Exception $exception ){
            return $exception->getMessage();
        }
    }

    function get_user_id ($data) {
        try{
            return $data["source"]["userId"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_room_id ($data) {
        try{
            return $data["source"]["roomId"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_group_id ($data) {
        try{
            return $data["source"]["groupId"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_message_id ($data) {
        try{
            return $data["message"]["id"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_source_message ($data) {
        try{
            return $data["source"]["type"];
        }catch (\Exception $exception ){
            return "";
        }
    }

    function get_event_type ($data) {
        try{
            return $data["type"];
        }catch (\Exception $exception ){
            return "";
        }
    }


    function save_message() {

        try{
            $chat = new Chat;
            $chat->event_type = $this->event_type;
            $chat->source_type = $this->source_type;
            $chat->reply_token = $this->reply_token;
            $chat->user_id = $this->user_id;
            $chat->message_time = Carbon::createFromTimestamp(intval($this->message_time /1000));
            $chat->message_type = $this->message_type;
            $chat->message_id = $this->message_id;
            $chat->group_id = $this->group_id;
            $chat->room_id = $this->room_id;
            $chat->image_path = $this->image_path;
            $chat->bot_id = $this->bot->bot_id;

            if($this->event_type == "message") {
                switch ($this->message_type) {
                    case "text":
                        $chat->text = $this->message_text;
                        break;
                    case "sticker":
                        $chat->package_id = $this->message_sticker->package_id;
                        $chat->sticker_id = $this->message_sticker->sticker_id;
                        break;
                    case "location":
                        $chat->title = $this->message_location->title;
                        $chat->address = $this->message_location->address;
                        $chat->latitude = $this->message_location->latitude;
                        $chat->longitude = $this->message_location->longitude;
                        break;
                    default:
                        break;
                }
            }
            elseif ($this->event_type == "beacon")
            {
                $chat->text = $this->message_text;
            }


            if ($this->bot->bot_id != 7){
                $chat->save();
            }

            return $chat;

        }
        catch(\Exception $e){
            return null;
        }

    }

    function get_message_content() {

        if ($this->message_type === "image" ||
            $this->message_type === "video" ||
            $this->message_type === "audio" ||
            $this->message_type === "file" ){


			if(!$this->bot){
				$this->bot = BotController::getInstant($this->bot_name);
			}

			try {
                $response = $this->bot->getMessageContent($this->message_id);
            } catch(\Exception $error) {
			    return ;
            }

            if ($response->isSucceeded()) {

                $dataBinary = $response->getRawBody();

                switch ($this->message_type) {
                    case "image":
                        $time = Carbon::now()->timestamp;
                        $fileFullSavePath = public_path('line_image_upload').'/'.$time.'.jpg';
                        $this->image_path = url('line_image_upload').'/'.$time.'.jpg';
                        file_put_contents($fileFullSavePath,$dataBinary);
                        break;
                    case "video":
                        break;
                    case "audio":
                        break;
                    case "file":
                        break;
                }
            }
        }


    }


}
