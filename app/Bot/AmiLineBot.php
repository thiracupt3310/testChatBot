<?php
namespace App\Bot;
use App\LineBot;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Flex\FlexSampleRestaurant;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Flex\FlexSampleShopping;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\UrlBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\ExternalLinkBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\VideoBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\CameraRollTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\CameraTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\LocationTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

/**
 * Created by PhpStorm.
 * User: sra
 * Date: 9/21/2018
 * Time: 9:51 PM
 */
class AmiLineBot extends CustomLineBot
{

    public static $bot_name = "ami";
    protected static $ID = 7;
    protected static $ACCESS_TOKEN = "ummjiCO1AINoNQ/8anm9Z19ZJBxACJF0sZ0nuvlG2xtAPSX6V848dXJLNrdLe6AI/EBt/RKjN2VY292UqGgulT46DtxH35/Oz8jzISrG5daARxXx5ap1G+YyKz4iR43Dus1sBMAhwRbRNC2sfkeO7wdB04t89/1O/w1cDnyilFU=";
    protected static $CHANNEL_TOKEN = "6a52b47869cbd23e90cdece6a2e5b473";

    public static function getInstant(){

        if (!isset(self::$ACCESS_TOKEN) ||!isset(self::$CHANNEL_TOKEN)) {
            $bot = LineBot::get_bot_by_name(self::$bot_name);
            return new self($bot->id, $bot->access_token,$bot->channel_token);
        }

        return new self(self::$ID, self::$ACCESS_TOKEN,self::$CHANNEL_TOKEN);
    }

    function action($option = [], $chat_message = null)
    {
        parent::action($option, $chat_message);

    }

    public function userReplyAction()
    {
        parent::userReplyAction();

        $message = $this->chat_message->text;

        if ($this->word_contain($message, "เมนู")){

            $quick_replies = [];

            $quick_replies[] = $this->create_quick_reply($this->create_message_template_action("test1", "เทส1"));
            $quick_replies[] = $this->create_quick_reply($this->create_camera_roll_template_action("Camera Roll"));
            $quick_replies[] = $this->create_quick_reply($this->create_camera_template_action("Camera"));
            $quick_replies[] = $this->create_quick_reply($this->create_location_template_action("Sent your location"));
//            $quick_replies[] = $this->create_quick_reply($this->create_text_message("test2"));
//            $quick_replies[] = $this->create_quick_reply($this->create_text_message("test3"));
//            $quick_replies[] = $this->create_quick_reply($this->create_text_message("test4"));
//            $quick_replies[] = $this->create_quick_reply($this->create_text_message("test5"));

//            $this->reply_text_message("test");
            $this->quick_reply_message("เลือก quick reply ได้นะฮะ", $quick_replies);
        }
        else if ($this->word_contain($message, "test")){
            $this->reply_text_message("test");
        }
    }

    public function groupReplyAction()
    {
        parent::groupReplyAction();

        $message = $this->chat_message->text;

        if (!$this->words_contain($message, ["เอมิ" , "ami", "Ami","flex"])){
            return;
        }

        if($this->words_contain($message, ["hi", "how are you"] )){
            $this->reply_text_message("สวัดดีคะ หนูชื่อเอมิ");
        }
        else if($this->word_contain_allcase($message, "บ้าน")){

            $title = "บ้านเอมิ";
            $address = "10/121 หมู่บ้านชวนชื่นโมดัสวิภาวดี ถนนวิภาวดีรังสิต แขวงสนามบิน เขตดอนเมือง กรุงเทพ 10210";
            $lat = "13.890178";
            $long = "100.590127";

            $this->reply_location_message($title,$address,$lat,$long);
        }
    }

    public function askBot(){


    }


    public function setAnswer($ask){

        $message_builder  = "";

        switch ($ask){
            case "random":
                break;
            case "":
                break;
        }


    }

    public function roomReplyAction()
    {
        parent::roomReplyAction();
    }


