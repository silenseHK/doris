<?php


namespace app\store\model\store;
use app\common\enum\user\grade\GradeSize;
use app\common\model\store\DieticianTeam as DieticianTeamModel;
use app\store\model\User as UserModel;
use app\store\model\user\Grade;
use think\Db;
use think\Exception;


class DieticianTeam extends DieticianTeamModel
{

    /**
     * 营养师管理团队信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function teamInfo(){
        $store_user_id = input('store_user_id',0,'intval');
        $check_ids = $this->where(['store_user_id'=>$store_user_id])->column('leader_user_id');
        ##团队列表
        $grade_id = Grade::getGradeId(GradeSize::PARTNER);
        $team_list = UserModel::where(['grade_id'=>$grade_id, 'is_delete'=>0])->field(['user_id', 'nickName', 'avatarUrl', 'mobile'])->select();
//        print_r($check_ids);die;
        return compact('check_ids','team_list','store_user_id');
    }

    /**
     * 编辑营养师管理团队
     * @return bool
     */
    public function editTeam(){
        ##参数
        $store_user_id = input('post.store_user_id',0,'intval');
        $team_user_ids = input('post.team_user_ids/a',[]);
        ##操作
        $data = [];
        foreach($team_user_ids as $k => $v){
            $data[] = [
                'store_user_id' => $store_user_id,
                'leader_user_id' => $v
            ];
        }
        Db::startTrans();
        try{
            ##删除以前的绑定
            $this->where(['store_user_id'=>$store_user_id])->delete();
            ##增加新的绑定
            if($data){
                $res = $this->isUpdate(false)->saveAll($data);
                if($res === false)throw new Exception('操作失败.');
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 计算营养师管理的团队数
     * @param $store_user_id
     * @return int|string
     * @throws Exception
     */
    public static function countTeamNum($store_user_id){
        return self::where(['store_user_id'=>$store_user_id])->count();
    }

    /**
     * 获取营养管理的团队
     * @param $store_user_id
     * @return array
     */
    public static function getTeamLeaderIds($store_user_id){
        return self::where(['store_user_id'=>$store_user_id])->column('leader_user_id');
    }

}