<?php


namespace app\store\model;

use app\common\model\Salesperson as SalespersonModel;
use app\store\service\user\Export;
use app\store\validate\SalespersonValid;
use think\db\Query;

class Salesperson extends SalespersonModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new SalespersonValid();
    }

    /**
     * 招商列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function salespersonList(){
        $this->setSalespersonWhere();
        $list = $this
            ->with([
                'user'=>function(Query $query){
                    $query->field(['user_id', 'nickName', 'avatarUrl']);
                }
            ])
            ->field(['salesperson_id', 'user_id', 'name', 'create_time', 'type', 'status', 'group_id'])
            ->paginate(15,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getSalespersonList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = empty($list)? [] : $list->toArray()['data'];
        return compact('page','total','list');
    }

    /**
     * 这是查询条件
     */
    protected function setSalespersonWhere(){
        $name = input('name','','search_filter');
        if($name)
            $where['name'] = ['LIKE', "%{$name}%"];

        $id = input('id',0,'intval');
        if($id > 0)
            $where['user_id|salesperson_id'] = $id;

        $type = input('type',0,'intval');
        if($type > 0)
            $where['type'] = $type;

        $group_id = input('group_id',0,'intval');
        if($group_id > 0)
            $where['group_id'] = $group_id;

        $status = input('status',-1,'intval');
        if($status >= 0)
            $where['status'] = $status;

        isset($where) && $this->where($where);
    }

    /**
     * 添加
     * @return bool
     */
    public function add(){
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
        return true;
    }

    /**
     * 编辑
     * @return bool
     */
    public function edit(){
        ##验证
        $salesperson_id = input('post.salesperson_id',0,'intval');
        $rule = [
            'user_id' => "require|number|>=:1|unique:salesperson,user_id,{$salesperson_id}"
        ];
        if(!$this->valid->scene('edit')->rule($rule)->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $data = $this->filterData();
        ##操作
        $res = $this->update($data, compact('salesperson_id'));
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    protected function filterData(){
        return [
            'user_id' => input('post.user_id',0,'intval'),
            'name' => input('post.name','','str_filter'),
            'group_id' => input('post.group_id',0,'intval'),
            'type' => input('post.type',0,'intval'),
        ];
    }

    /**
     * 删除
     * @return bool
     */
    public function del(){
        ##验证
        if(!$this->valid->scene('del')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $salesperson_id = input('post.salesperson_id',0,'intval');
        ##操作
        $res = $this->where(compact('salesperson_id'))->delete();
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 编辑状态
     * @return bool
     * @throws \think\exception\DbException
     */
    public function editStatus(){
        ##验证
        if(!$this->valid->scene('edit_status')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $salesperson_id = input('post.salesperson_id',0,'intval');
        $salesperson = self::get($salesperson_id);
        if(!$salesperson){
            $this->error = '招商信息不存在';
            return false;
        }
        ##操作
        $status = ($salesperson['status'] + 1) % 2;
        $res = $salesperson->update(compact('status'), compact('salesperson_id'));
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return $status;
    }


    public function exportSaleData(){
        ##验证
        if(!$this->valid->scene('export_sale_data')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        $this->setSalespersonWhere();
        $start_time = input('start_time','','str_filter');
        $end_time = input('end_time','','str_filter');
        $users = $this->field(['user_id', 'name', 'group_id', 'type'])->select();
        if($users->isEmpty()){
            $this->error = '暂无符合条件的招商人员';
            return false;
        }
        $users = $users->toArray();
        ##获取业绩
        $orderModel = new Order();
        $sale_data = $orderModel->statisticsSaleDataS($users, $start_time, $end_time);
        $export = new Export();
        return $export->salespersonSaleData($sale_data, $start_time, $end_time);
    }

}