<?php
/**
 * TgUser
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 16:17
 * @filename TgUser.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\controller\code;
use app\admin\model\TgUser as TgUserModel;
use app\admin\traits\Curd;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Env;
/**
 * Class TgUser
 * @package app\admin\controller\code
 * @ControllerAnnotation(title="tg用户")
 */
class TgUser extends AdminController
{
    use Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new TgUserModel();
    }

    /**
     * @NodeAnotation(title="发送TG消息")
     */
    public function send_msg($id){
        $row = $this->model->find($id);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            $result=$this->sendMessages($row->tg_id,$post['text']);
            if ($result['error_code']??0===400){
                $this->error('发送失败');
            }
            $this->success('发送成功');
        }
        $this->assign('row', $row);
        return $this->fetch();
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
            'parse_mode'=>'markdown',
        ];
        return curl_post($url, $data);
    }
}