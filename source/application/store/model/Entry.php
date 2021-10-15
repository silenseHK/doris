<?php


namespace app\store\model;

use app\common\model\Entry as EntryModel;
use app\store\validate\EntryValid;

class Entry extends EntryModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = ($this->valid instanceof EntryValid)? $this->valid: new EntryValid();
    }

    /**
     * 词条列表
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function entry(){
        $list = $this->field(['entry_id', 'keywords', 'alias', 'content', 'sort'])->order('sort','asc')->select();
        return $list;
    }

    /**
     * 新增词条
     * @return bool|string
     */
    public function addEntry(){
        ##验证
        if(!$this->valid->scene('add')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $data = $this->filterData();
        ##操作
        $res = $this->isUpdate(false)->save($data);
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return $this->getLastInsID();
    }

    /**
     * 编辑词条
     * @return bool
     */
    public function editEntry(){
        ##验证
        if(!$this->valid->scene('edit')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $data = $this->filterData();
        $entry_id = input('post.entry_id',0,'intval');
        ##操作
        $res = $this->update($data, compact('entry_id'));
        if($res === false){
            $this->error = '操作失败';
        }
        return false;
    }

    /**
     * 过滤数据
     * @return array
     */
    protected function filterData(){
        return [
            'keywords' => input('post.keywords','','str_filter'),
            'alias' => input('post.alias','','str_filter'),
            'content' => input('post.content','','str_filter'),
            'sort' => input('post.sort',0,'intval')
        ];
    }

    /**
     * 编辑排序
     * @return bool
     */
    public function editSort(){
        ##验证
        if(!$this->valid->scene('edit_sort')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $entry_id = input('post.entry_id',0,'intval');
        $sort = input('post.sort',9999,'intval');
        ##操作
        $res = $this->where(compact('entry_id'))->setField('sort',$sort);
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 删除词条
     * @return bool
     */
    public function del(){
        ##验证
        if(!$this->valid->scene('del')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $entry_id = input('post.entry_id',0,'intval');
        ##操作
        $res = $this->where(compact('entry_id'))->delete();
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

}