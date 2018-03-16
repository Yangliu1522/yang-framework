<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 17-11-28
 * Time: 下午10:05
 */

namespace yang\model;


abstract class Connection {
    protected $lastsql;

    abstract public function query($statement, array $data = array());

    abstract public function fetch($statement, array $data = array());

    abstract public function fetchall($statement, array $data = array());

    abstract public function fetchcolumn($statement, array $data = array());

    abstract public function lastid();

    abstract public function tablename($table);

    /**
     * 获取执行中的错误
     * @return array 返回错误信息
     */
    abstract public function rollback();

    abstract public function getLastsql();

    abstract public function begin();

    abstract public function commit();

    public function getAllField($table)
    {
        $sql = 'DESCRIBE ' . $table;
        $query = $this->fetchall($sql, array());
        return $this->convertTypes($query);
    }

    /**
     * 获取类型记录默认值
     * @param PDOStatement $statement
     * @return mixed
     */
    private function convertTypes(array $statement)
    {
        $assoc = array();
        foreach ($statement as $key => $val) {
            if (strpos($val['Key'], 'PRI') !== false) {
                continue;
            }
            $val['Field'] = strtolower($val['Field']);
            $assoc[$val['Field']] = array();
            if ($this->serachVar($val['Type'], array('int','decimal','float','double','bit','boolean'))) {
                $assoc[$val['Field']] = (empty($val['Default']) || is_null($val['Default']))?intval(null):$val['Default'];
            } else {
                $assoc[$val['Field']] = (empty($val['Default']) || is_null($val['Default']))?'':$val['Default'];
            }
        }
        return $assoc;
    }

    /**
     * 排查 如果包含数组内其中任意一个值返回ture
     * @param string $str
     * @param array $match
     * @return bool
     */
    private function serachVar($str = '', array $match = array())
    {
        $a = false;
        foreach ($match as $value) {
            if (strpos($str, $value) !== false) {
                $a = true;
                break;
            }
        }
        return $a;
    }

    public function convertSql($st, array $data, $error = array()) {
        $this->lastsql['sql'] = $st;
        $this->lastsql['params'] = $data;
        $this->lastsql['error'] = $error;
        if (\yang\Common::$app_debug === true) {
            \Log::recore('lastsql', $this->lastsql, 'debug');
        }
    }

    public function closeSql($sql, $data) {
        if (!empty($data)) $sql = str_replace(array_keys($data), array_values($data), $sql);
        if (stripos($sql, 'SELECT') === 0 ||
            stripos($sql, 'INSERT') === 0 ||
            stripos($sql, 'DELECT') === 0 ||
            stripos($sql, 'UPDATE') === 0) {

            if (preg_match("/(drop\s+database)/is", $sql)) {
                return true;
            }
        }
    }
}