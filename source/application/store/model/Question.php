<?php


namespace app\store\model;

use app\common\model\Question as QuestionModel;
use app\store\validate\QuestionValid;
use think\Db;
use think\Exception;

class Question extends QuestionModel
{

    public function getIndexData(){
        $type = input('type',0,'intval');
        $where = "1=1";
        if($type > 0)$where .= " and type = {$type}";
        $list = $this->where($where)->with(['option'])->paginate(15,false,['query' => \request()->request()]);
        $typeList = $this->getTypeList();
        return compact('list','typeList','type');
    }

    public function getQuestionList(){
        $type = input('type',0,'intval');
        $where = "1=1";
        if($type > 0)$where .= " and type = {$type}";
        $list = $this->where($where)->with(['option'])->paginate(10,false,['type' => 'Bootstrap',
            'var_page' => 'page',
            'path' => 'javascript:initQuestionList([PAGE]);']);

        $page = $list->render();
        $total = $list->total();
        $list = $list->toArray()['data'];
        $typeList = $this->getTypeList();
        return compact('list','typeList','type','page','total');
    }

    public function add(){
        ##验证
        $validate = new QuestionValid();
        if(!$validate->scene('add')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();
        ##操作
        Db::startTrans();
        try{
            ##增加问题
            $res = $this->isUpdate(false)->save($data);
            if($res === false)throw new Exception('操作失败');
            ##增加选项
            if($data['type'] == 20 || $data['type'] == 30){
                $answer = input('post.answer/a',[]);
                if(empty($answer))throw new Exception('请添加选项');
                $answer_id = $this->getLastInsID();
                foreach($answer as &$an){
                    $an['question_id'] = $answer_id;
                    $an['mark'] = strtoupper($an['mark']);
                }
                $model = new QuestionOptions();
                $res = $model->isUpdate(false)->allowField(true)->saveAll($answer);
                if($res === false)throw new Exception('操作失败...');
            }
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
        if(!$validate->scene('edit')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();
        $question_id = input('post.question_id',0,'intval');
        ##操作
        Db::startTrans();
        try{
            ##修改问题
            $res = $this->isUpdate(true)->save($data, compact('question_id'));
            if($res === false)throw new Exception('操作失败');
            ##选项
            if($data['type'] == 20 || $data['type'] == 30){
                $answer = input('post.answer/a',[]);
                if(empty($answer))throw new Exception('请添加选项');
                $model = new QuestionOptions();
                $model->delOption($question_id);
                ##删除以前的选项
                foreach($answer as &$an){
                    $an['question_id'] = $question_id;
                }
                $model = new QuestionOptions();
                $res = $model->isUpdate(false)->allowField(true)->saveAll($answer);
                if($res === false)throw new Exception('操作失败...');
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    public function filterData(){
        return [
            'name' => input('post.name','','str_filter'),
            'label' => input('post.label','','str_filter'),
            'type' => input('post.type','','intval'),
            'is_require' => input('post.is_require','','intval'),
            'icon' => input('post.icon','','str_filter'),
            'choose_limit' => input('post.choose_limit',0,'intval'),
            'is_analysis' => input('post.is_analysis',0,'intval'),
            'tips' => input('post.tips','','str_filter')
        ];
    }

    public function info(){
        $question_id = input('question_id',0,'intval');
        if($question_id <= 0)throw new Exception('参数错误');
        ##获取数据
        $info = self::get(compact('question_id'),['option']);
        if(!$info)throw new Exception('数据不存在');
        $typeList = $this->getTypeList();
        return compact('info','typeList');
    }

    public function del(){
        ##验证
        $validate = new QuestionValid();
        if(!$validate->scene('del')->check(input()))throw new Exception($validate->getError());
        ##参数
        $question_id = input('post.question_id',0,'intval');
        ##删除
        $res = Question::destroy($question_id);
        if($res === false)throw new Exception('操作失败');
    }

    /**
     * 获取出现条件文本
     * @param $show_limit
     * @return string
     * @throws \think\exception\DbException
     */
    public function getShowLimitTxt($show_limit){
        $show_limit = json_decode($show_limit,true);
        $show_limit_txt = '设置出现条件';
        if($show_limit){
            $question = self::get(['question_id'=>$show_limit['question_id']], ['option']);
            $show_limit_txt = $question['label'] . ":";
            foreach($question['option'] as $val){
                if(in_array($val['mark'], $show_limit['option'])){
                    $show_limit_txt .= $val['label'] . ",";
                }
            }
        }
        return trim($show_limit_txt,',');
    }

}