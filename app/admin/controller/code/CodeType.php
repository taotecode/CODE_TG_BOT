<?php
/**
 * CodeType
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 10:02
 * @filename CodeType.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\controller\code;

use app\admin\model\CodeType as CodeTypeModel;
use app\admin\traits\Curd;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
/**
 * Class CodeType
 * @package app\admin\controller\code
 * @ControllerAnnotation(title="码库管理")
 */
class CodeType extends AdminController
{
    use Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new CodeTypeModel();
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
                ->with('SystemCommand')
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }
}