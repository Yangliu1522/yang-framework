<?php

namespace yang;
/*
 *  验证获取的信息
 *  格式有 如下msg 按 | 分隔
 *
 *  '名字' => '检查项1|检查项2|检查项3'
 *  '名字' => ['检查项1|检查项2|检查项3', '转换的名字']
 */
class Validate
{
    public $rule = array();
    public $message = array();
    public $validate = array();
    private $default_message = array(
        'intval' => ':name 不是整数',
        'digit' => ':name 不是数字',
        'phone'  => ':name 不是有效的电话号码',
        'email'  => ':name 不是有效的邮箱地址',
        'require'=> ':name 不存在',
        'array'  => ':name 不是有效的数组',
        'chUN' => ':name 必须是英文数字组合',
        'chCU'   => ':name 必须是中文或英文组合',
        'max'    => ':name 长度不得超过:attr位',
        'min'    => ':name 长度不得少于:attr位',
        'reval'  => ':name 与:attr输入不相同',
        'between'    => ':name 必须在:attr之间',
        'sbetween'    => ':name 长度必须在:attr 位之间',
    );
    private $data = '';
    private $error_messages = '';
    private $bmessages = '';
    private $aname = '';
    private $arule = '';
    private $aattr = '';
    private $conver = '';
    private $tdata = array();
    private $brmessages = array();
    public $b = false;

    public function __construct(array $validate = array(), array $rule = array(), array $message = array())
    {
        if (!empty($validate)) {
            $this->vlidate = $validate;
        }

        if (!empty($rule)) {
            $this->rule = $rule;
        }

        if (!empty($message)) {
            $this->message = $message;
        }

        $this->default_message = array_merge($this->default_message, $this->message);
    }

    public function check($data)
    {
        $this->data = $data;
        foreach ($this->validate as $k => $v) {
            if (is_array($v)) {
                $this->conver = $v[1];
                $v = $v[0];
            }
            if (strpos($v, '|') === false) {
                $rule = array($v);
            } else {
                $rule = explode('|', $v);
            }
            if (!$this->On($rule, $k)) {
                if (!$this->b) {
                    return false;
                }
            }
            $this->conver = '';
        }

        if (!empty($this->brmessages)) {
            return false;
        }
        return true;
    }

    public function getCheckedData()
    {
        return $this->tdata;
    }

    private function On($rule, $k)
    {
        $data = '';
        $rules = array();
        $value = isset($this->data[$k]) ? $this->data[$k] : '';
        $this->tdata[$k] = $value;
        if (is_array($rule)) {
            foreach ($rule as $v) {
                if (strpos($v, ':')) {
                    list($v, $data) = explode(":", trim($v), 2);
                    if ($v == 'default') {
                        if ($value == '') {
                            $this->tdata[$k] = $data;
                        }
                        continue;
                    }

                    if ($v == 'message') {
                        $this->bmessages = $data;
                        continue;
                    }

                    if ($v == 'conver') {
                        $this->conver = $data;
                        continue;
                    }
                }
                $rules[$v] = $data;
            }
        }
        if (empty($rules)) {
            return true;
        }
        return $this->onCh($rules, $k, $value);
    }

    private function onCh($rule, $name, $value)
    {
        $data = '';
        if (is_array($rule)) {
            foreach ($rule as $v => $data) {
                if (!$this->checkOn(trim($v), $data, $value)) {
                    $this->error_messages = !empty($this->bmessages) ? $this->bmessages : $v;
                    $this->arule = $v;
                    $this->aname = !empty($this->conver)?$this->conver :$name;
                    $this->aattr = $data;
                    if ($this->b) {
                        $this->brmessages[$name][] = array('rule' => $v, 'message' => $this->messages());
                        continue;
                    } else {
                        return false;
                    }
                }
            }
            $this->bmessages = '';
            $this->conver = '';
        } else {
            if (strpos($rule, ':')) {
                list($rule, $data) = explode(':', trim($rule));
            }
            if (!$this->checkOn($rule, $data, $value)) {
                $this->error_messages = $rule;
                $this->arule = $rule;
                $this->aname = $name;
                $this->aattr = $data;
                if ($this->b) {
                    $this->brmessages[$name][] = array('rule' => $rule ,'message' => $this->messages());
                    return true;
                }
                return false;
            }
        }
        return true;
    }

