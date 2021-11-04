<?php
/**
 * CodeTableService
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 17:17
 * @filename CodeTableService.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\service;

use think\facade\Db;
use think\facade\Config;
use think\facade\Log;

class CodeTableService
{
    /**
     * 当前实例
     * @var object
     */
    public static $instance;

    /**
     * 表前缀
     * @var string
     */
    public $tablePrefix;

    /**
     * 表后缀
     * @var string
     */
    public $tableSuffix;

    /**
     * 表名
     * @var string
     */
    public $tableName;

    public $tableNameType;

    /**
     * 构造方法
     * SystemLogService constructor.
     */
    public function __construct()
    {
        $this->tablePrefix = Config::get('database.connections.mysql.prefix');
        $this->tableSuffix = date('Ym', time());
        if (empty($this->tableNameType)){
            $this->tableName = "{$this->tablePrefix}code_{$this->tableSuffix}";
        }else{
            $this->tableName = "{$this->tablePrefix}code_{$this->tableSuffix}_{$this->tableNameType}";
        }
        return $this;
    }

    /**
     * 获取实例对象
     * @return CodeTableService|object
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 查询数据
     * @param $where
     * @param $type
     * @return array|string|\think\Model|null
     */
    public function query($where,$type){
        Log::info($this->tableName);
        Db::startTrans();
        try {
            $this->detectTable();
            $data=Db::table($this->tableName)->where($where)->find();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
        return $data;
    }

    /**
     * 保存数据
     * @param $data
     * @param $type
     * @return bool|string
     */
    public function save($data,$type)
    {
        Log::info($this->tableName);
        Db::startTrans();
        try {
            $this->detectTable();
            Db::table($this->tableName)->insert($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
        return true;
    }

    /**
     * 保存数据
     * @param $data
     * @param $type
     * @return bool|string
     */
    public function saveALl($data,$type)
    {
        Log::info($this->tableName);
        Db::startTrans();
        try {
            $this->detectTable();
            Db::table($this->tableName)->insertAll($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
        return true;
    }

    /**
     * 检测数据表
     * @return bool
     */
    public function detectTable()
    {
        $check = Db::query("show tables like '{$this->tableName}'");
        if (empty($check)) {
            $sql = $this->getCreateSql();
            Db::execute($sql);
        }
        return true;
    }

    /**
     * 根据后缀获取创建表的sql
     * @return string
     */
    public function getCreateSql()
    {
        return <<<EOT
CREATE TABLE `{$this->tableName}` (
    `id` BIGINT NOT NULL AUTO_INCREMENT , 
    `chat_id` VARCHAR(200) NOT NULL COMMENT '群组ID' , 
    `tg_id` VARCHAR(200) NOT NULL COMMENT '用户ID' , 
    `code` VARCHAR(500) NULL DEFAULT NULL COMMENT '助力码' , 
    `num` bigint unsigned DEFAULT '0' COMMENT '次数',
    `pull_num` bigint unsigned DEFAULT '0' COMMENT '助力次数',
    `status` TINYINT(10) NOT NULL DEFAULT '0' COMMENT '状态' ,
    `create_time` INT NULL DEFAULT NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci COMMENT = '助力码表 - {$this->tableSuffix}';
EOT;

/*        return <<<EOT
CREATE TABLE `{$this->tableName}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID',
  `url` varchar(1500) NOT NULL DEFAULT '' COMMENT '操作页面',
  `method` varchar(50) NOT NULL COMMENT '请求方法',
  `title` varchar(100) DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) DEFAULT '' COMMENT 'User-Agent',
  `create_time` int(10) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=630 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='后台操作日志表 - {$this->tableSuffix}';
EOT;*/
    }
}