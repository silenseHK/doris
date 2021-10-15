<?php


namespace app\store\model;

use app\common\model\Agent as AgentModel;
use app\store\validate\AgentValid;
use think\db\Query;
use think\Exception;

class Agent extends AgentModel
{

    /**
     * 代理列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function agentList(){
        $this->setAgentListWhere();
        $list = $this
            ->field(['agent_id', 'user_id', 'create_time', 'status', 'login_time', 'mobile'])
            ->with([
                'user' => function(Query $query){
                    $query->field(['user_id', 'nickName', 'avatarUrl', 'grade_id'])->with(['grade'=>function(Query $query){$query->field(['grade_id', 'name']);}]);
                }
            ])
            ->order('create_time','desc')
            ->paginate(15,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getAgentList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = $list->isEmpty() ? [] : $list->toArray()['data'];
        return compact('list','total','page');
    }

    protected function setAgentListWhere(){
        $user_id = input('post.user_id',0,'intval');
        $mobile = input('post.mobile','','search_filter');
        if($user_id)
            $where['user_id|agent_id'] = $user_id;

        if($mobile)
            $where['mobile'] = ['LIKE', "%{$mobile}%"];

        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($start_time && $end_time)
            $where['create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];

        isset($where) && $this->where($where);
    }

    /**
     * 新增
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        $valid = new AgentValid();
        if(!$valid->scene('add')->check(input()))throw new Exception($valid->getError());
        ##参数
        $data = $this->filterData();
        $data['password'] = encrypt_pwd($data['password']);
        ##操作
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
        $agent_id = input('post.agent_id',0,'intval');
        ##验证
        $valid = new AgentValid();
        $rule = [
            'user_id' => "require|number|unique:agent,user_id,{$agent_id},agent_id"
        ];
        if(!$valid->scene('edit')->rule($rule)->check(input()))throw new Exception($valid->getError());
        $data = $this->filterData();
        unset($data['password']);
        ##操作
        $res = $this->isUpdate(true)->save($data,compact('agent_id'));
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 过滤参数
     * @return array
     */
    public function filterData(){
        return [
            'user_id' => input('post.user_id',0,'intval'),
            'mobile' => input('post.mobile','','str_filter'),
            'password' => input('post.password','','str_filter'),
        ];
    }

    /**
     * 修改状态
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function editStatus(){
        ##验证
        $valid = new AgentValid();
        if(!$valid->scene('edit_status')->check(input()))throw new Exception($valid->getError());
        ##参数
        $agent_id = input('post.agent_id',0,'intval');
        ##操作
        $agent = self::get(compact('agent_id'));
        if(!$agent)throw new Exception('账号不存在');
        $status = ($agent['status']+1) % 2;
        $res = $agent->isUpdate(true)->save(compact('status'));
        if($res === false)throw new Exception('操作失败');
        return $status;
    }

    /**
     * 修改密码
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function editPwd(){
        ##验证
        $valid = new AgentValid();
        if(!$valid->scene('edit_pwd')->check(input()))throw new Exception($valid->getError());
        ##参数
        $agent_id = input('post.agent_id',0,'intval');
        $password = input('post.password','','str_filter');
        ##操作
        $agent = self::get(compact('agent_id'));
        if(!$agent)throw new Exception('账号不存在');
        $password = encrypt_pwd($password);
        $res = $agent->where(compact('agent_id'))->setField('password', $password);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

}