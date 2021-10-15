<?php


namespace app\common\model\user;


use app\common\model\BaseModel;
use app\common\model\User;

class Achievement extends BaseModel
{

    protected $name = 'user_achievement';

    protected $pk = 'id';

    protected $insert = ['wxapp_id'];

    protected $cur_year;

    protected $cur_month;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->cur_year = date('Y');
        $this->cur_month = date('m');
    }

    public function setWxappIdAttr(){
        return self::$wxapp_id ? : 10001;
    }

    public function addAchievement($user_id, $achievement){
        $check = $this->checkExist($user_id);
        if(!$check){ ##增加记录
            $res = $this->create([
                'user_id' => $user_id,
                'year' => $this->cur_year,
                'month' => $this->cur_month,
                'self_achievement' => $achievement,
                'team_achievement' => 0
            ]);
        }else{
            $res = $this->where(['id'=>$check['id']])->setInc('self_achievement',$achievement);
        }
        return $res;
    }

    public function addTeamAchievement($user_id, $supply_user_id, $achievement, $id){
        $user = User::get(['user_id' => $user_id]);
        $user_ids = $this->getSameLevelUser($user['relation'], $supply_user_id, $user['grade_id']);
        if(!$user_ids)return true;
        $link_data = [];
        foreach($user_ids as $item){
            $achievement_id = $this->addPerTeamAchievement($item, $achievement);
            $link_data[] = [
                'achievement_id' => $achievement_id,
                'achievement_detail_id' => $id
            ];
        }
        $linkModel = new AchievementLink();
        $linkModel->isUpdate(false)->saveAll($link_data);
        return true;
    }

    public function addPerTeamAchievement($user_id, $achievement){
        $check = $this->checkExist($user_id);
        if(!$check){ ##增加记录
            $res = $this->create([
                'user_id' => $user_id,
                'year' => $this->cur_year,
                'month' => $this->cur_month,
                'self_achievement' => 0,
                'team_achievement' => $achievement
            ]);
            $id = $id = $res['id'];
        }else{
            $res = $this->where(['id'=>$check['id']])->setInc('team_achievement',$achievement);
            $id = $check['id'];
        }
        return $id;
    }

    /**
     * 获取同等级的用户id数组
     * @param $relation
     * @param $supply_user_id
     * @param $grade_id
     * @return array|false|string
     */
    public function getSameLevelUser($relation, $supply_user_id, $grade_id){
        $relation = trim($relation, '-');
        if(!$relation)return [];
        $relation = explode('-', $relation);
        if(!$relation[0])return [];
        if($supply_user_id > 0){
            $filter = [];
            foreach($relation as $rel){
                if($rel == $supply_user_id){
                    break;
                }
                $filter[] = $rel;
            }
        }else{
            $filter = $relation;
        }
        if(!$filter)return [];
        $orderFilter = implode(',', $filter);
        $user_ids = User::where(['user_id'=>['IN', $filter], 'grade_id'=>$grade_id, 'status'=>1, 'is_delete'=>0])->limit(1)->orderRaw("field(user_id," . $orderFilter . ")")->column('user_id');
        return $user_ids;
    }

    public function checkExist($user_id){
        return $this->where(['user_id'=>$user_id, 'year'=>$this->cur_year, 'month'=>$this->cur_month])->find();
    }

    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

}