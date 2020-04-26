<?php


namespace app\api\model;

use app\common\model\NoticeMessage as NoticeMessageModel;

class NoticeMessage extends NoticeMessageModel
{

    public function getType(){
        return $this->type;
    }

    /**
     * 获取系统消息
     * @param $num
     * @param int $page
     * @param int $size
     * @return array|false|\PDOStatement|string|\think\Collection|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSystemMsg($num, $page=1){
        return $num > 1 ? $this->getSomeSystemMsg($num, $page) : $this->getOneSystemMsg();
    }

    /**
     * 获取一条系统通知
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOneSystemMsg(){
        return $this->where(['type'=>10, 'effect_time'=>['ELT', time()]])->field(['title', 'content'])->find();
    }

    /**
     * 获取多条系统消息
     * @param $num
     * @param int $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSomeSystemMsg($num, $page=1){
        return $this->where(['type'=>10, 'effect_time'=>['ELT', time()]])->field(['id', 'title', 'content', 'url', 'params', 'effect_time', 'detail'])->page($page, $num)->select();
    }

}