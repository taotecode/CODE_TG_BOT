<?php
/**
 * Telegram
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 18:10
 * @filename Telegram.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\controller;

use app\admin\model\AuthTgGroup;
use app\admin\model\SystemCommand;
use app\admin\model\TgUser;
use app\common\controller\ApiController;
use think\App;
use think\facade\Env;
use think\facade\Log;

class Telegram extends ApiController
{
    protected $authTgGroup;
    protected $systemCommand;
    protected $tgUser;

    public function __construct(
        App $app,
        AuthTgGroup $authTgGroup,
        SystemCommand $systemCommand,
        TgUser $tgUser
    ){
        parent::__construct($app);
        $this->authTgGroup = $authTgGroup;
        $this->systemCommand = $systemCommand;
        $this->tgUser = $tgUser;
    }

    public function receiveMessages(){
        $input = file_get_contents('php://input');
        $input = json_decode($input, true);
        Log::info(json_encode($input));

        //获取发送类型
        $chatType=$input['message']['chat']['type']??'';
        //获取发送者ID
        $chatId=$input['message']['chat']['id'];
        //获取消息ID
        $messageId=$input['message']['message_id'];
        //用户信息
        $messageUser=$input['message']['from'];
        //消息内容
        $messageData=$input['message']['text']??'';

        //验证消息通道是否授权
        if ($chatType === 'private'){
            //检查用户是否在授权群组
            $groupAuthList=$this->authTgGroup->select();
            $isGroup=false;
            foreach ($groupAuthList as $item){
                if ($this->checkGroup($messageUser['id'],$item->chat_id)){
                    $isGroup=true;
                    $input['groupId']=$item->chat_id;
                    break;
                }
            }
            if ($isGroup===false){
                return $this->sendMessages($chatId,'您未加入任何所授权的群组！',$messageId);
            }
        }elseif ($chatType === 'group'||$chatType==='supergroup'){
            //检查用户是否在授权群组
            if (!$this->authTgGroup->where('chat_id',$chatId)->find()){
                return $this->sendMessages($chatId,'您所在的群组未被授权！'.PHP_EOL.'请进入以授权的群组内',$messageId);
            }
        }else{
            $this->sendMessages($chatId,'您发送的消息通道有误。',$messageId);
        }

        if (!empty($input['new_chat_participant'])||!empty($input['new_chat_member'])){
            //判断用户是否加入
            if (!$this->tgUser->where('tg_id',$messageUser['id'])->find()){
                $this->tgUser->save([
                    'tg_id'=>$messageUser['id'],
                    'last_name'=>$messageUser['last_name'],
                    'first_name'=>$messageUser['first_name'],
                    'username'=>$messageUser['username'],
                    'language_code'=>$messageUser['language_code'],
                ]);
            }
            $this->sendMessages($chatId,'您发送的消息通道有误。',$messageId);
        }

        //判断用户是否加入
        if (!$this->tgUser->where('tg_id',$messageUser['id'])->find()){
            $this->tgUser->save([
                'tg_id'=>$messageUser['id'],
                'last_name'=>$messageUser['last_name'],
                'first_name'=>$messageUser['first_name'],
                'username'=>$messageUser['username'],
                'language_code'=>$messageUser['language_code'],
            ]);
        }

        //判断命令
        if (strpos($messageData, '/') !== false){
            $command=substr($messageData, 1);
            $command=explode(" ",$command);
            $commandData=$this->systemCommand->where('command','LIKE','%'.$command[0].'%')->find();
            if (empty($commandData)){
                return $this->sendMessages($chatId,'不是正确的命令，请输入 /help 查看命令',$messageId);
            }
            $call=invoke([$commandData->call_controller,$commandData->call_action],[$command[1]??'',$input,$commandData]);
            if (is_array($call)){
                return $this->sendMessagesMarkDown($chatId,$call['text'],$messageId);
            }
            return $this->sendMessages($chatId,$call,$messageId);
        }

        return $this->sendMessages($chatId,$messageData,$messageId);
    }


    /**
     * 发送信息
     * @param $chat_id
     * @param $text
     * @param null $message_id
     * @return bool|string
     */
    private function sendMessages($chat_id,$text,$message_id=null){
        $url = 'https://api.telegram.org/bot' . $this->getToken() . '/sendmessage';
        $data = [
            'chat_id' => $chat_id,
            'text'=>$text,
            'reply_to_message_id'=>$message_id,
        ];
        return curl_post($url, $data);
    }

    /**
     * 发送信息
     * @param $chat_id
     * @param $text
     * @param null $message_id
     * @return bool|string
     */
    private function sendMessagesHtml($chat_id,$text,$message_id=null){
        $url = 'https://api.telegram.org/bot' . $this->getToken() . '/sendmessage';
        $data = [
            'chat_id' => $chat_id,
            'text'=>htmlspecialchars_decode($text),
            'reply_to_message_id'=>$message_id,
            'parse_mode'=>'HTML',
        ];
        return curl_post($url, $data);
    }

    /**
     * 发送信息
     * @param $chat_id
     * @param $text
     * @param null $message_id
     * @return bool|string
     */
    private function sendMessagesMarkDown($chat_id,$text,$message_id=null){
        $url = 'https://api.telegram.org/bot' . $this->getToken() . '/sendmessage';
        $data = [
            'chat_id' => $chat_id,
            'text'=>htmlspecialchars_decode($text),
            'reply_to_message_id'=>$message_id,
            'parse_mode'=>'markdown',
        ];
        return curl_post($url, $data);
    }

    /**
     * 检查是否在指定群组内
     * @param $user_id
     * @param $chat_id
     * @return bool
     */
    private function checkGroup($user_id,$chat_id){
        $url = 'https://api.telegram.org/bot' . $this->getToken() . '/getChatMember';
        $data = [
            'chat_id'=>$chat_id,
            'user_id'=>$user_id,
        ];
        $result = curl_post($url, $data);
        $result=json_decode($result,true);
        if ($result['error_code']??0===400){
            return false;
        }else{
            return true;
        }
    }
}