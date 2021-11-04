<?php
/**
 * Code
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 14:46
 * @filename Code.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\job;

use think\App;
use think\facade\Config;
use think\facade\Db;
use think\queue\Job;

class Code
{
    /**
     * 调用
     * @param Job $job 当前的任务对象
     * @param array|mixed|string $data 发布任务时自定义的数据
     * @throws \think\db\exception\DbException
     */
    public function fire(Job $job, $data): void
    {

        $isJobDone = $this->run_job($data);
        if ($isJobDone) {
            $job->delete();
            print("<info>已经完成并删除" . "</info>\n");
        } else if ($job->attempts() > 3) {
            print("<warn>Hello Job has been retried more than 3 times!" . "</warn>\n");
            $job->delete();
        }
    }

    /**
     * 根据消息中的数据进行实际的业务处理...
     * @param $data array|string
     * @return bool
     * @throws \think\db\exception\DbException
     */
    private function run_job($data)
    {
        print("<info>新的工作开始</info> \n");
        if (!empty($data['userInfo']??'')){
            //增加pull_num
            Db::table(Config::get('database.connections.mysql.prefix').$data['tableName'])->where('id', $data['userInfo']['id'])->inc('pull_num')->update();
        }
        if (!empty($data['list'])){
            //增加被助力的num
            foreach ($data['list'] as $item){
                Db::table(Config::get('database.connections.mysql.prefix').$data['tableName'])->where('id', $item['id'])->inc('num')->update();
            }
        }
        return true;
    }


    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
        print("Warning: Job failed after max retries. job data is :" . var_export($data, true) . "\n");
    }
}