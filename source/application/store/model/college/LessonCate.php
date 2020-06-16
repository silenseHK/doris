<?php


namespace app\store\model\college;

use app\common\model\college\LessonCate as LessonCateModel;
use app\store\validate\LessonValid;
use think\Exception;

class LessonCate extends LessonCateModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new LessonValid();
    }

    /**
     * 分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $list = $this->field(['lesson_cate_id', 'title', 'sort', 'is_show', 'create_time', 'pid'])->select()->toArray();
        $list = getTree($list, 0, 0);
        return compact('list');
    }

    /**
     * 获取一级分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getParentCateList(){
        return $this->where(['level'=>1])->order('sort','asc')->field(['lesson_cate_id', 'title'])->select();
    }

    /**
     * 分类信息
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo(){
        $lesson_cate_id = input('lesson_cate_id',0,'intval');
        $info = self::get(['lesson_cate_id'=>$lesson_cate_id]);
        if(!$info)throw new Exception('分类数据不存在或已删除');
        $cate_list = $this->getParentCateList();
        return compact('info','cate_list');
    }

    /**
     * 添加
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        if(!$this->valid->scene('cate_add')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $data = $this->filterParams();
        $check = $this->where(['title'=>$data['title'],'pid'=>$data['pid']])->count();
        if($check)throw new Exception('分类名称已存在');
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
        $lesson_cate_id = input('post.lesson_cate_id',0,'intval');
        ##验证
        if(!$this->valid->scene('cate_edit')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $data = $this->filterParams();
        $check = $this->where(['title'=>$data['title'],'pid'=>$data['pid'],'lesson_cate_id'=>['NEQ',$lesson_cate_id]])->count();
        if($check)throw new Exception('分类名称已存在');
        $data['level'] = $this->getLevel($data['pid']);
        $res = $this->update($data, ['lesson_cate_id'=>$lesson_cate_id]);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 过滤参数
     * @return array
     */
    public function filterParams(){
        return [
            'title' => input('post.title','','str_filter'),
            'pid' => input('post.pid',0,'intval'),
            'is_show' => input('post.is_show',0,'intval'),
            'sort' => input('post.sort',9999,'intval')
        ];
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        $lesson_cate_id = input('post.lesson_cate_id',0,'intval');
        $res = self::destroy($lesson_cate_id);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCateList(){
        return self::where(['level'=>1])->field(['lesson_cate_id', 'title', 'lesson_cate_id as child'])->order('sort','asc')->select()->toArray();
    }

    /**
     * 设置子级分类
     * @param $value
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getChildAttr($value, $data){
        return self::where(['pid'=>$data['lesson_cate_id'], 'level'=>2])->field(['lesson_cate_id', 'title'])->select()->toArray();
    }

    /**
     * 获取父级分类id
     * @param $cate_id
     * @return mixed
     */
    public static function GetParentCateId($cate_id){
        $pid = self::where(['lesson_cate_id'=>$cate_id])->value('pid');
        $cate_info = self::where(['lesson_cate_id'=>$pid])->field(['lesson_cate_id', 'title'])->find()->toArray();
        return $cate_info;
    }

}