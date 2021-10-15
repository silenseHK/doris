<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 9:26
 */

namespace app\api\controller\business;


use app\api\controller\Controller;
use app\common\exception\BaseException;
use think\Cache;
use think\Request;

class Base extends Controller
{

    protected $user_id;

    public function __construct()
    {
        parent::__construct();
        ##验证用户token
        if(!$token = request()->header('token')){
            $this->throwError('请先登录',100);
        }
        ##判断token
        if(!$user = Cache::get($token)){
            $this->throwError('请先登录',100);
        }
        if($user['expire_time'] <= time()){
            $this->throwError('请重新登录',100);
        }
        $this->user_id = $user['user_id'];
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
    protected function renderError($msg = 'error', $code=self::JSON_ERROR_STATUS, $data = [])
    {
        return $this->renderJson($code, $msg, $data);
    }

}