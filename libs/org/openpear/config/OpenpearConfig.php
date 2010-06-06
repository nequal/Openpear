<?php
class OpenpearConfig
{
    static public function __callStatic($name, array $args) {
        if (empty($args)) {
            return module_const($name);
        } else {
            $arg = array_shift($args);
            return module_const($name, $arg);
        }
    }
}