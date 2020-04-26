<?php


namespace app\store\model;

use app\api\model\user\Fill;
use app\common\model\Questionnaire as QuestionnaireModel;
use app\store\model\Question as QuestionModel;
use app\store\model\QuestionnaireQuestion as QuestionnaireQuestionModel;
use app\store\validate\QuestionValid;
use think\Db;
use think\Exception;

class Questionnaire extends QuestionnaireModel
{

    public function getIndexData(){
        $status = input('status',0,'intval');
        $where = "1=1";
        if($status > 0)$where .= " and status = {$status}";
        $list = $this->where($where)->paginate(15,false,['query' => \request()->request()]);
        $list->append(['fill_num']);
        return compact('list','status');
    }

    public function getFillNumAttr($value, $data){
        return Fill::where(['questionnaire_id'=>$data['questionnaire_id']])->count();
    }

    public function add(){
        ##验证
        $validate = new QuestionValid();
        if(!$validate->scene('questionnaire_add')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();
        Db::startTrans();
        try{
            ##增加调查表
            $res = $this->isUpdate(false)->allowField(true)->save($data);
            if($res === false)throw new Exception('操作失败');
            ##增加关联问题
            $questionnaire_id = $this->getLastInsID();
            $question_data = [];
            $sort = 1;
            foreach($data['question_ids'] as $item){
                $question_data[] = [
                    'questionnaire_id' => $questionnaire_id,
                    'question_id' => $item,
                    'sort' => $sort
                ];
                ++$sort;
            }
            $model = new QuestionnaireQuestionModel();
            $res = $model->isUpdate(false)->saveAll($question_data);
            if($res === false)throw new Exception('操作失败.');

            Db::commit();
            return true;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function edit(){
        ##验证
        $validate = new QuestionValid();
        $questionnaire_id = input('post.questionnaire_id',0,'intval');
        $rule = [
            'questionnaire_no' => "require|unique:questionnaire,questionnaire_no,{$questionnaire_id},questionnaire_id"
        ];
        if(!$validate->scene('questionnaire_edit')->rule($rule)->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();

        Db::startTrans();
        try{
            ##增加调查表
            $res = $this->isUpdate(true)->allowField(true)->save($data, compact('questionnaire_id'));
            if($res === false)throw new Exception('操作失败');
            ##增加关联问题
            ###删除以前的关联
            $model = new QuestionnaireQuestionModel();
            $model->deleteLink($questionnaire_id);
            $question_data = [];
            $sort = 1;
            foreach($data['question_ids'] as $item){
                $question_data[] = [
                    'questionnaire_id' => $questionnaire_id,
                    'question_id' => $item,
                    'sort' => $sort
                ];
                ++$sort;
            }

            $res = $model->isUpdate(false)->saveAll($question_data);
            if($res === false)throw new Exception('操作失败.');

            Db::commit();
            return true;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function filterData(){
        return [
            'title' => input('post.title','','str_filter'),
            'status' => input('post.status','','intval'),
            'question_ids' => input('post.question_ids/a',''),
            'questionnaire_no' => input('post.questionnaire_no','','str_filter')
        ];
    }

    public function info(){
        ##验证
        $validate = new QuestionValid();
        if(!$validate->scene('questionnaire_info')->check(input()))throw new Exception($validate->getError());
        $questionnaire_id = input('questionnaire_id',0,'intval');
        $info = self::get(['questionnaire_id'=>$questionnaire_id], ['questions.option']);
        return compact('info');
    }

    public function del(){
        ##验证
        $validate = new QuestionValid();
        if(!$validate->scene('questionnaire_del')->check(input()))throw new Exception($validate->getError());
        $questionnaire_id = input('post.questionnaire_id',0,'intval');
        $res = self::destroy($questionnaire_id);
        if($res === false)throw new Exception('操作失败');
    }

}