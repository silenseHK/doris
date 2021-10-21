<?php


namespace app\store\controller\project;


use app\store\controller\Controller;
use app\store\model\project\P_Matter_cate;
use app\store\validate\MatterValid;

class Matter extends Controller
{

    protected $matterCateModel;

    protected $validate;

    public function __construct
    (
        P_Matter_cate $p_Matter_cate,
        MatterValid $validate
    )
    {
        parent::__construct();
        $this->matterCateModel = $p_Matter_cate;
        $this->validate = $validate;
    }

    public function cate()
    {
        ##问题分类列表
        $lists = $this->matterCateModel->cateLists();
        return $this->fetch('',compact('lists'));
    }

    public function addCate()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$data = $this->validate->scene('add')->check(input())){
                return $this->renderError($this->validate->getError());
            }
            ##获取数据
            if(!$this->matterCateModel->add()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            return $this->fetch('add_cate', ['cateList' => $this->matterCateModel->cateLists()]);
        }
    }

    public function editCate()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$data = $this->validate->scene('edit')->check(input())){
                return $this->renderError($this->validate->getError());
            }
            ##获取数据
            if(!$this->matterCateModel->edit()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##分类信息
            $info = $this->matterCateModel->info();

            if(!$info)
            {
                $this->error('分类信息不存在或已删除');
            }
            $info = $info->toArray();
            $info['status'] = $info['status']['value'];
            $info['level_1'] = '';
            $info['level_2'] = '';
            $cateList = $this->matterCateModel->cateLists();
            return $this->fetch('edit_cate', compact('info','cateList'));
        }
    }

}