<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller\system;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use app\admin\model\SystemConfig;
use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * Class Config
 * @package app\admin\controller\system
 * @ControllerAnnotation(title="系统配置管理")
 */
class Config extends AdminController
{

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new SystemConfig();
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="保存")
     */
    public function save()
    {
        $this->checkPostRequest();
        $post = $this->request->post();
        try {
            foreach ($post as $key => $val) {
                $this->model
                    ->where('name', $key)
                    ->update([
                        'value' => $val,
                    ]);
            }
            TriggerService::updateMenu();
            TriggerService::updateSysconfig();
        } catch (\Exception $e) {
            $this->error('保存失败');
        }
        $this->success('保存成功');
    }

    /**
     * @NodeAnotation(title="设置Webhook")
     */
    public function setWebhook(){
        if (empty($this->getTgToken())){
            $this->error('请先保存机器人用户名和TOKEN配置');
        }
        $callback=$this->request->domain().'/api/receiveMessages.json';
        try {
            $telegram = new Telegram($this->getTgToken(), $this->getTgUserName());
            $result = $telegram->setWebhook($callback);
            if ($result->isOk()) {
                $this->success($result->getDescription());
            }
        } catch (TelegramException $e) {
            $this->error($e->getMessage());
        }
    }

}