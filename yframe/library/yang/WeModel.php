<?php

namespace yang;
abstract class WeModel {
    public $table;
    public $prefix;
    public $a_join;
    private $setVar = array();
    private $getVar = array();
    private $fields = '*';
    private $where = '';
    private $order = '';
    private $limit = '';
    private $between = '';
    private $join = '';
    private $convert = array();
    private $table2 = '';
    private $datas = array();
    protected $g_field = array();
    public $dtdata = array();
    protected $times = '';
    protected $alise = '';
    private $sql;
    private $use_cache = false;
    private $field_data = array();

    private $beginning = false;


    /**
     * 获取执行中的错误
     * @return array 返回错误信息
     */
    public function getError(){
        if ($this->beginning){
            pdo_rollback();
        }
    }

    public function begin(){
        $this->beginning = true;
        pdo_begin();
    }

    public function commit(){
        try{
            pdo_commit();
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * 获取全部字段
     * @param $table
     * @return mixed
     */
    public function getAllField($table){
        $sql = 'DESCRIBE ' . $table;
        $query = pdo_fetchall($sql);
        return $this->convertTypes($query);
    }

    /**
     * 获取类型记录默认值
     * @param PDOStatement $statement
     * @return mixed
     */
    private function convertTypes(array $statement){
        $assoc = array();
        foreach ($statement as $val){
            if (strpos($val['Key'] ,'PRI') !== false){
                continue;
            }
            $val['Field'] = strtolower($val['Field']);
            $assoc[$val['Field']] = array();
            if ($this->serachVar($val['Type'], array('int','decimal','float','double','bit','boolean'))){
                $assoc[$val['Field']] = (empty($val['Default']) || is_null($val['Default']))?intval(null):$val['Default'];
            }else{
                $assoc[$val['Field']] = (empty($val['Default']) || is_null($val['Default']))?'':$val['Default'];
            }
        }
        unset($statement,$val);
        return $assoc;
    }

    /**
     * 单排查 如果包含数组内其中任意一个值返回ture
     * @param string $str
     * @param array $match
     * @return bool
     */
    private function serachVar($str = '', array $match = array()){
        $a = false;
        foreach ($match as $value){
            if (strpos($str,$value) !== false){
                $a = true;break;
            }
        }
        return $a;
    }

    public function __construct() {
        if (empty($this->table)) {
            $name = explode("\\",get_called_class());
            $name = strtolower($this->humpToLine(end($name)));
            if (defined("YF_DB_PRIFX")) {
                $config = YF_DB_PRIFX;
                $this->table = $config . '_' . $name;
            } else {
                $this->table = Env::get('yf_module_name') . '_' . $name;
            }
        }
        $this->table2 = $this->table;
        $this->table = tablename($this->table);
        $this->getAllFileds();
        $this->getAutoFunc();
        $this->init();
    }

    private function humpToLine($str){
        $str = lcfirst($str);
        if (preg_match('/([A-Z]{1})/',$str)){
            $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                return '_'.strtolower($matches[0]);
            },$str);
        }
        return $str;
    }

    protected function init(){

    }

    /**
     * 获取全部字段
     */
    private function getAllFileds(){
        $filed = Cache::file('db.'.$this->table2.'_filed');
        if (empty($filed) || $filed === false){
            $filed = $this->getAllField($this->table);
            Cache::file('db.'.$this->table2.'_filed',$filed);
        }
        $this->field_data = $filed;
    }

    public function cache($type = false) {
        $this->use_cache = $type;
        return $this;
    }

    private function cached_result($sql) {
        $sql = str_replace("\s\n\t\r",'',$sql);
        $result = Cache::file('db.'.$this->table2.'.'.$sql);
        if (empty($result) || $result === false){
            return false;
        }
        return $result;
    }

    private function cache_result($sql,$result) {
        $sql = str_replace("\s\n\t\r",'',$sql);
        Cache::file('db.'.$this->table2.'.'.$sql,$result);
        return $result;
    }

    private function cache_clear() {
        Cache::clear_all('db.'.$this->table2);
    }

    /**
     * insert 语句
     * @param array $data
     */
    public function insert(){
        $filed = $this->coverFied(array_keys($this->datas));
        $value = implode(',',$this->datas);
        $sql = 'INSERT INTO '.$this->table."({$filed})".' VALUES '."({$value});";
        $this->sql = $sql;
        return $this->query();
    }

    public function delete(){
        $sql = 'DELETE FROM '.$this->table.$this->where;
        $this->sql = $sql;
        return $this->query();
    }