    public function handleFlexMessage()
    {
        $text = $this->chat_message->text;
        $replyToken = $this->chat_message->reply_token;
        $source_type =  $this->chat_message->source_type;

        switch ($text) {
            case 'flex profile':
                $userId = $this->chat_message->user_id;
                $this->sendProfile($replyToken, $userId);
                break;
            case 'flex byebyebot':
                if ($source_type == "room") {
                    $this->bot->replyText($replyToken, 'Leaving room');
                    $this->bot->leaveRoom($this->chat_message->room_id);
                    break;
                }
                if ($source_type == "group") {
                    $this->bot->replyText($replyToken, 'Leaving group');
                    $this->bot->leaveGroup($this->chat_message->group_id);
                    break;
                }
                $this->bot->replyText($replyToken, 'Bot cannot leave from 1:1 chat');
                break;
            case 'flex confirm':
                $this->bot->replyMessage(
                    $replyToken,
                    new TemplateMessageBuilder(
                        'Confirm alt text',
                        new ConfirmTemplateBuilder('Do it?', [
                            new MessageTemplateActionBuilder('Yes', 'Yes!'),
                            new MessageTemplateActionBuilder('No', 'No!'),
                        ])
                    )
                );
                break;
            case 'flex buttons':
//                $imageUrl = UrlBuilder::buildUrl($this->req, ['static', 'buttons', '1040.jpg']);
                $url =  url("image/b.jpg");
                $buttonTemplateBuilder = new ButtonTemplateBuilder(
                    'My button sample',
                    'Hello my button',
                    $url,
                    array(
                        new UriTemplateActionBuilder('Go to line.me', 'https://line.me'),
                        new PostbackTemplateActionBuilder('Buy', 'action=buy&itemid=123'),
                        new PostbackTemplateActionBuilder('Add to cart', 'action=add&itemid=123'),
                        new MessageTemplateActionBuilder('Say message', 'hello hello'),
                    )
                );
                $templateMessage = new TemplateMessageBuilder('Button alt text', $buttonTemplateBuilder);
                $this->bot->replyMessage($replyToken, $templateMessage);
                break;
            case 'flex carousel':
                $imageUrl = url("image/b.jpg");
                $carouselTemplateBuilder = new CarouselTemplateBuilder([
                    new CarouselColumnTemplateBuilder('foo', 'bar', $imageUrl, [
                        new UriTemplateActionBuilder('Go to line.me', 'https://line.me'),
                        new PostbackTemplateActionBuilder('Buy', 'action=buy&itemid=123'),
                    ]),
                    new CarouselColumnTemplateBuilder('buz', 'qux', $imageUrl, [
                        new PostbackTemplateActionBuilder('Add to cart', 'action=add&itemid=123'),
                        new MessageTemplateActionBuilder('Say message', 'hello hello'),
                    ]),
                ]);
                $templateMessage = new TemplateMessageBuilder('Button alt text', $carouselTemplateBuilder);
                $this->bot->replyMessage($replyToken, $templateMessage);
                break;
            case 'flex imagemap':
                $richMessageUrl = url("image/menu1040.png");
                $imagemapMessageBuilder = new ImagemapMessageBuilder(
                    $richMessageUrl,
                    'This is alt text',
                    new BaseSizeBuilder(1040, 1040),
                    [
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/manga/en',
                            new AreaBuilder(0, 0, 520, 520)
                        ),
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/music/en',
                            new AreaBuilder(520, 0, 520, 520)
                        ),
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/play/en',
                            new AreaBuilder(0, 520, 520, 520)
                        ),
                        new ImagemapMessageActionBuilder(
                            'URANAI!',
                            new AreaBuilder(520, 520, 520, 520)
                        )
                    ]
                );
                $this->bot->replyMessage($replyToken, $imagemapMessageBuilder);
                break;
            case 'flex imagemapVideo':

                $video_url_1 = url("video/test/videoplayback.mp4");
                $preview_url_2 = url("video/test/video_pre.png");

                $richMessageUrl = url("image/menu1040.png");
                $imagemapMessageBuilder = new ImagemapMessageBuilder(
                    $richMessageUrl,
                    'This is alt text',
                    new BaseSizeBuilder(1040, 1040),
                    [
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/manga/en',
                            new AreaBuilder(0, 0, 520, 520)
                        ),
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/music/en',
                            new AreaBuilder(520, 0, 520, 520)
                        ),
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/play/en',
                            new AreaBuilder(0, 520, 520, 520)
                        ),
                        new ImagemapMessageActionBuilder(
                            'URANAI!',
                            new AreaBuilder(520, 520, 520, 520)
                        )
                    ],
                    null,
                    new VideoBuilder(
                        $video_url_1,
                        $preview_url_2,
                        new AreaBuilder(260, 260, 520, 520),
                        new ExternalLinkBuilder('https://line.me', 'LINE')
                    )
                );
                $this->bot->replyMessage($replyToken, $imagemapMessageBuilder);
                break;
            case 'flex restaurant':
                $flexMessageBuilder = FlexSampleRestaurant::get();
                $this->bot->replyMessage($replyToken, $flexMessageBuilder);
                break;
            case 'flex shopping':
                $flexMessageBuilder = FlexSampleShopping::get();
                $this->bot->replyMessage($replyToken, $flexMessageBuilder);
                break;
            case 'flex quickReply':
                $postback = new PostbackTemplateActionBuilder('Buy', 'action=quickBuy&itemid=222', 'Buy');
                $datetimePicker = new DatetimePickerTemplateActionBuilder(
                    'Select date',
                    'storeId=12345',
                    'datetime',
                    '2017-12-25t00:00',
                    '2018-01-24t23:59',
                    '2017-12-25t00:00'
                );
                $quickReply = new QuickReplyMessageBuilder([
                    new QuickReplyButtonBuilder(new LocationTemplateActionBuilder('Location')),
                    new QuickReplyButtonBuilder(new CameraTemplateActionBuilder('Camera')),
                    new QuickReplyButtonBuilder(new CameraRollTemplateActionBuilder('Camera roll')),
                    new QuickReplyButtonBuilder($postback),
                    new QuickReplyButtonBuilder($datetimePicker),
                ]);
                $messageTemplate = new TextMessageBuilder('Text with quickReply buttons', $quickReply);
                $this->bot->replyMessage($replyToken, $messageTemplate);
                break;
            case 'flex json':
                $json = ' { "type": "flex",
                              "altText": "This is a Flex Message",
                              "contents": {
                                "type": "bubble",
                                "body": {
                                  "type": "box",
                                  "layout": "horizontal",
                                  "contents": [
                                    {
                                      "type": "text",
                                      "text": "Hello,"
                                    },
                                    {
                                      "type": "text",
                                      "text": "World!"
                                    }
                                  ]
                                }
                              }
                            }';

                $datas = json_decode($json);
                $this->reply_flex_json($datas);
                break;

            default:
                $this->echoBack($replyToken, $text);
                break;
        }
    }
    /**
     * @param string $replyToken
     * @param string $text
     * @throws \ReflectionException
     */
    private function echoBack($replyToken, $text)
    {
        $this->logger->info("Returns echo message $replyToken: $text");
        $this->bot->replyText($replyToken, $text);
    }
    /**
     * @param $replyToken
     * @param $userId
     * @throws \ReflectionException
     */
    private function sendProfile($replyToken, $userId)
    {
        if (!isset($userId)) {
            $this->bot->replyText($replyToken, "Bot can't use profile API without user ID");
            return;
        }
        $response = $this->bot->getProfile($userId);
        if (!$response->isSucceeded()) {
            $this->bot->replyText($replyToken, $response->getRawBody());
            return;
        }
        $profile = $response->getJSONDecodedBody();
        $this->bot->replyText(
            $replyToken,
            'Display name: ' . $profile['displayName'],
            'Status message: ' . $profile['statusMessage']
        );
    }

}
