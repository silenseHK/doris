<?php


namespace app\store\model\college;

use app\common\model\college\Lecturer as LecturerModel;
use app\store\validate\LecturerValid;
use think\Exception;

class Lecturer extends LecturerModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new LecturerValid();
    }

    /**
     * 讲师列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function index(){
        $params = [
            'keywords' => input('keywords','','str_filter')
        ];
        $this->setWhere($params);
        $list = $this->with(['image'])->paginate(15,false,['query'=>\request()->request()]);
        return array_merge(compact('list'),$params);
    }

    /**
     * 设置查询条件
     * @param $params
     */
    public function setWhere($params){
        if($params['keywords']){
            $this->where(['name'=>['LIKE', "%{$params['keywords']}%"]]);
        }
    }

    /**
     * 讲师信息
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function info(){
        $lecturer_id = input('lecturer_id',0,'intval');
        $info = self::get(['lecturer_id'=>$lecturer_id], ['image']);
        if(!$info)throw new Exception('讲师信息不存在或已删除');
        return compact('info');
    }

    /**
     * 新增
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        if(!$this->valid->scene('add')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $data = $this->filterParams();
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
        if(!$this->valid->scene('edit')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $data = $this->filterParams();
//        print_r($data);die;
        $lecturer_id = input('post.lecturer_id',0,'intval');
        $res = $this->update($data, ['lecturer_id'=>$lecturer_id]);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 过滤参数
     * @return array
     */
    public function filterParams(){
        $data = [
            'name' => input('post.name','','str_filter'),
            'avatar' => input('post.avatar',0,'intval'),
            'desc'=> input('post.desc','','str_filter'),
            'label'=> input('post.label/a',''),
        ];
        if($data['label'])$data['label'] = implode(',',$data['label']);
        return $data;
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        $lecturer_id = input('post.lecturer_id',0,'intval');
        $res = self::destroy($lecturer_id);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 营养师列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getList(){
        return self::field(['lecturer_id', 'name'])->select()->toJson();
    }

}