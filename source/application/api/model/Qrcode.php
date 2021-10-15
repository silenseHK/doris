<?php


namespace app\api\model;

use app\common\model\Qrcode as QrcodeModel;

class Qrcode extends QrcodeModel
{

    /**
     * 体验装入群及营养师二维码
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getExperienceQrCode(){
        $group_qrcode = $this->getGroupQrCode();
        $dr_qrcode = $this->getDrQrCode();
        return compact('group_qrcode','dr_qrcode');
    }

    /**
     * 获取群二维码
     * @return array|bool|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGroupQrCode(){
        return $this->getQrCode(10);
    }

    /**
     * 获取营养师二维码
     * @return array|bool|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDrQrCode(){
        return $this->getQrCode(20);
    }

    /**
     * 获取二维码
     * @param $type
     * @return array|bool|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getQrCode($type){
        return $this->where(['status'=>1, 'qrcode_type'=>$type])->with(['image'])->find();
    }

}