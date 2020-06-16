<?php


namespace app\api\model\college;

use app\api\validate\college\LessonValid;
use app\common\model\college\LecturerCollect as LecturerCollectModel;
use think\db\Query;

class LecturerCollect extends LecturerCollectModel
{

    protected $user_id;

    protected $lecturer_id;

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new LessonValid();
    }

    /**
     * 检查用户是否已关注讲师
     * @param $user_id
     * @param $lecturer_id
     * @return int|string
     * @throws \think\Exception
     */
    public static function checkCollect($user_id, $lecturer_id){
        return (self::where(compact('user_id','lecturer_id'))->count()) > 0 ? 1 : 0;
    }

    /**
     * 关注、取消关注讲师
     * @param $user
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function lecturerCollect($user){
        $this->user_id = $user['user_id'];
        ##验证
        if(!$this->valid->scene('lecturer_collect')->check(input()))
            return $this->setError($this->valid->getError());
        ##参数
        $lecturer_id = input('post.lecturer_id',0,'intval');
        $lecturer = Lecturer::get(['lecturer_id'=>$lecturer_id]);
        $type = input('post.type',1,'intval');
        ##检查讲师
        if(!$lecturer)
            return $this->setError('讲师不存在');
        $this->lecturer_id = $lecturer_id;
        ##操作
        switch($type){
            case 1:
                return $this->collect();
                break;
            case 2:
                return $this->cancel();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 关注讲师
     * @return bool
     * @throws \think\Exception
     */
    protected function collect(){
        $user_id = $this->user_id;
        $lecturer_id = $this->lecturer_id;
        ##查看是否关注
        if(self::checkCollect($user_id, $lecturer_id))return true;
        ##增加讲师关注数
        Lecturer::collect($lecturer_id);
        ##新增
        $res = $this->isUpdate(false)->save(compact('user_id','lecturer_id'));
        return $res === false ? $this->setError('操作失败') : true;
    }

    /**
     * 取消关注
     * @return bool
     * @throws \think\Exception
     */
    protected function cancel(){
        $user_id = $this->user_id;
        $lecturer_id = $this->lecturer_id;
        ##查看是否关注
        if(!self::checkCollect($user_id, $lecturer_id))return true;
        ##减少讲师关注数
        Lecturer::cancel($lecturer_id);
        ##删除记录
        $res = $this->where(compact('user_id','lecturer_id'))->delete();
        return $res === false ? $this->setError('操作失败') : true;
    }

    /**
     * 用户收藏导师列表
     * @param $user
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collectList($user){
        if(!$this->valid->scene('lecturer_collect_list')->check(input()))
            return $this->setError($this->valid->getError());
        ##参数
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        ##数据
        $list = $this
            ->where([
                'user_id' => $user['user_id']
            ])
            ->with(
                [
                    'lecturer' => function(Query $query){
                        $query
                            ->field(['lecturer_id', 'name', 'avatar', 'label'])
                            ->with(
                                [
                                    'image' => function(Query $query){
                                        $query->field(['file_id', 'storage', 'file_url', 'file_name']);
                                    }
                                ]
                            );
                    }
                ]
            )
            ->order('create_time','desc')
            ->page($page, $size)
            ->select();
        return compact('list');
    }

}