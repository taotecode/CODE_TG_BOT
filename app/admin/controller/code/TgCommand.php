<?php
/**
 * TgCommand
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 10:33
 * @filename TgCommand.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\controller\code;

use app\admin\model\SystemCommand;
use app\admin\traits\Curd;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * Class TgCommand
 * @package app\admin\controller\code
 * @ControllerAnnotation(title="机器人命令管理")
 */
class TgCommand extends AdminController
{
    use Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new SystemCommand();
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->where($where)
                ->count();
            $list = $this->model
                ->where($where)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        $commandList = $this->model->select();
        $commandAdd=null;
        foreach ($commandList as $item){
            $commandAdd .=$item->command.'-'.$item->description.'<br>';
        }
        $this->assign('commandAdd',trim($commandAdd,'<br>'));
        return $this->fetch();
    }
}