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
    use query\Helper;

    protected function where(array $data = []) {
        if (empty($data)) {
            return $this;
        }
    }
}