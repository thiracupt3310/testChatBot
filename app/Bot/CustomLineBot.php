<?php
namespace App\Bot;
use App\Http\Controllers\UserController;
use App\LineGroup;
use Carbon\Carbon;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Illuminate\Http\Request;
use App\LineMessage;
use App\LineUser;
use Illuminate\Support\Facades\Storage;

/**
 * Created by PhpStorm.
 * User: sra
 * Date: 9/21/2018
 * Time: 9:41 PM
 */
class CustomLineBot
{
    public $bot;

    public $chat_message;
    public $line_message;
    public $bot_id;

    public $response_message_list;
    public $request_message_list;

    public $line_user;
    public $line_group;

    public $access_token;


    function __construct($id, $access_token, $channel_secret)
    {
        $this->bot_id = $id;

        $this->access_token = $access_token;
        $http_client = new CurlHTTPClient($access_token);
        $this->bot = new LINEBot($http_client, ['channelSecret' => $channel_secret]);

    }

    public function initReplyMessage($name = ""){

        $source_type =  $this->chat_message->source_type;

        if ($source_type == "user"){

            $this->response_message_list = [
                //add more message here

            ];

            $this->request_message_list = [
                //add more message here
            ];

        }else if ($source_type == "group"){

            $this->response_message_list = [
                //add more message here

            ];

            $this->request_message_list = [
                //add more message here
            ];


        }else if ($source_type == "room"){

            $this->response_message_list = [
                //add more message here

            ];

            $this->request_message_list = [
                //add more message here
            ];
        }
    }

    public function getChatMessage()
    {
        return $this->chat_message;
    }


    public function setChatMessage($chat_message)
    {
        $this->chat_message = $chat_message;
        $username = "";
        if($this->chat_message->event_type === "message") {
            $this->get_user_profile();
            $username = $this->line_user['username'];
        }
        else if ($this->chat_message->event_type === "follow") {
            $this->get_user_profile();
            $username = $this->line_user->username;
        }
        else if ($this->chat_message->event_type === "join") {
            if ($this->chat_message->source_type === "group")
                $this->set_group();
        }
        $this->initReplyMessage($username);

        $this->line_message = new LineMessage();

        $this->line_message->setRequestMessageList($this->request_message_list);
        $this->line_message->setResponseMessageList($this->response_message_list);


    }

    function request_user_profile(Request $request) {

        $group_id = $request->group_id;
        $user_id = $request->user_id;

        $res = $this->bot->getGroupMemberProfile($group_id, $user_id);
        return $res->getJSONDecodedBody();
    }


    function get_user_profile() {
        $source_type = $this->chat_message->source_type;

        switch ($source_type) {
            case "user":
                $res = $this->bot->getProfile($this->chat_message->user_id);
                break;
            case "room":
                $res = $this->bot->getRoomMemberProfile($this->chat_message->room_id, $this->chat_message->user_id);
                break;
            case "group":
                $res = $this->bot->getGroupMemberProfile($this->chat_message->group_id, $this->chat_message->user_id);
                break;
            default:
                $res = $this->bot->getProfile($this->chat_message->user_id);
                break;

        }

        if ($res->isSucceeded()) {
            $profile = $res->getJSONDecodedBody();

            $user_name = $profile['displayName'];
            $user_status = isset($profile['statusMessage'])?$profile['statusMessage']:"";
            $user_picture = $profile['pictureUrl'];
            $this->line_user = LineUser::create_user($user_name,$user_picture,$user_status,$this->chat_message->user_id, $this->bot_id);

        }

    }

    function set_group() {
        $this->line_group = LineGroup::create_group($this->chat_message->group_id,  $this->bot_id);
    }

    function set_group_name($name) {
        $this->line_group->set_group_name($name);
    }

    function sendCustomMessage(Request $request){

    }

    function sendMultiCustomMessage(Request $request){

    }


