<?php


namespace app\store\model;

use app\common\model\QuestionCate as QuestionCateModel;
use think\Exception;

class QuestionCate extends QuestionCateModel
{

    public function getCateIndexData(){
        $list = $this->paginate(20,false,['type' => 'Bootstrap',
            'var_page' => 'page',
            'path' => 'javascript:getCateList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = $list->toArray()['data'];
        return compact('list','page','total');
    }

    /**
     * 新增问题分类
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function add(){
        ##参数
        $title = input('post.title','','str_filter');
        $alias = input('post.alias','','str_filter');
        if(!$title || !$alias)throw new Exception('参数缺失');
        ##验证唯一
        $check = self::get(['title'=>$title]);
        if($check)throw new Exception('分类名已存在');
        ##添加分类
        $res = $this->isUpdate(false)->save(compact('title','alias'));
        if($res === false)throw new Exception('添加失败');
        return true;
    }

    /**
     * 修改问题分类
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function editCate(){
        ##参数
        $title = input('post.title','','str_filter');
        $alias = input('post.alias','','str_filter');
        $cate_id = input('post.cate_id','','intval');
        if(!$title || !$alias || !$cate_id)throw new Exception('参数缺失');
        ##验证唯一
        $check = self::get(['title'=>$title, 'cate_id'=>['NEQ', $cate_id]]);
        if($check)throw new Exception('分类名已存在');
        ##添加分类
        $res = $this->update(compact('title','alias'), ['cate_id'=>$cate_id]);
        if($res === false)throw new Exception('修改失败');
        return true;
    }

    /**
     * 删除问题分类
     * @return bool
     * @throws Exception
     */
    public function delCate(){
        ##参数
        $cate_id = input('post.cate_id',0,'intval');
        $res = self::destroy($cate_id);
        if($res === false)throw new Exception('删除失败');
        return true;
    }

    /**
     * 获取为分页分类列表
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCateList(){
        ##参数
        $cate_ids = input('post.cate_ids','','str_filter');
        $cate_ids = trim($cate_ids,',');
        ##数据
        $list = $this->where(['cate_id'=>['NOT IN', $cate_ids]])->field(['cate_id', 'title', 'alias'])->select();
        return $list;
    }

}