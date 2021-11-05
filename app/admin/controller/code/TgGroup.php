<?php
/**
 * TgGroup
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 15:38
 * @filename TgGroup.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\controller\code;
use app\admin\model\AuthTgGroup;
use app\admin\traits\Curd;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Env;

/**
 * Class TgGroup
 * @package app\admin\controller\code
 * @ControllerAnnotation(title="授权群组")
 */
class TgGroup extends AdminController
{
    use Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new AuthTgGroup();
    }

    /**
     * @NodeAnotation(title="发送TG消息")
     */
    public function send_msg($id){
        $row = $this->model->whereIn('id', $id)->select();
        $row->isEmpty() && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            foreach ($row as $item){
                $result=$this->sendMessages($item->tg_id,$post['text']);
                if ($result['error_code']??0===400){
                    $this->error('发送失败,ID:'.$item->tg_id);
                }
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