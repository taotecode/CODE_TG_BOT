<?php
/**
 * CodeNum
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 16:49
 * @filename CodeNum.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\command;

use app\admin\model\CodeType;
use EasyAdmin\console\CliEcho;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

/**
 * 将下面这段ssh添加到定时任务里，每天0点执行一次
cd /你的网站根目录/
php think code_num
 */
class CodeNum extends Command
{

    protected $codeType;
    protected function configure()
    {
        $this->setName('code_num')
            ->setDescription('码库维护');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("========码库维护：========" . date('Y-m-d H:i:s'));
        $this->codeType=new CodeType();
        //清除超过一个月的数据库
        $codeType=$this->codeType->select();
        $month=1;
        foreach ($codeType as $key=>$val){
            $tableName='jd_code_'.date($val->storage_time, strtotime("-".$month." month")).'_'.$val->SystemCommand->command;
            $check = Db::query("show tables like '{$tableName}'");
            if (!$check){
                continue;
            }
            if (Db::execute("DROP TABLE {$tableName}")!=0){
                CliEcho::error('删除数据库失败：' . $tableName);
            }
            $month++;
        }
        //清除超过指定天数的数据
        foreach ($codeType as $key=>$val){
            $tableName='jd_code_'.date($val->storage_time).'_'.$val->SystemCommand->command;
            $check = Db::query("show tables like '{$tableName}'");
            if (!$check){
                continue;
            }
            $data=Db::table($tableName)->whereTime('create_time', '<=',strtotime("-".$val->code_time." day") )->delete();
            if (!$data){
                CliEcho::error('删除数据失败：' . $tableName);
            }
        }
        //清除剩余的码字的次数
        foreach ($codeType as $key=>$val){
            $tableName='jd_code_'.date($val->storage_time).'_'.$val->SystemCommand->command;
            $check = Db::query("show tables like '{$tableName}'");
            if (!$check){
                continue;
            }
            $data=Db::table($tableName)->whereNotIn('num','0')->whereNotIn('pull_num','0')->update(['num'=>0,'pull_num'=>0]);
            if (!$data){
                CliEcho::error('清除剩余的码字的次数失败：' . $tableName);
            }
        }
    }
}