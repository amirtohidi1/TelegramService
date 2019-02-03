<?php

namespace App\Service;

class telegramapi
{
    private $API_CODE = '###:###';
    private $API_CODE_ADMIn = '###:###';
    private $CHANNEL_ID = 'CHANNEL_ID_WITH_@';
    private $CHANNEL_ADMIN_ID = 'CHANNEL_ID_WITH_@';

    public function __construct()
    {

    }


    public function send_log($text, $chat_id , $replyMarkup = NULL)
    {
        $chat_id = $this->CHANNEL_ADMIN_ID;
        $datas = array(
            "chat_id" => $chat_id,
            "text" => $text
        );

        if (!is_null($replyMarkup)) {
            $datas["reply_markup"] = json_encode($replyMarkup);
        }
        $this->sendCurl('sendMessage', $datas, FALSE);
    }

    public function sendMessage($text, $chat_id , $replyMarkup = NULL, $ADMIN = FALSE)
    {
        $chat_id = $this->CHANNEL_ID;
        $datas = array(
            "chat_id" => $chat_id,
            "text" => $text
        );
        if (!is_null($replyMarkup)) {
            $datas["reply_markup"] = json_encode($replyMarkup);
        }
        $this->sendCurl('sendMessage', $datas, $ADMIN);
    }

    /**
     * @param $text
     * @param $chat_id
     *
     * درخواست دریافت موقعیت و شماره موبایل از طریق وب سرویس
     */
    public function sendRequestLocationOrCellphone($text, $chat_id)
    {
        $datas = array(
            "chat_id" => $chat_id,
            "text" => $text
        );

        $keyboard =
            ["keyboard"=>
                [
                    [
                        ["text"=>"MAIN MENU"],
                        ["text" => "NUMBER ","request_contact"=>true , ]
                    ]
                ]
                ,"resize_keyboard"=>true
            ];

        $datas["reply_markup"] = json_encode($keyboard);

        $x = $this->sendCurl('sendMessage', $datas, FALSE);
//        $this->sendMessage($x, $chat_id);
    }


    public function sendCallBack($id, $text, $ADMIN = FALSE)
    {
        $datas['callback_query_id'] = $id;
        $datas['text'] = $text;
        $this->sendCurl('answerCallbackQuery', $datas, $ADMIN);
    }


    public function sendCurl($method, $datas, $ADMIN = FALSE)
    {
        $code = ($ADMIN == FALSE) ? $this->API_CODE : $this->API_CODE_ADMIn;
        $url = "https://api.telegram.org/bot" . $code . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($datas));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        } else {
            return ($res);
        }
    }

    public function ReciveMessage($request)
    {
        $x = $request->all();
        $recive = $this->logRecive($x);
        return $recive;
    }

    // Log Service Need Database And Model
    public function logRecive($d)
    {
        $telegram_recive = new Telegram_recive();

        if (isset($d['message'])) {
            $telegram_recive->username = $d['message']['from']['username'];
            $telegram_recive->chat_id = $d['message']['from']['id'];
            $telegram_recive->message_id = $d['message']['message_id'];
            $telegram_recive->msg = isset($d['message']['text'])?$d['message']['text']:'';
            $telegram_recive->type = 'msg';
        }else if(isset($d['callback_query']))
        {
            $telegram_recive->username = $d['callback_query']['from']['username'];
            $telegram_recive->chat_id = $d['callback_query']['from']['id'];
            $telegram_recive->message_id = $d['callback_query']['message']['message_id'];
            $telegram_recive->msg = $d['callback_query']['data'];
            $telegram_recive->type = 'callback';
        }

        if(is_array($d))
        {
            $telegram_recive->body = \GuzzleHttp\json_encode($d);

        }else
        {

        }

        $telegram_recive->save();

        return $telegram_recive;
    }

    public function setWebhook()
    {
        echo "WEBHOOK";
        $data['url'] = 'URLSET';
        echo $this->sendCurl('setWebhook', $data);
    }

}