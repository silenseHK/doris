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

            $questionnaire_id = $this->getLastInsID();
            $this->handleQuestionnaire($questionnaire_id, $data);

            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
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
//        print_r(input());die;
        Db::startTrans();
        try{
            ##增加调查表
            $res = $this->isUpdate(true)->allowField(true)->save($data, compact('questionnaire_id'));
            if($res === false)throw new Exception('操作失败');
            ##增加关联问题
            ###删除以前的关联
            $model = new QuestionnaireQuestionModel();
            $model->deleteLink($questionnaire_id);
            $cateModel = new QuestionnaireCate();
            $cateModel->deleteLink($questionnaire_id);

            $this->handleQuestionnaire($questionnaire_id, $data);

            Db::commit();
            return true;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function handleQuestionnaire($questionnaire_id, $data){
        $cate_data = [];
        $question_data = [];
        $sort = 1;
        ##组织数据
        foreach($data['questions'] as $item){

            $cate_data[] = [
                'questionnaire_id' => $questionnaire_id,
                'question_cate_id' => $item['cate_id'],
                'sort' => $sort
            ];
            ++$sort;
            $sort_ = 1;
            foreach($item['questions'] as $it){
                $show_limit = isset($it['show_limit'])? $it['show_limit'] : '';
                if($show_limit){
                    $show_limit = str_replace('&quot;','"',$show_limit);
                    if(is_array($show_limit) && !empty($show_limit))$show_limit = json_encode($show_limit);
                }
                $question_data[] = [
                    'questionnaire_id' => $questionnaire_id,
                    'question_id' => $it['question_id'],
                    'sort' => $sort_,
                    'question_cate_id' => $item['cate_id'],
                    'show_limit' => $show_limit
                ];
                ++$sort_;
            }
        }
        ##增加分类关联
        $cateModel = new QuestionnaireCate();
        $res = $cateModel->isUpdate(false)->saveAll($cate_data);
        if($res === false)throw new Exception('操作失败');

        ##增加问题关联
        $model = new QuestionnaireQuestionModel();
        $res = $model->isUpdate(false)->saveAll($question_data);
        if($res === false)throw new Exception('操作失败.');
    }

    public function filterData(){
        return [
            'title' => input('post.title','','str_filter'),
            'status' => input('post.status','','intval'),
            'questions' => input('post.questions/a',''),
            'questionnaire_no' => input('post.questionnaire_no','','str_filter')
        ];
    }

    public function info(){
        ##验证
        $validate = new QuestionValid();
        if(!$validate->scene('questionnaire_info')->check(input()))throw new Exception($validate->getError());
        $questionnaire_id = input('questionnaire_id',0,'intval');
        $info = self::get(['questionnaire_id'=>$questionnaire_id], ['questions.option','cate'])->toArray();
        $cate_list = $info['cate'];
        $question_list = $info['questions'];
        $questionModel = new Question();
        ##获取问题数据
        foreach($cate_list as &$cate){
            $cate['questions'] = [];
            foreach($question_list as $question){
                if($question['pivot']['question_cate_id'] == $cate['pivot']['question_cate_id']){
                    $question['show_limit'] = $question['pivot']['show_limit'];
                    $question['show_limit_txt'] = $questionModel->getShowLimitTxt($question['pivot']['show_limit']);
                    $question['type'] = $question['type']['value'];
                    $cate['questions'][] = $question;
                }
            }
        }
        return compact('info','cate_list');
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