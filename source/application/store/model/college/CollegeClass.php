<?php


namespace app\store\model\college;

use app\common\model\college\CollegeClass as CollegeClassModel;
use think\db\Query;
use app\store\validate\CollegeClassValid;
use think\Exception;

class CollegeClass extends CollegeClassModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new CollegeClassValid();
    }

    /**
     * 课时信息
     * @param $lesson_id
     * @return CollegeClass|null
     * @throws \think\exception\DbException
     */
    public static function getSoloClassInfo($lesson_id){
        return self::get(['lesson_id'=>$lesson_id], ['liveRoom']);
    }

    /**
     * 首页信息
     * @return array
     * @throws \think\exception\DbException
     */
    public function index(){
        $params = [
            'lesson_id' => input('lesson_id',0,'intval'),
            'keywords' => input('keywords','','str_filter'),
        ];
        $this->setWhere($params);
        $list = $this
            ->with(
                [
                    'image' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name', 'file_url']);
                    },
                    'liveRoom' => function(Query $query){
                        $query->field(['id', 'room_name']);
                    },
                    'lesson' => function(Query $query){
                        $query->field(['lesson_id', 'title', 'lesson_type', 'lesson_size', 'is_private']);
                    }
                ]
            )
            ->field(['class_id', 'title', 'cover', 'lesson_id', 'desc', '`desc` as filter_desc', 'video_url', 'live_room_id', 'create_time', 'is_free', 'sort', 'status'])
            ->order('sort','asc')
            ->paginate(15,false,['query'=>\request()->request()]);
        $list2 = $list->toArray();
        $data = $list2['data'];
        return array_merge(compact('list','data'), $params);
    }

    /**
     * 设置筛选条件
     * @param $params
     */
    public function setWhere($params){
        $where = [
            'lesson_id' => $params['lesson_id']
        ];
        if($params['keywords']){
            $where['title'] = ['LIKE', "%{$params['keywords']}%"];
        }
        $this->where($where);
    }

    /**
     * 添加
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        if(!$this->valid->scene('add')->check(input()))throw new Exception($this->valid->getError());
        $data = $this->filterData();
        ##添加
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
        $class_id = input('post.class_id',0,'intval');
        $data = $this->filterData();
        ##编辑
        $res = $this->save($data, ['class_id'=>$class_id]);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 过滤数据
     * @return array
     */
    protected function filterData(){
        return [
            'lesson_id' => input('post.lesson_id',0,'intval'),
            'cover' => input('post.img_id',0,'intval'),
            'is_free' => input('post.is_free',0,'intval'),
            'status' => input('post.status',0,'intval'),
            'sort' => input('post.sort',0,'intval'),
            'title' => input('post.title','','str_filter'),
            'desc' => input('post.desc','','str_filter'),
            'video_url' => input('post.video_url','','str_filter'),
            'content' => input('post.content','','htmlspecialchars'),
        ];
    }

    /**
     * 编辑信息
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function editInfo(){
        ##验证
        if(!$this->valid->scene('edit_info')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $class_id = input('class_id',0,'intval');
        $info = self::get(['class_id'=>$class_id], ['image']);
        if(!$info)throw new Exception('课时信息不存在或已删除');
        return compact('info');
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        $class_id = input('class_id',0,'intval');
        $res = self::destroy($class_id);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 修改字段
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function changeField(){
        ##验证
        if(!$this->valid->scene('change_field')->check(input()))throw new Exception($this->valid->getError());
        $class_id = input('post.class_id',0,'intval');
        $field = input('post.field','','str_filter');
        $value = input('post.value','','str_filter');
        $data = self::get($class_id);
        if(!$data)throw new Exception('数据不存在或已删除');
        if(!isset($data[$field]))throw new Exception('字段不存在');
        if(!$value){
            $value = ($data[$field] + 1) % 2;
        }
        $res = $this->where(['class_id'=>$class_id])->setField($field, $value);
        if($res === false)throw new Exception('操作失败');
        return $value;
    }

}