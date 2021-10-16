<?php


namespace app\api\model\business;


use \app\common\model\project\P_Company as Base_P_Company;
use think\Db;

class P_Company extends Base_P_Company
{

    public function projectCompanyLists()
    {
        $company_ids = Db::name('p_project')->whereNull('delete_time')->group('company_id')->column('company_id');
        return $this->whereIn('id',$company_ids)->field('id, title')->select();
    }

}