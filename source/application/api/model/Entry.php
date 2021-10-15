<?php


namespace app\api\model;

use app\common\model\Entry as EntryModel;

class Entry extends EntryModel
{

    /**
     * 词条
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function entry(){
        $list = $this->field(['entry_id', 'keywords', 'alias', 'content'])->order('sort','asc')->select();
        $list = $list->isEmpty() ? [] : $list->toArray();
        return $list;
    }

}