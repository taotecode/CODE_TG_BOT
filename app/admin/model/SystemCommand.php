<?php
/**
 * SystemCommand
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 20:12
 * @filename SystemCommand.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\admin\model;
use app\common\model\TimeModel;
class SystemCommand extends TimeModel
{
    /**
     * 更新时间
     * @var string
     */
    protected $updateTime = false;

    public function CodeType()
    {
        return $this->hasOne(CodeType::class, 'command_id');
    }
}