    public function error($output = false)
    {
        if ($this->b) {
            if ($output) {
                $data = var_export($this->brmessages, true);
                echo '<pre>' . $data . '</pre>';
            } else {
                return $this->brmessages;
            }
        } else {
            if ($output) {
                echo '<pre>' . $this->messages() . '</pre>';
            } else {
                return $this->messages();
            }
        }
    }

    private function checkOn($rule = '', $v = '', $value = '')
    {
        $result = true;
        switch (trim($rule)) {
            case 'intval':
                $result = is_int($value);
                break;
            case 'digit':
                $result = ctype_digit($value);
                break;
            case 'require':
                $result = !empty($value) || '0' == $value;
                break;
            case 'array':
                $result = is_array($value);
                break;
            case 'email':
                $result = $this->filter($value, FILTER_VALIDATE_EMAIL);
                break;
            case "ip":
                $result = $this->filter($value, FILTER_VALIDATE_IP);
                break;
            case 'url':
                $result = $this->filter($value, FILTER_VALIDATE_URL);
                break;
            case 'chUN':
                $result = $this->regex($value, '^[0-9A-Za-z]+$');
                break;
            case 'chCU':
                $result = $this->regex($value, '^[\u4e00-\u9fa5_a-zA-Z]+$');
                break;
            case 'reval':
                $v = $this->c_attr($v, 1);
                $c = isset($this->data[$v])?$this->data[$v]:'';
                $result = strcmp($c, $value) === 0;
                break;
            case 'max':
                $v = intval($this->c_attr($v, 1));
                $result = mb_strlen($value) <= $v;
                break;
            case 'min':
                $v = intval($this->c_attr($v, 1));
                $result = mb_strlen($value) >= $v;
                break;

            case 'between':
                $v = $this->c_attr($v, 2);
                $result = $this->between($value, $v);
                break;

            case 'sbetween':
                $v = $this->c_attr($v, 2);
                $result = $this->sbetween($value, $v);
                break;
            default:
                if (in_array($rule, $this->rule)) {
                    $result = $this->regex($value, $this->rule['rule']);
                } else {
                    $result = $this->regex($value, $rule);
                }
                break;
        }
        return $result;
    }

    public function between($value, $num1, $num2 = 0)
    {
        if (is_array($num1)) {
            $num2 = array_pop($num1);
            $num1 = !empty($num1) ? array_pop($num1) : 0;
        }

        return ($num1 <= $value && $value <= $num2);
    }

    public function sbetween($value, $num1, $num2 = 0)
    {
        if (is_array($num1)) {
            $num2 = array_pop($num1);
            $num1 = !empty($num1) ? array_pop($num1) : 0;
        }

        $value = mb_strlen($value);
        return ($num1 <= $value && $value <= $num2);
    }

    private function c_attr($data, $count = 1)
    {
        if (strpos($data, ',')) {
            $data = explode(',', $data);
        }

        if (is_array($data)) {
            return array_splice($data, 0, $count);
        }

        if ($count <= 1) {
            if (is_array($data)) {
                return array_shift($data);
            }
        }
        return $data;
    }

    public function filter($value, $rule)
    {
        if (is_string($rule) && strpos($rule, ',')) {
            list($rule, $param) = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = isset($rule[1]) ? $rule[1] : null;
            $rule  = $rule[0];
        } else {
            $param = null;
        }
        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

    public function regex($value, $rule)
    {
        if (isset($this->rule[$rule])) {
            $rule = $this->rule[$rule];
        }
        if (0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }
        return 1 === preg_match($rule, (string) $value);
    }


    public function messages()
    {
        $message = isset($this->default_message[$this->error_messages]) ? $this->default_message[$this->error_messages] : $this->error_messages;
        return str_replace([
            ':rule',':name',':attr'
        ], [
            $this->arule,$this->aname,$this->aattr
        ], $message);
    }
}
