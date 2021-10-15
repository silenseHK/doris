<?php


namespace app\api\controller;


class Callback
{

    public function printCallBack(){
        $data = file_get_contents('php://input');
        print_r($data);
        file_put_contents('test.txt', $data,FILE_APPEND);
    }

}