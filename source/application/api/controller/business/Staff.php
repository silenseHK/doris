<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 9:38
 */

namespace app\api\controller\business;


use app\api\controller\Controller;
use app\api\model\business\P_Company;
use app\api\model\business\P_Department;
use app\api\model\business\P_Staff;
use app\api\validate\business\StaffValid;

class Staff extends Base
{

    protected $companyModel, $staffModel, $departmentModel;

    protected $validate;

    public function __construct
    (
        P_Company $company,
        P_Staff $staff,
        P_Department $p_Department,
        StaffValid $staffValid
    )
    {
        parent::__construct();
        $this->companyModel = $company;
        $this->staffModel = $staff;
        $this->departmentModel = $p_Department;
        $this->validate = $staffValid;
    }

    public function companies()
    {
        if(request()->isPost())
        {
            $list = $this->companyModel->lists();
            return $this->renderSuccess($list);
        }
        return false;
    }

    public function companyStaff()
    {
        if(request()->isPost())
        {
            $list = $this->staffModel->companyStaff();
            return $this->renderSuccess($list);
        }
        return false;
    }

    public function staff()
    {
        if(request()->isPost())
        {
            $list = $this->staffModel->staff();
            return $this->renderSuccess($list);
        }
        return false;
    }

    public function apartment()
    {
        if(request()->isPost())
        {
            $list = $this->departmentModel->department();
            return $this->renderSuccess($list);
        }
        return false;
    }

    public function matterCollect()
    {
        if(request()->isPost())
        {
            if(!$this->validate->scene('collect')->check(request()->post()))
            {
                return $this->renderError($this->validate->getError());
            }
            if(!$this->staffModel->matterCollect($this->user_id))
            {
                return $this->renderError($this->staffModel->getError());
            }
            return $this->renderSuccess('','操作成功');
        }
        return false;
    }

    public function adviceCollect()
    {
        if(request()->isPost())
        {
            if(!$this->validate->scene('collect')->check(request()->post()))
            {
                return $this->renderError($this->validate->getError());
            }
            if(!$this->staffModel->adviceCollect($this->user_id))
            {
                return $this->renderError($this->staffModel->getError());
            }
            return $this->renderSuccess('','操作成功');
        }
        return false;
    }

}