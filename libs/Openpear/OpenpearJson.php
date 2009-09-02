<?php
// Json で出力したいときはこのクラスを返却するとかどうか

class OpenpearJsonView extends Openpear
{
    public function output(){
        $result = $this->inVars('result');
        echo json_encode($result);
        exit;
    }
}