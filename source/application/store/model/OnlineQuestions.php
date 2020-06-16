<?php


namespace app\store\model;

use app\common\model\OnlineQuestions as OnlineQuestionsModel;
use app\store\validate\OnlineQuestionsValid;
use think\Exception;
use app\store\model\OnlineQuestionsCate;

class OnlineQuestions extends OnlineQuestionsModel
{

    public function getList(){
        $params = [
            'cate_id' => input('cate_id',0,'intval')
        ];
        $this->setWhere($params);
        $list = $this->with(['cate'])->paginate(10,false,['query'=>\request()->request()]);
        $cateModel = new OnlineQuestionsCate();
        $cate_list = $cateModel->cateList();
        return array_merge(compact('list','cate_list'),$params);
    }

    public function setWhere($params){
        if($params['cate_id'] > 0){
            $this->where(['cate_id'=>$params['cate_id']]);
        }
    }

    /**
     * 添加问答
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('add')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 编辑问答
     * @return bool
     * @throws Exception
     */
    public function edit(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('edit')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();
        $question_id = input('post.question_id',0,'intval');
        $res = $this->isUpdate(true)->save($data,compact('question_id'));
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 过滤参数
     * @return array
     */
    public function filterData(){
        return [
            'title' => input('post.title','','str_filter'),
            'cate_id' => input('post.cate_id',0,'intval'),
            'desc' => input('post.desc','','str_filter'),
            'answer' => input('post.answer','','htmlspecialchars'),
            'status' => input('post.status',0,'intval'),
            'sort' => input('post.sort',0,'intval'),
        ];
    }

    /**
     * 修改状态
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function changeStatus(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('change_status')->check(input()))throw new Exception($validate->getError());
        ##参数
        $question_id = input('post.question_id',0,'intval');
        $info = self::get(compact('question_id'));
        if(!$info)throw new Exception('操作失败');
        $res = $this->update(['status'=>($info['status']+1)%2], compact('question_id'));
        if($res === false)throw new Exception('操作失败.');
        return true;
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('del')->check(input()))throw new Exception($validate->getError());
        ##参数
        $question_id = input('post.question_id',0,'intval');
        ##删除
        $res = self::destroy($question_id);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 问答信息
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('info')->check(input()))throw new Exception($validate->getError());
        ##参数
        $question_id = input('question_id',0,'intval');
        $info = self::get(compact('question_id'));
        if(!$info)throw new Exception('数据不存在或已删除');
        $cateModel = new OnlineQuestionsCate();
        $cate_list = $cateModel->cateList();
        return compact('info','cate_list');
    }

}