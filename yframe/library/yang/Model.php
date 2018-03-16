<?php
/**
 * Created by PhpStorm.
 * User: superyang
 * Date: 18-2-3
 * Time: 下午2:32
 */

namespace yang;
use yang\model\Result;

class Model
{
    /**
     * @var model\connection\PdoConnection|model\connection\We7Connection
     */
    protected $db;
    protected $tablename = '';
    protected $alise = '';
    private $sqlcom = [
        'where' => '',
        'limit' => '',
        'order' => '',
        'field' => '*',
        'basetable' => '',
        'table' => '',
        'insert_type' => ' VALUES ',
        'cache_flag' => '',
        'convert' => [],
    ], $runnerType = 0;
    private $getFunc = [], $setFunc = [], $last_sql = [];
     protected $dateFormat = 'Y-m-d H:i:s', $type = [],$auto_insert_data = [];
    private $auto_time = false,
        $is_cache = false, $auto_insert = false, $is_insert = false,$is_update = false;
    private $cacheFiled = [];
    private $filterword = [
        'function' => '/(load_file|floor|hex|substring|if|ord|char|benchmark|reverse|strcmp|datadir|updatexml|extractvalue|name_const|multipoint|database|user|
)(\(.*?\))/is',
        'word' => '/\s+(select|delect|insert|update|@|intooutfile|intodumpfile|unionselect|uniondistinct|information_schema|current_user|current_date?)\s+/i'
    ];

    use model\Query;

    public function __construct($tablename = '') {
        if (!empty($tablename)) $this->sqlcom['table'] = $this->humpToLine($tablename);

        if (empty($this->sqlcom['table'])) $this->sqlcom['table'] = $this->method_table(get_called_class());

        $this->db = $this->db_method();
        $this->sqlcom['basetable'] = $this->sqlcom['table'];
        $this->sqlcom['table'] = $this->db->tablename($this->sqlcom['table']);
        // 初始化所有字段，获取所有获取器修改器
        $this->all_field();
        $this->auto_func();
        $this->init();
    }

    protected function init(){

    }

    public function debug() {
        Common::dump($this->sqlcom);
    }

    /**
     * @param string $field
     * @param string $condition
     * @param string $code
     * @param string $type
     * @return \yang\model\Result
     */
    public function select($field = '', $condition = '', $code = 'eq', $type = '\\1 \\3 \\2') {
        if (!empty($field)) {
            if (is_numeric($field)) {
                $this->limit($field);
            } elseif (empty($condition)) {
                $this->field($field);
            } else {
                $this->where($field, $condition, $code, $type);
            }
        }

        $this->sqlcom['sql'] = 'SELECT '. $this->sqlcom['field'] .' FROM '. $this->sqlcom['table'].$this->sqlcom['where'] . $this->sqlcom['order'] . $this->sqlcom['limit'];
        $data = $this->db->fetchall($this->sqlcom['sql'], $this->sqlcom['convert']);
        foreach ($data as $key => &$value) {
            $this->run_auto($value);
        }
        $this->cached($data);
        $this->clear();
        if (empty($data)) {
            return $data;
        }
        return Result::create($data);
    }

    /**
     * @param string $field
     * @param string $condition
     * @param string $code
     * @param string $type
     * @return bool|int|mixed
     */
    public function count($field = '', $condition = '', $code = 'eq', $type = '\\1 \\3 \\2') {
        if (!empty($field)) {
            if (empty($condition)) {
                $this->field($field);
            } else {
                $this->where($field, $condition, $code, $type);
            }
        }

        $this->sqlcom['sql'] = 'SELECT COUNT('. $this->sqlcom['field'] .') FROM '. $this->sqlcom['table'].$this->sqlcom['where'];
        $data = $this->db->fetchcolumn($this->sqlcom['sql'], $this->sqlcom['convert']);
        $this->clear();
        return $data;
    }

    /**
     * @param string $field
     * @param string $condition
     * @param string $code
     * @param string $type
     * @return \yang\model\Result
     */
    public function find($field = '', $condition = '', $code = 'eq', $type = '\\1 \\3 \\2') {
        if (!empty($field)) {
            if (empty($condition)) {
                $this->field($field);
            } else {
                $this->where($field, $condition, $code, $type);
            }
        }
        $this->limit(1);
        $this->sqlcom['sql'] = 'SELECT '. $this->sqlcom['field'] .' FROM '. $this->sqlcom['table'].$this->sqlcom['where'].$this->sqlcom['limit'];

        $data = $this->db->fetch($this->sqlcom['sql'], $this->sqlcom['convert']);
        $this->run_auto($data);
        $this->cached($data);
        $this->clear();
        if (empty($data)) {
            return $data;
        }
        return Result::create($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data = []) {
        if (!empty($data)) {
            $this->data($data);
        }

        $this->sqlcom['sql'] = 'UPDATE ' . $this->sqlcom['table']. ' SET '.$this->sqlcom['data'].$this->sqlcom['where'];
        $data = $this->db->query($this->sqlcom['sql'], $this->sqlcom['convert']);
        $this->delcache();
        $this->clear();
        return !empty($data);
    }

    /**
     * @param array $data
     * @return bool|\yang\model\Result
     */
    public function insert(array $data = []) {
        $this->is_insert = true;
        if (!empty($data)) {
            $this->data($data);
        }

        $this->sqlcom['sql'] = 'INSERT INTO ' . $this->sqlcom['table'] . $this->sqlcom['insert_type'] .$this->sqlcom['data'];
        $data = $this->db->query($this->sqlcom['sql'], $this->sqlcom['convert']);
        $this->delcache();
        $this->clear();
        if (!empty($data)) {
            $temp = [
                'user_id' => $this->db->lastid()
            ];
            return Result::create($temp);
        }
        return !empty($data);
    }

    /**
     * @param string $field
     * @param string $condition
     * @param string $code
     * @param string $type
     * @return bool
     */
    public function delect($field = '', $condition = '', $code = 'eq', $type = '\\1 \\3 \\2') {
        if (!empty($field)) {
            $this->where($field, $condition, $code, $type);
        }

        $this->sqlcom['sql'] = 'DELECT FROM ' . $this->sqlcom['table'] .$this->sqlcom['where'];
        $data = $this->db->query($this->sqlcom['sql'], $this->sqlcom['convert']);
        $this->delcache();
        $this->clear();
        return !empty($data);
    }

    private function filtersql($sql) {
        if (preg_match($this->filterword['function'], $sql)) {
            $sql = preg_replace_callback($this->filterword['function'], function () {
                return '';
            }, $sql);
        }

        if (preg_match($this->filterword['word'], $sql)) {
            $sql = preg_replace_callback($this->filterword['word'], function () {
                return '';
            }, $sql);
        }

        return $sql;
    }
}