    /**
     * 获取最后执行的sql语句
     * @return array
     */
    public function getLastsql(){
        $error = pdo_debug(false);
        $error = end($error);
        return array($this->sql,$error);
    }

    /**
     * update 语句
     * @param array $data
     * @param array $condition
     */
    public function update(){
        $value = implode(',',$this->coverUData($this->datas));
        $sql = 'UPDATE '.$this->table." SET {$value}".$this->where;
        $this->sql = $sql;
        return $this->query();
    }

    public function join($tablename,$join = '',$alise = '',$on = ''){
        if (empty($join)){
            $join = 'LEFT';
        }
        $sql = ' '.strtoupper($join)." JOIN ".tablename($tablename);
        if (!empty($alise)){
            $sql .= ' AS '.$alise;
        }
        if (!empty($on)){
            $sql .= ' ON '. $on;
        }
        $this->join = $sql . ' ';
        return $this;
    }


    public function beetween($data1 , $data2){
        if (is_string($data1)){
            $data1 = $this->parseString($data1);
        }
        if (is_string($data2)){
            $data2 = $this->parseString($data2);
        }
        $sql = " BETWEEN {$data1} AND {$data2} ";
        if (empty($this->where)){
            $this->where = " WHERE".$sql;
        }else{
            $this->beetween = $sql;
        }
        return $this;
    }
    /**
     * 字段减1
     * @param $field
     * @param int $add
     */
    public function setDes($field, $add = 1){
        $add = intval($add);
        $sql = 'UPDATE '.$this->table." SET `{$field}` = `{$field}`-{$add}".$this->where;
        $this->sql = $sql;
        return $this->query();
    }

    /**
     * sum 计算
     * @param $fild 字段名
     * @return array|bool
     */
    public function sum($fild){
        $sql = 'SELECT sum(`'.$fild.'`) FROM '.$this->table.' '.$this->alise.$this->join.$this->where;
        $this->sql = $sql;
        return pdo_fetchcolumn($this->sql,$this->convert);
    }

    /**
     * 字段设值
     * @param $field
     * @param string $add
     */
    public function setData($field, $add = ''){
        $add = $this->parseString($add);
        $sql = 'UPDATE '.$this->table." SET `{$field}` = {$add}".$this->where;
        $this->sql = $sql;
        return $this->query();
    }

    /**
     * 字段加1
     * @param $field
     * @param int $add
     */
    public function setAes($field, $add = 1){
        $add = intval($add);
        $sql = 'UPDATE '.$this->table." SET `{$field}` = `{$field}`+{$add}".$this->where;
        $this->sql = $sql;
        return $this->query();
    }

    private function coverUData($data){
        $temp = array();
        foreach ($data as $key=>$value){
            $key2 = str_replace('`','',$key);
            $value2 = str_replace(array('"',"'"), array('',''),$value);
            if ((is_numeric($value2) && $value2 == 0) || empty($value2)){
                continue;
            }
            if (!in_array($key2,$this->g_field) && !empty($value)){
                $temp[] = "`{$key}` = {$value}";
            }
        }
        return $temp;
    }

    /**
     * 字段分割
     * @param $field
     */
    public function field(){
        $field = func_get_args();
        if (is_array($field[0])) {
            $field = $field[0];
        }
        if (is_array($field) && !empty($field)){
            foreach ($field as $key=>$value){
                if (!is_numeric($key)){
                    $field[$key] = $key . "(".$this->parseFiled($value).")";
                }else{
                    if (!empty($this->alise) && strpos($value,'.') === false){
                        $value = $this->alise.'.'.$value;
                    }
                    $field[$key] = $this->parseFiled($value);
                }
            }
            $this->fields = implode(',',array_values($field));
        }
        return $this;
    }

    private function coverFied($filed){
        foreach ($filed as $key=>$value){
            if (!is_numeric($key)){
                $field[$key] = $key . "(".$this->parseFiled($value).")";
            }else{
                $field[$key] = $this->parseFiled($value);
            }
        }
        $filed = array_filter($filed);
        $filed = implode(',',array_values($filed));
        return $filed;
    }

    private function getAutoFunc(){
        foreach (get_class_methods($this) as $v) {
            if (preg_match('/get([\w]*)Var/', $v, $match)) {
                $this->getVar[] = $match[1];
            }
            if (preg_match('/set([\w]*)Var/', $v, $match)) {
                $this->setVar[] = $match[1];
            }
        }
    }

    public function convertDataMethod(&$data,&$temp1){
        if (!is_array($data)) {
            return false;
        }
        $temp1 = array();
        array_walk($data,function (&$value,$key) use (&$temp1) {
            $temp1[":" . $key] = $value;
            $value = ":" . $key;
        });
    }

