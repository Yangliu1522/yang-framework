<?php
/**
 * Created by PhpStorm.
 * User: superyang
 * Date: 18-2-3
 * Time: 下午2:32
 */

namespace yang;


class Model
{
    protected $tablename = '';
    protected $alise = '';
    private $wherecode = 1, $ordercode = 2, $limitcode = 3, $datacode = 4, $filedcode = 0;
    private $selectcode = 5, $insertcode = 6, $updatecode = 7, $delectcode = 8;
    private $sqldata = [
        0 => " FILEDDATA ",
        1 => " WHERE ",
        2 => " ORDER BY ",
        3 => " LIMIT ",
        4 => " DATA ",
        5 => ' SELECT FILEDDATA FROM TABLENAME ',
        6 => ' INSERT INTO TABLENAME FILEDDATA ',
        7 => ' UPDATE TABLENAME SET DATA ',
        8 => ' DELECT FROM TABLENAME '
    ], $sqlcomplate = [];

    use model\Query;
}