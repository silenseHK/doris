<?php


namespace app\agent\logic;


class BaseLogic
{

    protected $error = '';

    public function getError(){
        return $this->error;
    }

    /**
     * 设置错误信息并返回false
     * @param $err
     * @return bool
     */
    public function rtnErr($err){
        $this->error = $err;
        return false;
    }

    /**
     * 生成token
     * @param $agent_id
     * @return string
     */
    protected function token($agent_id){
        return md5($agent_id . time() . rand(1000,9999));
    }

}