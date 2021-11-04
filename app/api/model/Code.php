<?php
/**
 * Code
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 20:56
 * @filename Code.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\model;

use app\common\model\TimeModel;

class Code extends TimeModel
{
    /**
     * 更新时间
     * @var string
     */
    protected $updateTime = false;

    public function setTableName($name)
    {
        $this->name = $name;
        return $this;
    }
}