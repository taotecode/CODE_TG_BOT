<?php
/**
 * CodeType
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 16:58
 * @filename CodeType.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\model;
use app\common\model\TimeModel;
class CodeType extends TimeModel
{
    /**
     * 更新时间
     * @var string
     */
    protected $updateTime = false;

    public function SystemCommand()
    {
        return $this->hasOne(SystemCommand::class, 'id','command_id');
    }
}