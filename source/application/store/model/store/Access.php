<?php

namespace app\store\model\store;

use app\common\model\store\Access as AccessModel;

/**
 * 商家用户权限模型
 * Class Access
 * @package app\store\model\store
 */
class Access extends AccessModel
{
    /**
     * 获取权限列表 jstree格式
     * @param int $role_id 当前角色id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getJsTree($role_id = null)
    {
        $store_user_id = session('yoshop_store.user')['store_user_id'];
        $is_super = User::where(['store_user_id'=>$store_user_id])->value('is_super');
        $accessIds = is_null($role_id) ? [] : RoleAccess::getAccessIds($role_id);
        $jsTree = [];
        foreach ($this->getAll($is_super) as $item) {
            $jsTree[] = [
                'id' => $item['access_id'],
                'parent' => $item['parent_id'] > 0 ? $item['parent_id'] : '#',
                'text' => $item['name'],
                'state' => [
                    'selected' => (in_array($item['access_id'], $accessIds) && !$this->hasChildren($item['access_id']))
                ]
            ];
        }
        return json_encode($jsTree);
    }

    /**
     * 是否存在子集
     * @param $access_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function hasChildren($access_id)
    {
        foreach (self::getAll(1) as $item) {
            if ($item['parent_id'] == $access_id)
                return true;
        }
        return false;
    }

}