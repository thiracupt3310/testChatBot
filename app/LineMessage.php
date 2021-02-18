<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineMessage extends Model
{
    public $user_name;
    public $message;
    public $sticker;
    public $package;
    public $response;
    public $response_message_list;
    public $request_message_list;



    public function response_message($response){
        return $this->response_message_list[$response];
    }

    public function request_message($request) {
        return $this->request_message_list[$request];
    }




    /**
     * @return array
     */
    public function getResponseMessageList(): array
    {
        return $this->response_message_list;
    }

    /**
     * @param array $response_message_list
     */
    public function setResponseMessageList(array $response_message_list): void
    {
        $this->response_message_list = $response_message_list;
    }

    /**
     * @return array
     */
    public function getRequestMessageList(): array
    {
        return $this->request_message_list;
    }

    /**
     * @param array $request_message_list
     */
    public function setRequestMessageList(array $request_message_list): void
    {
        $this->request_message_list = $request_message_list;
    }


}
