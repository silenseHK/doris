<?php


namespace app\common\model\project;


use traits\model\SoftDelete;

class P_Company extends P_Base
{

    protected $name = 'p_company';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    public function levelCate()
    {
        return $this->field('id, title, level, pid')->select()->toArray();
    }

    public function lists()
    {
        $list = $this->field('id, title, level, pid')->select()->toArray();
        return $this->getTree($list,0);
    }

    public function adminLists()
    {
        $list = $this->field('id as value, title as label, pid')->select()->toArray();
        array_unshift($list,['value'=>-1, 'label'=>'顶级','pid'=>0]);
        return $this->getAdminTree($list,0);
    }

    public function parent()
    {
        return $this->belongsTo('app\common\model\project\P_Company','pid','id');
    }

    public function getParents($id, &$tree)
    {
        array_unshift($tree,(int)$id);
        $pid = $this->where('id',$id)->value('pid');
        if($pid){
            $this->getParents($pid,$tree);
        }
    }

    protected function getTree($data, $pId)
    {
        $tree = array();
        foreach($data as $k => $v)
        {
          if($v['pid'] == $pId)
          {
              //父亲找到儿子
               $v['children'] = $this->getTree($data, $v['id']);
               $tree[] = $v;
          }
        }
        return $tree;
    }

    protected function getAdminTree($data, $pId)
    {
        $tree = array();
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pId)
            {
                //父亲找到儿子
                $v['children'] = $this->getAdminTree($data, $v['value']);
                $tree[] = $v;
            }
        }
        return $tree;
    }

}