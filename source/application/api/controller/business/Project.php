<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 8:23
 */

namespace app\api\controller\business;

use app\api\model\business\P_Company;
use app\api\model\business\P_Matter;
use app\api\model\business\P_Project;
use app\api\validate\business\ProjectValid;

class Project extends Base
{

    protected $projectModel, $companyModel, $matterModel;

    protected $validate, $matterValidate;

    public function __construct
    (
        ProjectValid $validate,
        P_Project $p_Project,
        P_Company $p_Company,
        P_Matter $p_Matter
    )
    {
        parent::__construct();
        $this->validate = $validate;
        $this->projectModel = $p_Project;
        $this->companyModel = $p_Company;
        $this->matterModel = $p_Matter;
    }

    public function lists()
    {
        if(request()->isPost()){
            $lists = $this->projectModel->lists();
            return $this->renderSuccess($lists);
        }
        return false;
    }

    public function add()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->validate->scene('add')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##创建
            if(!$this->projectModel->add()){
                return $this->renderError($this->projectModel->getError());
            }
            return $this->renderSuccess('','创建成功');
        }
        return false;
    }

    public function detail()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->validate->scene('detail')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##创建
            if(!$data = $this->projectModel->detail()){
                return $this->renderError($this->projectModel->getError());
            }
            return $this->renderSuccess($data);
        }
        return false;
    }

    public function matters()
    {

    }

    public function addMatter()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->matterValidate->scene('add')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##创建
            if(!$this->matterModel->add()){
                return $this->renderError($this->projectModel->getError());
            }
            return $this->renderSuccess('','创建成功');
        }
        return false;
    }

    /**
     * 项目所属单位列表
     * @return array|false
     */
    public function companyLists()
    {
        if(request()->isPost()){
            $data = $this->companyModel->projectCompanyLists();
            return $this->renderSuccess($data);
        }
        return false;
    }

}