    public function data(array $data){
        $data = array_merge($this->field_data,$this->dtdata,$data);
        foreach ($data as $key=>$value){
            if (strpos($value,':') === false) {
                $data[$key] = $this->parseString($value);
            }
        }
        if (!empty($this->setVar)) {
            $data = $this->convertSetVar($data);
        }
        $data = array_filter($data);
        if (!empty($this->times)){
            $data[$this->times] = time();
        }
        $this->datas = $data;
        return $this;
    }

    public function where(){
        $where = func_get_args();
        if (empty($where)){
            return $this;
        }
        if (is_array($where[0])) {
            $where = $where[0];
        } else {
            $temp = array();
            $count = count($where);
            if ($count < 2) {
                return $this;
            }
            $temp[$where[0]] = array($where[1]);
            if ($count == 3) {
                $temp[$where[0]][] = $where[2];
            } elseif ($count == 4) {
                $temp[$where[0]][] = $where[2];
                $temp[$where[0]][] = $where[3];
            }
            $where = $temp;
        }
        $this->where = $this->parseWhere($where);
        if (!empty($this->between)){
            $this->where .= $this->between;
        }
        return $this;
    }

    public function convertData(array $convert = array()){
        foreach ($convert as $key=>$value){
            $convert[$key] = addslashes(trim($value," \t\n\r\0\x0B'\""));
        }
        if (!empty($this->setVar)){
            $convert = $this->convertSetVar($convert,true);
        }
        $this->convert = $convert;
        return $this;
    }

    /**
     * order 语句
     * @param array $id
     * @return $this
     */
    public function order(){
        $id = func_get_args();
        if (empty($id)){
            return $this;
        }
        $id[1] = isset($id[1]) ? $id[1] :'asc';
        $temp = array();
        $temp[$id[1]] = $id[0];
        $sql = ' ORDER BY ';
        foreach ($temp as $k=>$v){
            if (is_array($v)){
                foreach ($v as $kk=>$vv){
                    $v[$kk] = $this->parseFiled($vv);
                }
                $v = implode(' AND ',$v);
                $sql .= $v . ' ' . $k;
                break;
            }else{
                $v = $this->parseFiled($v);
                $sql .= $v . ' ' . strtoupper($k);
                break;
            }
        }
        $this->order = $sql;
        return $this;
    }

    public function limit($id,$id2 = 0){
        if (!is_numeric($id)){
            return $this;
        }
        $sql = ' LIMIT ';
        if ($id2 != 0){
            $sql .= intval($id).','.intval($id2);
        }else{
            $sql .= intval($id);
        }
        $this->limit = $sql;
        return $this;
    }

    /**
     * 转换select语句
     * @param array $where
     * @param array $convert
     * @param array $join
     */
    public function select(){
        $sql = 'SELECT '.$this->fields.' FROM '.$this->table.' '.$this->alise.$this->join.$this->where.$this->order.$this->limit;
        $this->sql = $sql;
        return $this->fetchAll();
    }

    /**
     *
     * @param array $where
     * @param array $convert
     * @param array $join
     */
    public function find(){
        $this->limit(1);
        $sql = 'SELECT '.$this->fields.' FROM '.$this->table.' '.$this->alise.$this->join.$this->where.$this->order.$this->limit;
        $this->sql = $sql;
        return $this->fetch();
    }

    /**
     *
     * @param array $where
     * @param array $convert
     * @param array $join
     */
    public function count(){
        $this->field(array('count'=>'*'));
        $sql = 'SELECT '.$this->fields.' FROM '.$this->table.$this->join.$this->where;
        $this->sql = $sql;
        if ($this->use_cache) {
            $result = $this->cached_result($this->sql);
            if (!empty($result) || $result !== false) {
                return $result;
            }
        }
        return $this->fetchcolumn();
    }

    private function parseWhere(array $condition){
        $sql = ' WHERE ';
        $keys = array_keys($condition);
        foreach ($condition as $key=>$value){
            $end = ($key == end($keys));
            $key = $this->parseFiled($key);
            $value = is_array($value)?$value[0]:$value;
            $or = (is_array($value) && isset($value[1]))?$value[1]:'AND';
            if ($end){
                $or = '';
            }
            $se = (is_array($value) && isset($value[2]))?$value[2]:'=';
            $function = (is_array($value) && isset($value[3]))?$value[3]:'';
            if ($function != '' || !empty($function)){
                if (is_array($value)){
                    foreach ($value as $k=>$v){
                        $value[$k] = $this->parseString($v);
                    }
                    $value = implode(',',$value);
                }else{
                    $value = $this->parseString($value);
                }
                $function = $function . "({$value})";
                $sql .= "{$key} {$se} {$function} {$or} ";
                continue;
            }else{
                $value = $this->parseString($value);
                $sql .= "{$key} {$se} {$value} {$or} ";
                continue;
            }
        }
        return $sql;
    }

