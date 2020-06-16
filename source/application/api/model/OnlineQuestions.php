<?php


namespace app\api\model;

use app\common\model\OnlineQuestions as OnlineQuestionsModel;
use app\api\validate\content\OnlineQuestionsValid;
use think\Exception;

class OnlineQuestions extends OnlineQuestionsModel
{

    protected $valid;

    protected $hidden = ['delete_time', 'status', 'sort', 'is_recom', 'wxapp_id'];

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new OnlineQuestionsValid();
    }

    /**
     * 问答列表
     * @return array
     * @throws Exception
     */
    public function getList(){
        ##验证
        if(!$this->valid->scene('answer_list')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $cate_id = input('get.cate_id',0,'intval');
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        $keywords = input('get.keywords','','keywords_filter');
        ##操作
        $this->setWhere($cate_id, $keywords);
        $list = $this->field(['question_id', 'title', 'desc'])->order('sort','asc')->order('question_id','asc')->limit(($page-1)*$size, $size)->select();
        return compact('list');
    }

    /**
     * 设置查询条件
     * @param $cate_id
     * @param $keywords
     */
    public function setWhere($cate_id, $keywords){
        $where = [
            'status' => 1,
        ];
        if($cate_id)
            $where['cate_id'] = $cate_id;
        if($keywords)
            $where['title'] = ['LIKE', "%{$keywords}%"];
        $this->where($where);
    }

    /**
     * 问答详情
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function info(){
        ##验证
        if(!$this->valid->scene('info')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $question_id = input('get.question_id',0,'intval');
        $info = self::get(['question_id'=>$question_id, 'status'=> 1]);
        if(!$info)throw new Exception('问答不存在或已删除');
        ##增加浏览次数
        $this->where(compact('question_id'))->setInc('scan_times',1);
        return compact('info');
    }

}