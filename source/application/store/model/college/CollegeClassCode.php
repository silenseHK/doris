<?php


namespace app\store\model\college;

use app\common\model\college\CollegeClassCode as CollegeClassCodeModel;
use app\store\validate\CollegeClassValid;
use think\Exception;

class CollegeClassCode extends CollegeClassCodeModel
{

    /**
     * 添加私享码
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        $validate = new CollegeClassValid();
        if(!$validate->scene('add_code')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = [
            'class_id' => input('post.class_id',0,'intval'),
            'can_use_num' => input('post.can_use_num',0,'intval'),
            'start_time' => strtotime(input('post.start_time','','str_filter')),
            'expire_time' => strtotime(input('post.expire_time','','str_filter')),
        ];
        $class = CollegeClass::get(['class_id'=>$data['class_id']]);
        $data['lesson_id'] = $class['lesson_id'];
        $data['code_type'] = 10;
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 添加课程私享码
     * @return bool
     * @throws Exception
     */
    public function addCode(){
        ##验证
        $validate = new CollegeClassValid();
        if(!$validate->scene('add_lesson_code')->check(input()))throw new Exception($validate->getError());
        ##参数
        $data = [
            'lesson_id' => input('post.lesson_id',0,'intval'),
            'can_use_num' => input('post.can_use_num',0,'intval'),
            'start_time' => strtotime(input('post.start_time','','str_filter')),
            'expire_time' => strtotime(input('post.expire_time','','str_filter')),
        ];
        $data['code_type'] = 20;
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        ##验证
        $validate = new CollegeClassValid();
        if(!$validate->scene('del_code')->check(input()))throw new Exception($validate->getError());
        ##参数
        $code_id = input('post.code_id',0,'intval');
        $res = $this->where(['code_id'=>$code_id])->delete();
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 私享码列表
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCodeList(){
        ##验证
        $validate = new CollegeClassValid();
        if(!$validate->scene('code_list')->check(input()))throw new Exception($validate->getError());
        ##参数
        $class_id = input('post.class_id',0,'intval');
        ##操作
        $class = CollegeClass::get(['class_id'=>$class_id]);
        $list = $this->where(['class_id'=>$class_id])->whereOr(['lesson_id'=>$class['lesson_id'], 'code_type'=>20])->select();
        foreach($list as &$val){
            $val['start_time'] = date('Y-m-d H:i', $val['start_time']);
            $val['expire_time'] = date('Y-m-d H:i', $val['expire_time']);
        }
        return compact('list');
    }

    /**
     * 课程私享码列表
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLessonCodeList(){
        ##验证
        $validate = new CollegeClassValid();
        if(!$validate->scene('lesson_code_list')->check(input()))throw new Exception($validate->getError());
        ##参数
        $lesson_id = input('post.lesson_id',0,'intval');
        ##操作
        $list = $this->where(['lesson_id'=>$lesson_id, 'code_type'=>20])->select();
        foreach($list as &$val){
            $val['start_time'] = date('Y-m-d H:i', $val['start_time']);
            $val['expire_time'] = date('Y-m-d H:i', $val['expire_time']);
        }
        return compact('list');
    }

}