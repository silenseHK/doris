<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 8:23
 */

namespace app\api\controller\business;

class Project extends Base
{

    protected $validate;

    public function __construct(\app\api\validate\project\Project $project)
    {
        parent::__construct();
        $this->validate = $project;
    }

    public function createProject()
    {
        if(!request()->isPost()){
            return $this->renderError();
        }
        ##验证参数
        if(!$data = $this->validate->scene('add')->check(request()->post())){
            return $this->validate->getError();
        }
        var_dump($data);
    }

}