    private function parseFiled($key){
        $key = addslashes($key);
        if ($key == '*'){
            return $key;
        }
        if (strpos($key,'.') !== false){
            $key = explode('.',$key);
            $end = "`".$key[1]."`";
            if (isset($key[2])){
                $alise = array_pop($key);
                $key = array_splice($key,0,2);
            }
            $key[1] = $end;
            if (isset($alise)){
                return implode('.',$key) . ' AS '.$alise;
            }
            return implode('.',$key);
        }else{
            return "`{$key}`";
        }
    }

    private function parseString($string){
        if (is_array($string)) {
            $string = implode('',$string);
        }
        if (strpos($string,':') === 0){
            return $string;
        }
        $string = addslashes($string);
        return "\"{$string}\"";
    }

    public function query($sql = '',$data = array()){
        if (!empty($sql)){
            $this->sql = $sql;
        }
        if (!empty($data)){
            $this->convertData($data);
        }
        $data = pdo_query($this->sql,$this->convert);
        if ($this->use_cache) {
            if (!empty($data)) {
                $this->cache_clear();
            }
        }
        $this->clear();
        return $data;
    }

    public function fetchAll($data = array()){

        if ($this->use_cache) {
            $result = $this->cached_result($this->sql);
            if (!empty($result) || $result !== false) {
                return $result;
            }
        }

        if (!empty($data)){
            $this->convertData($data);
        }
        $data = pdo_fetchall($this->sql,$this->convert);

        if (!empty($this->getVar)) {
            foreach ($data as $key => $value2) {
                foreach ($this->getVar as $value) {
                    $vals = $this->humpToLine(lcfirst($value));
                    if (isset($value2[strtolower($vals)])) {
                        $data[$key][strtolower($vals)] = call_user_func(array($this, 'get' . $value . 'Var'), $value2[strtolower($vals)]);
                    } else {
                        $null = '';
                        $data[$key][strtolower($vals)] = call_user_func(array($this, 'get' . $value . 'Var'), $null, $value2);
                    }
                }
            }
        }
        if ($this->use_cache) {
            $this->cache_result($this->sql,$data);
        }
        $this->clear();
        return $data;
    }
    public function fetch($data = array()){
        if ($this->use_cache) {
            $result = $this->cached_result($this->sql);
            if (!empty($result) || $result !== false) {
                return $result;
            }
        }
        if (!empty($data)){
            $this->convertData($data);
        }
        $data = pdo_fetch($this->sql,$this->convert);
        if (!empty($this->getVar)) {
            foreach ($this->getVar as $value) {
                $vals = $this->humpToLine(lcfirst($value));
                if (isset($data[strtolower($vals)])) {
                    $data[strtolower($vals)] = call_user_func(array($this, 'get' . $value . 'Var'), $data[strtolower($vals)]);
                } else {
                    $null = '';
                    $data[strtolower($vals)] = call_user_func(array($this, 'get' . $value . 'Var'), $null, $data);
                }
            }
        }
        if ($this->use_cache) {
            $this->cache_result($this->sql,$data);
        }
        $this->clear();
        return $data;
    }

    public function fetchcolumn($data = array()){
        if (!empty($data)){
            $this->convertData($data);
        }
        $data = pdo_fetchcolumn($this->sql,$this->convert);
        $this->clear();
        return $data;
    }

    private function convertSetVar(array $data = array(),$convert = false){
        if ($convert === false) {
            foreach ($this->setVar as $value) {
                $vals = $this->humpToLine(lcfirst($value));
                if (isset($data[strtolower($vals)])) {
                    $val = $data[strtolower($vals)];
                    $data[strtolower($vals)] = call_user_func(array($this, 'set' . $value . 'Var'), $val);
                }
            }
        }else {
            foreach ($this->setVar as $value) {
                $vals = $this->humpToLine(lcfirst($value));
                if (isset($data[':'.strtolower($vals)])) {
                    $val = $data[strtolower($vals)];
                    $data[strtolower($vals)] = call_user_func(array($this, 'set' . $value . 'Var'), $val);
                }
            }
        }
        return $data;
    }

    private function clear(){
        $this->where = '';
        $this->limit = '';
        $this->order = '';
        $this->datas = '';
        $this->fields = '*';
        $this->sql = str_replace(array_keys($this->convert),array_values($this->convert),$this->sql);
        $this->convert = array();
    }
}