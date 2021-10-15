<?php


namespace app\agent\controller;


use app\agent\model\Agent;
use app\common\exception\BaseException;
use think\Controller;
use think\Exception;
use think\Request;
use app\common\library\aes\Aes;

class Base extends Controller
{

    const JSON_SUCCESS_STATUS = 200;
    const JSON_ERROR_STATUS = 100;

    /* @ver $wxapp_id 小程序id */
    protected $wxapp_id;

    public function __construct(Aes $aes, Request $request = null)
    {
        parent::__construct($request);
        if($request->isPost()){
            $encrypt = config('encrypt');
            if($encrypt){
                $params = json_decode($aes->aesDe($request->post('params')),true);
                Request::instance()->post($params);
            }else{
                $params = $request->post('data/a');
                Request::instance()->post($params);
            }
        }
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array|string
     */
    protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = [])
    {
        $encrypt = config('encrypt');
        $filterController = config('filter_controller');
        if($encrypt && !in_array(request()->controller(), $filterController)){
            $aes = new Aes();
            $data = $aes->aesEn(json_encode($data));
        }
        return compact('code', 'msg', 'data');
    }

    /**
     * 返回操作成功json
     * @param array $data
     * @param string|array $msg
     * @return array
     */
    protected function renderSuccess($data = [], $msg = 'success')
    {
        return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $data = [])
    {
        return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
    }

    /**
     * 获取代理
     * @param bool $force
     * @param string $token
     * @return Agent|bool|null
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function getAgent($force=true, $token=''){
        $token = $token? : Request::instance()->header('accessToken');;
        $user = Agent::get(compact('token'), ['user']);
        if(!$user && $force){
            $this->throwError('账号不存在，请重新登陆',-1);
            return false;
        }
        if($user['token_expire_time'] < time() && $force){
            $this->throwError('登陆过期，请重新登陆',-1);
            return false;
        }
        if($user['status'] != 1 && $force){
            $this->throwError('账号已冻结',100);
            return false;
        }
        return $user;
    }

    /**
     * 输出错误信息
     * @param int $code
     * @param $msg
     * @throws BaseException
     */
    protected function throwError($msg, $code = 0)
    {
        throw new BaseException(['code' => $code, 'msg' => $msg]);
    }

}