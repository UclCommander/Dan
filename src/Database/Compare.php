<?php

namespace Dan\Database;

class Compare
{
    protected $methods = [
        '='     => 'isEqual',
        '!='    => 'isNotEqual',
        '>'     => 'isGreaterThan',
        '>='    => 'isGreaterThanOrEqual',
        '<'     => 'isLessThan',
        '<='    => 'isLessThanOrEqual',
    ];

    /**
     * @param $a
     * @param $is
     * @param $b
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public static function is($a, $is, $b)
    {
        $compare = new static();

        return $compare->compare($a, $is, $b);
    }

    /**
     * @param $a
     * @param $is
     * @param $b
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function compare($a, $is, $b)
    {
        if (!array_key_exists($is, $this->methods)) {
            throw new \Exception("Invalid comparison operator '{$is}'");
        }

        $method = $this->methods[$is];

        return $this->$method($a, $b);
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function isEqual($a, $b)
    {
        return $a == $b;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function isNotEqual($a, $b)
    {
        return $a != $b;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function isGreaterThan($a, $b)
    {
        return $a > $b;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function isGreaterThanOrEqual($a, $b)
    {
        return $a >= $b;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function isLessThan($a, $b)
    {
        return $a < $b;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function isLessThanOrEqual($a, $b)
    {
        return $a <= $b;
    }
}
