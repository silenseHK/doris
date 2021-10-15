<?php


namespace app\api\controller;


use app\api\model\GoodsSuggestion;
use app\api\model\user\Fill;
use app\common\model\FoodGroup;
use think\Exception;
use app\api\model\Questionnaire as QuestionnaireModel;

class Questionnaire extends Controller
{

    protected $no = '202004220001';
    protected $no2 = '202007290001';

    /**
     * 问卷信息
     * @return array
     */
    public function questionnaire(){
        try{
            $user = $this->getUser();
            $model = new QuestionnaireModel();
            $questionnaire_no = input('get.questionnaire_no','','str_filter');
            if(!$questionnaire_no)throw new Exception('参数缺失');
            return $this->renderSuccess($model->info($questionnaire_no, $user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function newQuestionnaire(){
        $user = $this->getUser();
        try{
            $model = new QuestionnaireModel();
            $questionnaire_no = input('get.questionnaire_no','','str_filter');
            if(!$questionnaire_no)throw new Exception('参数缺失');
            return $this->renderSuccess($model->newInfo($questionnaire_no, $user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 提交问卷
     * @return array
     */
    public function submitQuestionnaire(){
        try{
            $user = $this->getUser();
            $model = new QuestionnaireModel();
            $res = $model->submitQuestionnaire($user);
            if(!is_array($res))throw new Exception($res);
            ##获取配餐图
            $img = '';
            if($res['bmi'] > 0){
                $img = FoodGroup::getUserImg($res['bmi'],1);
            }
            $res['img'] = $img;
            return $this->renderSuccess($res,'提交成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 新问卷提交接口
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function newSubmitQuestionnaire(){
        $user = $this->getUser();
        try{
            $model = new QuestionnaireModel();
            $res = $model->newSubmitQuestionnaire($user);
            if(!$res)throw new Exception($model->getError());
            return $this->renderSuccess($res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 健康问卷数据
     * @return array
     * @throws Exception
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function healthQuestionnaire($flag=0){
        $user = $this->getUser();
        $questionnaire_no = $this->no;
        if($flag)return $questionnaire_no;
        $model = new QuestionnaireModel();
        $info = $model->info($questionnaire_no, $user);
        $title = $info['title'];
        $url = request()->domain() . "/web_view/questionnaire/index.html?questionnaire_no={$questionnaire_no}";
        return $this->renderSuccess(compact('url','questionnaire_no','title'));
    }

    /**
     * 用户填写问卷列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function userFillList(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            return $this->renderSuccess($model->getUserFillList($user, $this->no));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 报告列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function myReportList(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            return $this->renderSuccess($model->myReportList($user, $this->no2));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 报告详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function reportDetail(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            return $this->renderSuccess($model->reportDetail());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 营养建议
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function healthAdvice(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            return $this->renderSuccess($model->healthAdvice());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 推荐套餐
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function buySuggestion(){
        $user = $this->getUser(false);
        try{
            $model = new GoodsSuggestion();
            return $this->renderSuccess($model->suggestionList($user));
        }catch(Exception $e){
            return $this->renderJson($e->getMessage());
        }
    }

    /**
     * 删除报告
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function delReport(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            if(!$model->delReport($user))throw new Exception($model->getError());
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 配餐图
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function getFoodGroup(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            if(!$data = $model->getFoodGroup($user)){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess($data,'');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}