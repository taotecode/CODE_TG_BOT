<?php
/**
 * TgChat
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 14:56
 * @filename TgChat.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\controller\code;

use app\admin\model\TgChat as TgChatModel;
use app\admin\traits\Curd;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use think\App;
use think\facade\Env;
/**
 * Class TgChat
 * @package app\admin\controller\code
 * @ControllerAnnotation(title="tg聊天列表")
 */
class TgChat extends AdminController
{
    use Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new TgChatModel();
    }
}