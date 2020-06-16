<?php


namespace app\api\controller\college;


use app\api\controller\Controller;
use app\api\model\college\CollegeClass;
use app\api\model\college\CollegeWatchRecord;
use app\api\model\college\LecturerCollect;
use app\api\model\college\Lesson as LessonModel;
use app\api\model\college\LessonCollect;
use think\Exception;
use think\Request;

class Lesson extends Controller
{

    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new LessonModel();
    }

    /**
     * 课程分类列表
     * @return array
     */
    public function cateList(){
        try{
            return $this->renderSuccess($this->model->getCateList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 课程列表
     * @return array
     */
    public function lessonList(){
        try{
            return $this->renderSuccess($this->model->getLessonList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 系列课程详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lessonDetail(){
        $user = $this->getUser(false);
        try{
            return $this->renderSuccess($this->model->getLessonDetail($user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 课时详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function classDetail(){
        $user = $this->getUser(false);
        $model = new CollegeClass();
        try{
            return $this->renderSuccess($model->getDetail($user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 检查用户查看权限
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function checkAccess(){
        $user = $this->getUser();
        try{
            $res = $this->model->checkAccess($user);
            if($res){
                ##更新观看记录
                $this->model->updateWatchRecord($user);
                return $this->renderSuccess($res,'允许查看');
            }else{
                return $this->renderJson(2,'需输入私享码','');
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 收藏课程
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lessonCollect(){
        $user = $this->getUser();
        try{
            $model = new LessonCollect();
            return $model->lessonCollect($user) ? $this->renderSuccess() : $this->renderJson($model->getCode(), $model->getError());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 关注讲师
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lecturerCollect(){
        $user = $this->getUser();
        try{
            $model = new LecturerCollect();
            return $model->lecturerCollect($user) ? $this->renderSuccess() : $this->renderJson($model->getCode(), $model->getError());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户收藏课程列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function userCollectLessonList(){
        $user = $this->getUser();
        try{
            $model = new LessonCollect();
            $data = $model->collectList($user);
            if(!$data){
                return $this->renderJson($model->getCode(), $model->getError());
            }
            return $this->renderSuccess($data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户收藏导师列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function userCollectLecturerList(){
        $user = $this->getUser();
        try{
            $model = new LecturerCollect();
            $data = $model->collectList($user);
            if(!$data){
                return $this->renderJson($model->getCode(), $model->getError());
            }
            return $this->renderSuccess($data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 讲师课程列表
     * @return array
     */
    public function lecturerLessonList(){
        try{
            $model = new LessonModel();
            $data = $model->lecturerLessonList();
            if(!$data)
                return $this->renderJson($model->getCode(), $model->getError());
            return $this->renderSuccess($data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户观看记录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function watchRecord(){
        $user = $this->getUser();
        try{
            $model = new CollegeWatchRecord();
            $data = $model->watchRecord($user);
            if(!$data)
                return $this->renderJson($model->getCode(), $model->getError());
            return $this->renderSuccess($data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 搜索
     * @return array
     */
    public function search(){
        try{
            $model = new LessonModel();
            $data = $model->search();
            if(!$data)
                return $this->renderJson($model->getCode(), $model->getError());
            return $this->renderSuccess($data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 赚赚商学院入口参数
     * @return array
     */
    public function collegeEntrance(){
        $user = $this->getUser();
        $title = '内容中心';
        $url = request()->domain() . "/web_view/college/index.html";
        return $this->renderSuccess(compact('title','url'));
    }

}