    // Send Message From Request
    function sendMessage(Request $request){

        try{
            $type = $request->type;
            $user_id = $request->userId;
            if ($type == "text") {
                $message = $request->message;
                $this->push_message($user_id, $message);
            }
            else if ($type == "sticker") {
                $package_id = $request->packageId;
                $sticker_id = $request->stickerId;
                $this->push_sticker($user_id, $package_id, $sticker_id);
            }
            else if ($type == "image") {
                $image = $request->image;
                if ($request->has("imagePreview"))
                    $image_prev = $request->imagePreview;
                else
                    $image_prev = $image;
                $this->push_image($user_id, $image, $image_prev);
            }

        }
        catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    function sendMessageToUsers(Request $request, $users){
        try{
            $type = $request->type;

            foreach ( $users as $user) {
                if ($type == "text") {
                    $message = $request->message;
                    $this->push_message($user->user_id, $message);
                }
                else if ($type == "sticker") {
                    $package_id = $request->packageId;
                    $sticker_id = $request->stickerId;
                    $this->push_sticker($user->user_id, $package_id, $sticker_id);
                }
                else if ($type == "image") {
                    $image = $request->image;
                    if ($request->has("imagePreview"))
                        $image_prev = $request->imagePreview;
                    else
                        $image_prev = $image;
                    $this->push_image($user->user_id, $image, $image_prev);
                }
            }

        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function push_message($user_id, $message) {
        $this->bot->pushMessage($user_id, $this->create_text_message($message));
    }

    function push_sticker($user_id, $package_id, $sticker_id) {
        $this->bot->pushMessage($user_id, $this->create_sticker_message($package_id, $sticker_id));
    }

    function push_image($user_id, $image, $image_prev) {
        $imageSave = file_get_contents("$image");
        $imagePrevSave = file_get_contents("$image_prev");

        $filename = uniqid();

        $filePath = public_path("image/tmp/$filename.jpg");
        $filePrevPath = public_path("image/tmp/$filename"."_prev.jpg");

        $s3 = Storage::disk('s3')->put('images/'."$filename.jpg", $filePath);
        $s3prev = Storage::disk('s3')->put('images/'."$filename"."_prev.jpg", $filePath);

        \Log::debug('s3: '.$s3);

        file_put_contents($filePath, $imageSave);
        file_put_contents($filePrevPath, $imagePrevSave);

        $fileUrl = url("image/tmp/$filename.jpg");
        $filePrevUrl = url("image/tmp/$filename"."_prev.jpg");

        $fileUrl = str_replace("http://", "https://", $fileUrl);
        $filePrevUrl = str_replace("http://", "https://", $filePrevUrl);

        $this->bot->pushMessage($user_id, $this->create_image_message($fileUrl, $filePrevUrl));

        // sleep(5);

        // \File::delete($filePath, $filePrevPath);
    }


    //Reply Message

    function action($option = [], $chat_message = null){

        if($chat_message){
            $this->setChatMessage($chat_message);
        }

        if ($this->chat_message->source_type == "user"){
            $this->userReplyAction();
        }
        else if ($this->chat_message->source_type == "group") {
            $this->groupReplyAction();
        }
        else if ($this->chat_message->source_type == "room") {
            $this->roomReplyAction();
        }

    }

    public function userReplyAction(){


    }

    public function groupReplyAction(){

    }

    public function roomReplyAction(){

    }

    public function word_contain ($message, $needle) {
        if (strpos($message, $needle) !== false)
            return true;
        else return false;
    }

    public function words_contain ($message, $needles) {

        $message = strtolower($message);
        foreach ($needles as $needle){
            if (strpos($message, $needle) !== false)
                return true;
        }
        return false;
    }

    public function word_contain_allcase ($message, $needle) {
        if (strpos( strtolower($message) , $needle) !== false)
            return true;
        else return false;
    }


    function reply_text_message($message = ""){
        $text_message_builder = $this->create_text_message($message);
        $this->bot->replyMessage($this->chat_message->reply_token, $text_message_builder);
    }
    function create_text_message($message = "", $extra_text = null){
        return new LINEBot\MessageBuilder\TextMessageBuilder($message, $extra_text);
    }
    function create_quick_reply($component = null){
        return new LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($component);
    }

    function create_camera_template_action($label) {
        return new LINEBot\TemplateActionBuilder\CameraTemplateActionBuilder("$label");
    }

    function create_camera_roll_template_action($label) {
        return new LINEBot\TemplateActionBuilder\CameraRollTemplateActionBuilder($label);
    }

//    function create_date_time_picker_template_action($label) {
//        return new LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder($label);
//    }

    function create_location_template_action($label) {
        return new LINEBot\TemplateActionBuilder\LocationTemplateActionBuilder($label);
    }

    function create_message_template_action($label, $value) {
        return new LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder($label, $value);
    }



    /**
     * see package_id and sticker_id in 'https://developers.line.me/media/messaging-api/sticker_list.pdf'
     * @param $package_id
     * @param $sticker_id
     */
    function reply_sticker_message($package_id, $sticker_id) {
        $sticker_message_builder = $this->create_sticker_message($package_id, $sticker_id);
        $this->bot->replyMessage($this->chat_message->reply_token, $sticker_message_builder);
    }
    function create_sticker_message($package_id, $sticker_id) {
        return new LINEBot\MessageBuilder\StickerMessageBuilder($package_id, $sticker_id);
    }

    function reply_image_message($image, $image_prev) {
        $image_message_builder = $this->create_image_message($image, $image_prev);
        $this->bot->replyMessage($this->chat_message->reply_token, $image_message_builder);
    }

    function push_image_message($image, $image_prev) {
        $image_message_builder = $this->create_image_message($image, $image_prev);
        $this->bot->pushMessage($this->chat_message->user_id, $image_message_builder);
    }

    function create_image_message($image, $image_prev) {
        return new LINEBot\MessageBuilder\ImageMessageBuilder($image, $image_prev);
    }

    function reply_location_message($title, $address, $latitude, $longitude) {
        $message_builder = $this->create_location_message($title, $address, $latitude, $longitude);
        $this->bot->replyMessage($this->chat_message->reply_token, $message_builder);
    }
    function create_location_message($title, $address, $latitude, $longitude){
        return new LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $latitude, $longitude);
    }

    function reply_video_message($originalContentUrl, $previewImageUrl) {
        $message_builder = $this->create_video_message($originalContentUrl, $previewImageUrl);
        $this->bot->replyMessage($this->chat_message->reply_token, $message_builder);
    }
    function create_video_message($originalContentUrl, $previewImageUrl){
        return new LINEBot\MessageBuilder\VideoMessageBuilder($originalContentUrl, $previewImageUrl);
    }

    function quick_reply_message($message = "", $quick_reply_array =[]) {
        $quick_reply = new LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder($quick_reply_array);

        $message_template = $this->create_text_message($message, $quick_reply);
        $this->bot->replyMessage($this->chat_message->reply_token, $message_template);

    }

    function reply_flex_json($json){

        $reply_token = $this->chat_message->reply_token;
        $url = "https://api.line.me/v2/bot/message/reply";

        $messages = [];
        $messages['replyToken'] = $reply_token;
        $messages['messages'][0] = $json;

        $encodeJson = json_encode($messages);

        $this->sentMessageCurl($encodeJson,$url);

    }


    function sentMessageCurl($encodeJson,$url)
    {
        $datasReturn = [];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $encodeJson,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ". $this->access_token,
                "cache-control: no-cache",
                "content-type: application/json; charset=UTF-8",
            ),
        ));    $response = curl_exec($curl);
        $err = curl_error($curl);    curl_close($curl);    if ($err) {
        $datasReturn['result'] = 'E';
        $datasReturn['message'] = $err;
    } else {
        if($response == "{}"){
            $datasReturn['result'] = 'S';
            $datasReturn['message'] = 'Success';
        }else{
            $datasReturn['result'] = 'E';
            $datasReturn['message'] = $response;
        }
    }
        return $datasReturn;
    }

}
