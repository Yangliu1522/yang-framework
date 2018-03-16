<?php
/**
 * Created by PhpStorm.
 * User: superyang
 * Date: 18-2-3
 * Time: 下午2:43
 */

namespace yang\model;


trait Query
{
    use querys\Helper;


    /**
     * where 语句
     * @param string|array $field
     * @param string $condition
     * @param string $code
     * @param string $type
     * @return $this
     */
    public function where($field = '', $condition = '', $code = 'eq', $type = '\\1 \\3 \\2') {
        if (empty($field)) {
            return $this;
        }
        $sql = [];
        if (!is_array($field)) {
            $sql[$field] = [$condition, $code, $type];
        } else {
            $sql = $field;
        }

        $this->parseWhere($sql);
        return $this;
    }

    /**
     * where 转换
     * @param array $data
     * @return null
     */
    private function parseWhere(array $data = []) {
        if (empty($data)) {
            return null;
        }
        $where = '';
        if (isset($data['or'])) {
            $end = array_keys($data['or']);
            $end = end($end);
            $cont = 'OR ';
            $where = $this->parseWhereForeach($data['or'], $end, $cont);
            unset($data['or'], $copy);
        }

        $cont = 'AND ';
        if (!empty($data)) {
            $end = array_keys($data);
            $end = end($end);
            $where2 = $this->parseWhereForeach($data, $end, $cont);
            if (!empty($where)) {
                $where = '(' . $where . ' ) AND ' . $where2;
            } else {
                $where = $where2;
            }
        }

        $this->sqlcom['where'] = ' WHERE ' . $where;
    }

    /**
     * 详细的where转换
     * @param $condition
     * @param $end
     * @param string $cont
     * @return string
     */
    private function parseWhereForeach($condition, $end, $cont = '') {
        $where = '';
        foreach ($condition as $field2 => $condition2) {
            if ($end == $field2) {
                $cont = '';
            }
            $field2 = $this->parseField($field2);
            if (!is_array($condition2)) {
                $condition2 = [
                    $condition2, 'eq', '\\1 \\3 \\2'
                ];
            } else {
                if (!isset($condition2[1])) $condition2[1] = 'eq';
                if (!isset($condition2[2])) $condition2[2] = '\\1 \\3 \\2';
            }

            $condition2[0] = $this->parseQuot($condition2[0]);
            $condition2[1] = $this->parseConditioncode($condition2[1]);

            $where .= str_replace(['\\1', '\\2', '\\3'], [$field2, $condition2[0], $condition2[1]], strtoupper($condition2[2])) . ' ' . $cont;
        }

        return $where;
    }

    /**
     * Limit 語句
     * @param $start
     * @param int $end
     * @return $this
     */
    public function limit($start, $end = 0) {
        $limit = ' LIMIT '.intval($start);
        if (!empty($end)) {
            $limit .= ' , ' . intval($end);
        }
        $this->sqlcom['limit'] = $limit;
        return $this;
    }

    /**
     * order 语句
     * @param $data
     * @param string $type
     * @return $this
     */
    public function order($data, $type = 'ASC') {
        $order = ' ORDER BY ';
        $data = $this->parseFieldStr($data);

        $order .= implode(',', $data) . ' ' . strtoupper($type);

        $this->sqlcom['order'] = $order;
        return $this;
    }

    /**
     * 转换field
     * @param $data
     * @return $this
     */
    public function field($data) {

        $data = $this->parseFieldStr($data);
        $this->sqlcom['field'] = implode(',', $data);
        return $this;
    }

    /**
     * 生成插入数据，暂时没想到怎么做多条插入
     * @param array $data
     * @return $this
     */
    public function data(array $data = []) {
        if (empty($data)) {
            return $this;
        }
        $sql = '';
        if (isset($data[0])) {
            return $this;
        }

        $sql = [];
        if ($this->auto_insert) {
            $data = array_merge($data, $this->auto_insert_data);
        }

        $this->autotime($data);
        $this->auto_type($data, true);

        $this->run_auto($data, 'set');
        foreach ($data as $field => $value) {
            $sql[] = $this->parseField($field) . ' = ' . $this->parseQuot($value);
        }
        $this->sqlcom['data'] = implode(',', $sql);
        $this->sqlcom['insert_type'] = ' SET ';
        return $this;
    }

    /**
     * 生成插入数据，暂时没想到怎么做多条插入
     * @param array $data
     * @return $this
     */
    public function convert(array $data = []) {
        if (empty($data)) {
            return $this;
        }

        foreach ($data as $field => &$value) {
            $value =  $this->parseQuot($value);
        }
        $this->sqlcom['convert'] = $data;
        return $this;
    }

    public function cache($flag = '') {
        $this->sqlcom['cache_flag'] = $flag;
        $this->is_cache = true;
        return $this;
    }
}