<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 8:23
 */

namespace app\api\controller\business;

use app\api\model\business\P_Advice;
use app\api\model\business\P_Company;
use app\api\model\business\P_Matter;
use app\api\model\business\P_Matter_Cate;
use app\api\model\business\P_Project;
use app\api\model\business\P_Reform_Log;
use app\api\validate\business\AdviceValid;
use app\api\validate\business\MatterValid;
use app\api\validate\business\ProjectValid;
use app\api\validate\business\ReformValid;

class Project extends Base
{

    protected $projectModel, $companyModel, $matterModel, $matterCateModel, $reformLogModel, $adviceModel;

    protected $validate, $matterValidate, $reformValidate, $adviceValidate;

    public function __construct
    (
        ProjectValid $validate,
        P_Project $p_Project,
        P_Company $p_Company,
        P_Matter $p_Matter,
        P_Matter_Cate $p_Matter_Cate,
        P_Reform_Log $p_Reform_Log,
        P_Advice $p_Advice,
        MatterValid $matterValid,
        ReformValid $reformValid,
        AdviceValid $adviceValid
    )
    {
        parent::__construct();
        $this->validate = $validate;
        $this->projectModel = $p_Project;
        $this->companyModel = $p_Company;
        $this->matterModel = $p_Matter;
        $this->matterCateModel = $p_Matter_Cate;
        $this->reformLogModel = $p_Reform_Log;
        $this->adviceModel = $p_Advice;
        $this->matterValidate = $matterValid;
        $this->reformValidate = $reformValid;
        $this->adviceValidate = $adviceValid;
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

    public function edit()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->validate->scene('edit')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##创建
            if(!$this->projectModel->edit()){
                return $this->renderError($this->projectModel->getError());
            }
            return $this->renderSuccess('','操作成功');
        }
        return false;
    }

    public function del()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->validate->scene('del')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##创建
            if(!$this->projectModel->del()){
                return $this->renderError($this->projectModel->getError());
            }
            return $this->renderSuccess('','操作成功');
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
        if(request()->isPost()){
            ##验证参数
            if(!$this->validate->scene('matters')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##问题列表
            if(!$data = $this->matterModel->projectMatters()){
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess($data);
        }
        return false;
    }

    public function matterCateList()
    {
        if(request()->isPost()){
            return $this->renderSuccess($this->matterCateModel->cateLists(true));
        }
        return false;
    }

    public function addMatter()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->matterValidate->scene('add')->check(request()->post())){
                return $this->renderError($this->matterValidate->getError());
            }
            ##创建
            if(!$this->matterModel->add()){
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess('','创建成功');
        }
        return false;
    }

    public function matterDetail()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->matterValidate->scene('detail')->check(request()->post())){
                return $this->renderError($this->matterValidate->getError());
            }
            ##创建
            if(!$data = $this->matterModel->detail()){
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess($data);
        }
        return false;
    }

    /**
     * 编辑问题
     * @return array|false
     */
    public function editMatter()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->matterValidate->scene('edit')->check(request()->post())){
                return $this->renderError($this->matterValidate->getError());
            }
            ##创建
            if(!$this->matterModel->edit()){
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess('','修改成功');
        }
        return false;
    }

    /**
     * 删除问题
     * @return array|bool
     */
    public function delMatter()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->matterValidate->scene('del')->check(request()->post())){
                return $this->renderError($this->matterValidate->getError());
            }
            ##创建
            if(!$this->matterModel->del()){
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess('','操作成功');
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

    /**
     * 整改列表
     * @return array|false
     */
    public function reformList()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->reformValidate->scene('list')->check(request()->post())){
                return $this->renderError($this->reformValidate->getError());
            }
            $data = $this->reformLogModel->lists();
            return $this->renderSuccess($data);
        }
        return false;
    }

    /**
     * 新增整改记录
     * @return array|false
     */
    public function addReform()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->reformValidate->scene('add')->check(request()->post())){
                return $this->renderError($this->reformValidate->getError());
            }
            if(!$this->reformLogModel->add())
            {
                return $this->renderError($this->reformLogModel->getError());
            }
            return $this->renderSuccess('','操作成功');
        }
        return false;
    }

    /**
     * 完成整改
     * @return array|false
     */
    public function finishMatter()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->matterValidate->scene('done')->check(request()->post())){
                return $this->renderError($this->matterValidate->getError());
            }
            if(!$this->matterModel->done())
            {
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess('','操作成功');
        }
        return false;
    }

    /**
     * 添加指导意见
     * @return array|false
     */
    public function advice()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->adviceValidate->scene('add')->check(request()->post())){
                return $this->renderError($this->adviceValidate->getError());
            }
            if(!$this->adviceModel->add())
            {
                return $this->renderError($this->matterModel->getError());
            }
            return $this->renderSuccess('','操作成功');
        }
        return false;
    }

}