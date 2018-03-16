<?php
/**
 * Created by PhpStorm.
 * User: xmy
 * Date: 18-3-12
 * Time: 下午6:11
 */

namespace yang\model;


class Result implements \ArrayAccess, \Iterator
{
    private $data = [];
    protected $more = false;
    protected $all = 0;
    protected  $currIndex = 0;
    public static function create($data) {
        return new static($data);
    }

    public function __construct($data)
    {
        if(!is_array($data)){
            //do some error thing
            trigger_error('Error params' , E_USER_WARNING);
        }
        $this->data = $data;
        $this->currIndex = 0;
        $this->all = count($this->data);
        $this->more = ($this->all > 0);

    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnSet($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function next()
    {
        // TODO: Implement next() method.
        next($this->data);
        $this->currIndex++;
        $this->more = ($this->currIndex < $this->all);
    }

    function valid(){
        return $this->more;
    }

    public function current()
    {
        // TODO: Implement current() method.
        return current($this->data);
    }

    public function key()
    {
        // TODO: Implement key() method.

        return key(current($this->data));
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    public function toArray() {
        return $this->data;
    }
}