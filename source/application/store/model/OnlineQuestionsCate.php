<?php


namespace app\store\model;

use app\common\model\OnlineQuestionsCate as OnlineQuestionsCateModel;
use app\store\validate\OnlineQuestionsValid;
use think\db\Query;
use think\Exception;

class OnlineQuestionsCate extends OnlineQuestionsCateModel
{

    /**
     * 列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getList(){
        $list = $this
            ->with(
                [
                    'icon' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name']);
                    }
                ]
            )
            ->order('sort','asc')
            ->paginate(10,false,['Query'=>\request()->request()]);
        return compact('list');
    }

    /**
     * 简单列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cateList(){
        return $this->field(['cate_id', 'title'])->order('sort','asc')->select();
    }

    /**
     * 添加
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('cate_add')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterData();
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 编辑
     * @return bool
     * @throws Exception
     */
    public function edit(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('cate_edit')->check(input()))throw new Exception($validate->getError());
        ##参数
        $cate_id = input('post.cate_id',0,'intval');
        $data = $this->filterData();
        $res = $this->isUpdate(true)->save($data,['cate_id'=>$cate_id]);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 信息
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function info(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('cate_info')->check(input()))throw new Exception($validate->getError());
        ##参数
        $cate_id = input('cate_id',0,'intval');
        $info = self::get(['cate_id'=>$cate_id]);
        if(!$info)throw new Exception('数据不存在或已删除');
        return compact('info');
    }

    /**
     * 获取参数
     * @return array
     */
    public function filterData(){
        return [
            'title' => input('post.title','','str_filter'),
            'icon_id' => input('post.img_id',0,'intval'),
            'sort' => input('post.sort',0,'intval'),
            'status' => input('post.status',0,'intval'),
        ];
    }

    /**
     * 编辑状态
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function changeStatus(){
        ##验证
        $validate = new OnlineQuestionsValid();
        if(!$validate->scene('cate_change_status')->check(input()))throw new Exception($validate->getError());
        ##参数
        $cate_id = input('cate_id',0,'intval');
        $info = self::get(compact('cate_id'));
        if(!$info)throw new Exception('操作失败');
        $res = $this->update(['status'=>($info['status'] + 1)%2], ['cate_id'=>$cate_id]);
        if($res === false)throw new Exception('操作失败');
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
        if(!$validate->scene('cate_del')->check(input()))throw new Exception($validate->getError());
        ##参数
        $cate_id = input('cate_id',0,'intval');
        $res = self::destroy($cate_id);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

}