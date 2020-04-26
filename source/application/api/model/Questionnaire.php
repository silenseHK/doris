<?php


namespace app\api\model;

use app\api\model\user\Fill;
use app\api\model\user\FillAnswer;
use app\api\validate\user\questionnaireValidate;
use app\common\model\FoodGroup;
use app\common\model\Questionnaire as QuestionnaireModel;
use app\api\model\Question as QuestionModel;
use app\api\model\user\Fill as FillModel;
use app\api\model\user\FillAnswer as FillAnswerModel;
use think\Db;
use think\Exception;

class Questionnaire extends QuestionnaireModel
{

    protected $hidden = ['delete_time', 'update_time', 'create_time', 'wxapp_id', 'status'];

    /**
     * 问卷信息
     * @param $questionnaire_no
     * @param $user
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($questionnaire_no, $user){
        $info = $this
            ->where(compact('questionnaire_no'))
            ->with(
                [
                    'questions.option'
                ]
            )
            ->find();
        if(!$info)throw new Exception('问卷不存在');
        if($info['status'] != 1)throw new Exception('问卷已下架');
        ##判断用户是否已提交
        $answer = Fill::where(['user_id'=>$user['user_id'], 'questionnaire_id'=>$info['questionnaire_id']])->find();
        $is_submit = 0;
        $img = '';
        if($answer){
            $is_submit = 1;
            $img = FoodGroup::getUserImg($answer['bmi']);
        }
        $info['is_submit'] = $is_submit;
        $info['img'] = $img;
        return $info;
    }

    /**
     * 提交问卷
     * @param $user
     * @return array|string
     * @throws Exception
     */
    public function submitQuestionnaire($user){
        ##验证
        $validate = new questionnaireValidate();
        $params = @file_get_contents('php://input');
        $params = json_decode($params,true);
        if(!$validate->scene('submit')->check($params))throw new Exception($validate->getError());
        ##参数
        $questionnaire_id = intval($params['questionnaire_id']);
        ##检查是否已提交
        $fillModel = new FillModel();
//        if($fillModel->where(['user_id'=>$user['user_id'], 'questionnaire_id'=>$questionnaire_id])->count() > 0)throw new Exception('请勿重复提交');
        $answer = $params['answer'];
        if(empty($answer))throw new Exception('参数错误');
        Db::startTrans();
        try{
            $data = [
                'user_id' => $user['user_id'],
                'questionnaire_id' => $questionnaire_id
            ];

            $res = $fillModel->isUpdate(false)->save($data);
            if($res === false)throw new Exception('提交失败');
            $fill_id = $fillModel->getLastInsID();
            $questionModel = new QuestionModel();
            $fillAnswerModel = new FillAnswer();
            $question_data = [];
            foreach($answer as $item){
                $item_data = $questionModel->checkAnswer($item);
                $item_data['user_id'] = $user['user_id'];
                $item_data['questionnaire_id'] = $questionnaire_id;
                $item_data['fill_id'] = $fill_id;
                $question_data[] = $item_data;
            }
            $res = $fillAnswerModel->isUpdate(false)->saveAll($question_data);
            if($res === false)throw new Exception('提交失败...');

            ##计算得分和BMI
            $pointBMI = Fill::countPointBMI($fill_id);
            $res = $fillModel->where(['fill_id'=>$fill_id])->update($pointBMI);
            if($res === false)throw new Exception('提交失败');
            Db::commit();
            return $pointBMI;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }

    }

}