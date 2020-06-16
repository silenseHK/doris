<?php


namespace app\api\model\user;

use app\api\model\Questionnaire;
use app\api\model\User;
use app\api\validate\user\questionnaireValidate;
use app\common\enum\user\grade\GradeSize;
use app\common\model\FoodGroup;
use app\common\model\user\Fill as FillModel;
use app\store\model\store\DieticianTeam;
use think\Db;
use think\db\Query;
use think\Exception;
use think\Session;
use app\api\controller\Questionnaire as QuestionnaireController;
use app\store\model\store\User as StoreUser;

class Fill extends FillModel
{

    public function getIndexData(){
        $params = [
            'questionnaire_id' => input('questionnaire_id',0,'intval'),
            'keywords' => input('keywords','','str_filter')
        ];
        $this->setWhere($params);
        $list = $this->alias('q')
            ->join('user u','q.user_id = u.user_id','LEFT')
            ->group('q.user_id')
            ->paginate(15,false,['query'=>\request()->request()]);
        return array_merge(compact('list'),$params);
    }

    public function getFillList(){
        $params = [
            'questionnaire_id' => input('questionnaire_id',0,'intval'),
            'user_id' => input('user_id',0,'intval'),
            'start_time' => input('start_time','','str_filter'),
            'end_time' => input('end_time','','str_filter'),
        ];
        $this->setFillListWhere($params);
        $list = $this->alias('q')
            ->join('user u','q.user_id = u.user_id','LEFT')
            ->paginate(15,false,['query'=>\request()->request()]);
        return array_merge(compact('list'),$params);
    }

    /**
     * 用户填写列表条件筛选
     * @param $params
     */
    public function setFillListWhere($params){
        $where = [
            'questionnaire_id' => $params['questionnaire_id'],
            'q.user_id' => $params['user_id'],
        ];
        if($params['start_time'] && $params['end_time']){
            $where['q.create_time'] = ['BETWEEN', [strtotime($params['start_time']), strtotime($params['end_time'])]];
        }
        $this->where($where);
    }

    public function setWhere($params){
        $where['q.questionnaire_id'] = $params['questionnaire_id'];
        if($params['questionnaire_id'] <= 0)throw new Exception('参数缺失');
        if($params['keywords']){
            $where['u.mobile'] = ['LIKE', "%{$params['keywords']}%"];
        }
        $this->where($where);
        ##判断是否超级管理员
        $user = Session::get('yoshop_store.user');
        $is_sup = Db::name('store_user')->where(['store_user_id'=>$user['store_user_id']])->value('is_super');
        if(!$is_sup){
            $team_leader_ids = DieticianTeam::getTeamLeaderIds($user['store_user_id']);
            if(!$team_leader_ids)$team_leader_ids = ['-1'];
            $this->where(['q.group_user_id'=>['IN',$team_leader_ids]]);
        }
    }

    public function userFillDetail(){
        $fill_id = input('fill_id',0,'intval');
        ##答卷详情
        $data = self::get(['fill_id'=>$fill_id], ['userAnswer.question.option']);
        return compact('data');
    }

    public static function countPointBMI($fill_id){
        $data = self::get(['fill_id'=>$fill_id], ['userAnswer.question.option']);
        $point = 0;
        $weight = $height = 0;
        foreach($data['user_answer'] as $item){
            if(in_array($item['question']['type']['value'], [20, 30])){
                $option = $item['question']['option'];
                $option = $option->toArray();
                $new_option = array_column($option, null, 'mark');
                foreach($item['answer_mark'] as $it){
                    $point += $new_option[$it]['point'];
                }
            }
            if($item['question']['name'] == 'weight'){
                $weight = floatval($item['answer']);
            }
            if($item['question']['name'] == 'height'){
                $height = floatval($item['answer']);
            }
        }
        $bmi = 0;
        if($weight && $height){
            $bmi = $weight / ($height * $height /10000);
            $bmi = round($bmi,4);
        }
        return compact('point','bmi');
    }

    /**
     * 问卷列表
     * @param $user_id
     * @return array
     * @throws Exception
     */
    public function getAnswerList($user_id){
        ##验证
        $validate = new questionnaireValidate();
        if(!$validate->scene('answer_list')->check(input()))throw new Exception($validate->getError());

        ##参数
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        $mobile = input('get.mobile','','str_filter');

        ##获取当前在线的问卷id
        $questionnaire_no = (new QuestionnaireController)->healthQuestionnaire(1);
        $questionnaire_id = (int)(Questionnaire::where(['questionnaire_no'=>$questionnaire_no])->value('questionnaire_id'));
        if(!$questionnaire_id)throw new Exception('问卷不存在');
        $where = [
            'questionnaire_id' => $questionnaire_id,
            'u.invitation_user_id' => $user_id
        ];
        if($mobile){
            $where['u.mobile'] = $mobile;
        }
        $list = $this->alias('f')
            ->join('user u','f.user_id = u.user_id','LEFT')
            ->join('user_grade ug','ug.grade_id = u.grade_id','LEFT')
            ->where($where)
            ->order('f.create_time','desc')
            ->page($page, $size)
            ->field(['u.user_id', 'u.nickName', 'u.avatarUrl', 'ug.name', 'u.mobile', 'f.fill_id', 'f.point', 'f.bmi', 'f.bmi as bmi_img', 'f.create_time'])
            ->select();

        $url = request()->domain() . "/web_view/questionnaire/detail.html";
        return compact('list','url');
    }

    public function getBmiImgAttr($value){
        return FoodGroup::getUserImg($value);
    }

    /**
     * 获取问卷详情
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getAnswerDetail(){
        ##验证
        $validate = new questionnaireValidate();
        if(!$validate->scene('answer_detail')->check(input()))throw new Exception($validate->getError());

        ##参数
        $fill_id = input('get.fill_id',0,'intval');
        $data = self::get(['fill_id'=>$fill_id], ['userAnswer.question.option']);
        if(!$data)throw new Exception('问卷数据不存在');
        foreach($data['user_answer'] as $key => &$item){
            if(in_array($item['question']['type']['value'], [20, 30])){
                foreach($item['question']['option'] as &$val){
                    if(in_array($val['mark'], $item['answer_mark'])){
                        $val['checked'] = 1;
                        if($val['is_input'])$val['label'] = $item['answer'];
                    }else{
                        $val['checked'] = 0;
                    }
                }
            }
        }
        $data['bmi_img'] = FoodGroup::getUserImg($data['bmi']);
        return compact('data');
    }

    /**
     * 用户填写的问卷列表
     * @param $user
     * @param $no
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserFillList($user, $no){
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        $questionnaire = Questionnaire::get(['questionnaire_no'=>$no]);
        $list = $this->where(['user_id'=>$user['user_id'], 'questionnaire_id'=>$questionnaire['questionnaire_id']])->field(['fill_id', 'create_time', 'point', 'bmi' ,'bmi as bmi_img'])->page($page, $size)->order('create_time','desc')->select();
        return $list;
    }

}