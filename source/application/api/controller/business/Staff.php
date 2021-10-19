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

class Staff extends Controller
{

    protected $companyModel;

    public function __construct
    (
        P_Company $company
    )
    {
        parent::__construct();
        $this->companyModel = $company;
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

}