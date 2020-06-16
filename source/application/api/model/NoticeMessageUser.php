<?php


namespace app\api\model;

use app\common\model\NoticeMessageUser as NoticeMessageUserModel;
use app\api\model\NoticeMessage;
use app\api\validate\user\MessageValidate;
use think\Exception;

class NoticeMessageUser extends NoticeMessageUserModel
{

    protected $hidden = ['id', 'user_id', 'delete_time'];

    public function index($user){
        $noticeMessage = new NoticeMessage();
        $list = $noticeMessage->getType();
        foreach($list as $type => &$val){
            $val['message'] = $type == 10 ? $noticeMessage->getSystemMsg(1) : $this->getMsg($user['user_id'],1, $type);
        }
        $list = $this->filterIndexData($list);
        return compact('list');
    }

    /**
     * 获取信息
     * @param $user_id
     * @param $num
     * @param $type
     * @param $page
     * @return array|false|\PDOStatement|string|\think\Collection|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMsg($user_id, $num, $type, $page=1){
        return $num > 1 ? $this->getSomeMsg($user_id, $type, $num, $page) : $this->getOneMsg($user_id, $type);
    }

    /**
     * 获取一条信息
     * @param $user_id
     * @param $type
     * @return array|bool|false|\PDOStatement|string|\think\Model|null
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException\
     */
    public function getOneMsg($user_id, $type){
        $data = $this->alias('nmu')
            ->join('notice_message nm','nm.id = nmu.message_id','LEFT')
            ->where(
                [
                    'nmu.user_id' => $user_id,
                    'nm.type' => $type,
                    'nm.effect_time' => ['ELT', time()]
                ]
            )
            ->field(['nm.title', 'nm.content', 'nm.effect_time'])
            ->order('nm.effect_time','desc')
            ->find();
        if($data){
            $data['effect_time'] = date('Y-m-d H:i:s', $data['effect_time']);
            $data['wait_browse_num'] = $this->countDisBrowseNum($user_id, $type);
        }

        return $data;
    }

    /**
     * 获取多条信息
     * @param $user_id
     * @param $type
     * @param $num
     * @param int $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSomeMsg($user_id, $type, $num, $page=1){
        $list = $this->alias('nmu')
            ->join('notice_message nm','nm.id = nmu.message_id','LEFT')
            ->where(
                [
                    'nmu.user_id' => $user_id,
                    'nm.type' => $type,
                    'nm.effect_time' => ['ELT', time()]
                ]
            )
            ->field(['nm.id', 'nm.title', 'nm.content', 'nm.url', 'nm.params', 'nm.effect_time', 'nm.detail'])
            ->order('nm.effect_time','desc')
            ->page($page, $num)
            ->select();
        if(!$list->isEmpty())foreach($list as &$v)$v['effect_time'] = date('Y-m-d H:i:s',$v['effect_time']);
        return $list;
    }

    /**
     * 过滤掉没有信息的分类
     * @param $list
     * @return mixed
     */
    public function filterIndexData($list){
        foreach($list as $key => $val){
            if(empty($val['message']))unset($list[$key]);
        }
        return $list;
    }

    /**
     * 获取信息列表
     * @param $user
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function lists($user){
        ##验证
        $messageValidate = new MessageValidate();
        if(!$messageValidate->scene('lists')->check(input()))throw new Exception($messageValidate->getError());
        ##参数
        $type = input('get.type',0,'intval');
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        ##数据
        if($type == 10){
            $model = new NoticeMessage();
            $list = $model->getSystemMsg($size, $page);
        }else{
            $list = $this->getMsg($user['user_id'], $size, $type, $page);
            ##将用户信息置为已读
            $this->browse($user['user_id'], $type);
        }
        return compact('list');
    }

    /**
     * 计算未读信息
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public static function countDisReadMessage($user_id){
        return self::where(['user_id'=>$user_id,'browse_time'=>0])->count();
    }

    /**
     * 计算用户未读消息数 【根据分类】
     * @param $user_id
     * @param $type
     * @return int|string
     * @throws Exception
     */
    public function countDisBrowseNum($user_id, $type){
        return $this->alias('nmu')
            ->join('notice_message nm','nm.id = nmu.message_id','LEFT')
            ->where(
                [
                    'nmu.user_id' => $user_id,
                    'nm.type' => $type,
                    'nm.effect_time' => ['ELT', time()],
                    'nmu.browse_time' => 0
                ]
            )
            ->count();
    }

    /**
     * 阅读消息
     * @param $user_id
     * @param $type
     */
    public function browse($user_id, $type){
        $this->alias('nmu')
            ->join('notice_message nm','nm.id = nmu.message_id','LEFT')
            ->where(
                [
                    'nmu.user_id' => $user_id,
                    'nm.type' => $type,
                    'nm.effect_time' => ['ELT', time()],
                    'nmu.browse_time' => 0
                ]
            )
            ->setField('browse_time', time());
    }

}