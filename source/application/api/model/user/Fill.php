<?php


namespace app\api\model\user;

use app\api\model\Question;
use app\api\model\Questionnaire;
use app\api\model\User;
use app\api\validate\user\questionnaireValidate;
use app\common\enum\user\grade\GradeSize;
use app\common\model\FoodGroup;
use app\common\model\FoodGroupImage;
use app\common\model\user\Fill as FillModel;
use app\store\model\QuestionnaireQuestion;
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
            ->with(['groupUser'])
            ->order('q.create_time','desc')
            ->field(['u.nickName', 'u.user_id', 'u.avatarUrl', 'q.group_user_id', 'q.fill_id', 'q.create_time'])
            ->paginate(15,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getUserFillList([PAGE]);']);
        foreach($list as $key => $val){
            $list[$key]['count'] = $this->where(['questionnaire_id'=>$params['questionnaire_id'], 'user_id'=>$val['user_id']])->count();
        }
        $total = $list->total();
        $page = $list->render();
        $list = $list->toArray()['data'];
        return compact('list','page','total');
    }

    public function getFillList(){
        $params = [
            'questionnaire_id' => input('questionnaire_id',0,'intval'),
            'user_id' => input('user_id',0,'intval'),
            'start_time' => input('start_time','','str_filter'),
            'end_time' => input('end_time','','str_filter'),
        ];
        $this->setFillListWhere($params);
        $list = $this
            ->with(
                [
                    'inviteUser' => function(Query $query){
                        $query->field(['user_id', 'nickName']);
                    },
                    'foodGroup' => function(Query $query){
                        $query->field(['id', 'version'])->with(['images'=>function(Query $query){
                            $query->field(['storage', 'file_name', 'file_id', 'file_url']);
                        }]);
                    },
                ]
            )
            ->order('create_time','desc')
            ->field(['fill_id', 'bmi', 'point', 'advice', 'food_group_id', 'invite_user_id', 'create_time'])
            ->select();
        $total = count($list);
        if($total > 0){
            $list = $list->toArray();
            $foodGroup = new FoodGroup();
            foreach($list as &$item){
                if($item['food_group']['version'] == 2){
//                    $src_list = array_column($item['food_group']['images'], 'file_path');
//                    $item['src_list'] = $src_list;
                    $src_list = FoodGroupImage::getImages($item['food_group_id']);
                    if($src_list->isEmpty()){
                        $item['src_list'] = [];
                        $item['images'] = [];
                    }else{
                        $src_list = $src_list->toArray();
                        foreach($src_list as $items){
                            $item['src_list'][] = $items['images']['file_path'];
                        }
                    }
                }else{
                    $image = $foodGroup->getUserImg($item['bmi'],1);
                    $item['src_list'] = [$image];
                    $item['images'] = ['file_path' => $image];
                }
            }
        }
        return compact('list','total');
    }

    /**
     * 用户填写列表条件筛选
     * @param $params
     */
    public function setFillListWhere($params){
        $where = [
            'questionnaire_id' => $params['questionnaire_id'],
            'user_id' => $params['user_id'],
        ];
        if($params['start_time'] && $params['end_time']){
            $where['create_time'] = ['BETWEEN', [strtotime($params['start_time']), strtotime($params['end_time'])]];
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
        if($data){
            $data = $data->toArray();
            $answer = array_column($data['user_answer'], null, 'question_id');
            foreach($data['user_answer'] as &$item){
                $show_limit = QuestionnaireQuestion::getShowLimit($item['questionnaire_id'], $item['question_id']);
                $show_limit = $show_limit?json_decode($show_limit,true):[];
                if($show_limit){
                    $is_show = 0;
                    foreach($answer[$show_limit['question_id']]['answer_mark'] as $mark){
                        if(in_array($mark, $show_limit['option']))$is_show = 1;
                    }
                }else{
                    $is_show = 1;
                }
                $item['is_show'] = $is_show;
            }
        }

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

    /**
     * 我的报告
     * @param $user
     * @param $no
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myReportList($user, $no){
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        $type = input('get.type',10,'intval');
        $questionnaire = Questionnaire::get(['questionnaire_no'=>$no]);
        if(!$questionnaire)throw new Exception('问卷信息不存在');
        $where = [
            'questionnaire_id'=>$questionnaire['questionnaire_id']
        ];
        if($type == 10){
            $where['user_id'] = $user['user_id'];
        }else{
            $where['invite_user_id'] = $user['user_id'];
        }
        $list = $this->where($where)->field(['fill_id', 'create_time'])->page($page, $size)->order('create_time','desc')->select();
        $list = $this->formatReport($list);
        return compact('list');
    }

    /**
     * 格式化报告
     * @param $list
     * @return mixed
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function formatReport($list){
        ##类名new_name
        $new_name_id = Question::where(['name'=>'new_name'])->value('question_id');
        $sex_id = Question::where(['name'=>'sex'])->order('create_time','desc')->value('question_id');
        $health_goals = Question::get(['name'=>'health_goals'], ['option'])->toArray();
        $options = array_column($health_goals['option'], null, 'mark');
        $health_goals_id = $health_goals['question_id'];
        foreach($list as $key => $item){
            ##填写的用户名
            $name = FillAnswer::where(['fill_id'=>$item['fill_id'], 'question_id'=>$new_name_id])->value('answer');
            $name = stripslashes($name);
            ##性别
            $sex = FillAnswer::where(['fill_id'=>$item['fill_id'], 'question_id'=>$sex_id])->value('answer_mark');
            $sex = trim($sex,'-');
            ##选择的改善目标
            $user_health_goals = FillAnswer::where(['fill_id'=>$item['fill_id'], 'question_id'=>$health_goals_id])->value('answer_mark');
            $user_health_goals = explode('-',trim($user_health_goals,'-'));
            $goals = [];
            foreach($user_health_goals as $goal){
                $goals[] = $options[$goal];
            }
            $list[$key]['goals'] = $goals;
            $list[$key]['username'] = $name;
            $list[$key]['sex'] = $sex;
            $list[$key]['create_time_int'] = strtotime($item['create_time']);
            $list[$key]['bg_img'] = [
                'A' => 'http://qiniu.dekichina.com/no-sex.png',
                'B' => 'http://qiniu.dekichina.com/no-sex.png'
            ];
        }
        return $list;
    }

    /**
     * 报告详情
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reportDetail(){
        $fill_id = input('get.fill_id',0,'intval');
        $info = $this
            ->where(['fill_id'=>$fill_id])
            ->field(['fill_id', 'create_time', 'food_group_id', 'bmi', 'pain_point_analysis'])
            ->with(
                [
                    'foodGroup' => function(Query $query){
                        $query
                            ->field(['id'])
                            ->with(
                                [
                                    'images'
                                ]
                            );
                    }
                ]
            )
            ->find();
        if(!$info)throw new Exception('报告信息不存在');
        $info = $this->formatReport([$info->toArray()])[0];
        return compact('info');
    }

    /**
     * 营养建议
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function healthAdvice(){
        $fill_id = input('get.fill_id',0,'intval');
        $info = $this->where(compact('fill_id'))->field(['fill_id', 'bmi', 'advice'])->find();
        if(!$info)throw new Exception('报告信息不存在');
        return compact('info');
    }

    /**
     * 删除报告
     * @param $user
     * @return bool
     * @throws Exception
     */
    public function delReport($user){
        ##参数
        $fill_id = input('post.fill_id',0,'intval');
        if(!$fill_id)throw new Exception('参数缺失');
        ##操作
        $res = $this->update(['delete_time'=>time()], ['user_id'=>$user['user_id'], 'fill_id'=>$fill_id]);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 获取配餐图
     * @param $user
     * @return array|bool|float|int|mixed|object|\stdClass
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getFoodGroup($user){
        ##参数
        $fill_id = input('get.fill_id',0,'intval');
        if(!$fill_id)throw new Exception('参数缺失');
        ##操作
        $data = self::get(['fill_id'=>$fill_id, 'user_id'=>$user['user_id']], ['foodGroup.images']);
        if(!$data)throw new Exception('报告不存在');
        return $data['food_group'];
    }

}