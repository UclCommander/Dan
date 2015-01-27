<?php

class Test extends Test2 {}
class Test2 {
    /**
     * @return string
     */
    public function getName()
    {
        return strtolower(get_called_class());
    }
}


$test = new Test();

echo $test->getName();