<?php


namespace app\store\model;

use app\common\model\GoodsSuggestion as GoodsSuggestionModel;
use app\store\validate\GoodsSuggestionValid;
use think\db\Query;
use think\Exception;

class GoodsSuggestion extends GoodsSuggestionModel
{

    /**
     * 新增
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        $validate = new GoodsSuggestionValid();
        if(!$validate->scene('add')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterParams();
        ##增加套餐
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 编辑
     * @return bool
     * @throws Exception
     */
    public function edit(){
        ##验证
        $suggestion_id = input('post.suggestion_id',0,'intval');
        $validate = new GoodsSuggestionValid();
        $rule = [
            'title' => "require|unique:goods_suggestion,title,{$suggestion_id},suggestion_id"
        ];
        if(!$validate->scene('edit')->rule($rule)->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = $this->filterParams();
        ##修改套餐
        $res = $this->update($data, compact('suggestion_id'));
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 整理参数
     * @return array
     */
    public function filterParams(){
        $params = [
            'title' => input('post.title','','str_filter'),
            'sort' => input('post.sort',1,'intval'),
            'goods_id' => input('post.goods_id',0,'intval'),
            'goods_sku_id' => input('post.goods_sku_id',0,'intval'),
            'num' => input('post.num',0,'intval'),
            'status' => input('post.status',1,'intval'),
            'image_id' => input('post.image_id',0,'intval'),
            'description' => input('post.description','','str_filter'),
            'min_cycle' => input('post.min_cycle',1,'intval'),
            'max_cycle' => input('post.max_cycle',1,'intval'),
            'min_bmi' => input('post.min_bmi',1,'floatval'),
            'max_bmi' => input('post.max_bmi',1,'floatval'),
        ];
        return $params;
    }

    /**
     * 修改字段
     * @return bool
     * @throws Exception
     */
    public function editField(){
        ##验证
        $suggestion_id = input('post.suggestion_id',0,'intval');
        $validate = new GoodsSuggestionValid();
        if(!$validate->scene('edit_field')->check(input()))throw new Exception($validate->getError());
        ##参数
        $field = input('post.field','','str_filter');
        $value = input('post.value','','str_filter');
        $allow_field = ['status', 'sort'];
        if(!in_array($field, $allow_field))throw new Exception('操作失败');
        ##操作
        $res = $this->where(compact('suggestion_id'))->setField($field, $value);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 套餐列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function suggestionList(){
        $list = $this
            ->with(
                [
                    'spec' => function(Query $query){
                        $query->field(['goods_sku_id', 'spec_sku_id', 'image_id'])->with(['image'=>function(Query $query){
                            $query->field(['file_id', 'file_name', 'storage', 'file_url']);
                        }]);
                    },
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'image' => function(Query $query){
                        $query->field(['file_id', 'file_name', 'storage', 'file_url']);
                    }
                ]
            )
            ->order('create_time','desc')
            ->paginate(20,false,['type' => 'Bootstrap',
            'var_page' => 'page',
            'path' => 'javascript:getSuggestionList([PAGE]);']);
        $total = $list->total();
        $page = $list->render();
        $list = $list->toArray()['data'];
        return compact('list','page','total');
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        $suggestion_id = input('post.suggestion_id',0,'intval');
        $res = $this->where(compact('suggestion_id'))->delete();
        if($res === false)throw new Exception('操作失败');
        return true;
    }

}