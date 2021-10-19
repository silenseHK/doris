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
use app\api\model\business\P_Staff;

class Staff extends Controller
{

    protected $companyModel, $staffModel;

    public function __construct
    (
        P_Company $company,
        P_Staff $staff
    )
    {
        parent::__construct();
        $this->companyModel = $company;
        $this->staffModel = $staff;
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

}