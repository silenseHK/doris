<?php


namespace app\store\controller\finance;


use app\store\controller\Controller;
use app\store\model\dealer\Setting as SettingModel;
use think\Exception;

class Setting extends Controller
{

    public function index(){
        ##获取提现的设置
        $settingData = SettingModel::get(['key'=>'withdraw']);
        $withdraw_setting = $settingData['values'];
        return $this->fetch('',compact('withdraw_setting'));
    }

    public function edit(){
        try{
            ##参数
            $service_charge = input("post.service_charge",0,'floatval');
            $service_charge_status = input("post.service_charge_status",20,'intval');
            ##更新
            $res = SettingModel::where(['key'=>'withdraw'])->setField('values',json_encode(compact('service_charge','service_charge_status')));
            if($res === false)throw new Exception('操作失败');
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}