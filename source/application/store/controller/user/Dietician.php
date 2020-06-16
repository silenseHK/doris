<?php


namespace app\store\controller\user;


use app\store\controller\Controller;
use app\store\model\store\DieticianTeam;
use app\store\model\store\User;
use think\Exception;

class Dietician extends Controller
{

    /**
     * 营养师团队列表
     * @return mixed
     */
    public function lists(){
        $model = new User();
        return $this->fetch('',$model->getDietician());
    }

    /**
     * 编辑营养师团队
     * @return array|mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editTeam(){
        if(request()->isPost()){
            $model = new DieticianTeam();
            $res = $model->editTeam();
            if(!$res)throw new Exception($model->getError());
            return $this->renderSuccess('操作成功');
        }else{
            $model = new DieticianTeam();
            return $this->fetch('',$model->teamInfo());
        }
    }

}