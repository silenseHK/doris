<?php

namespace app\api\controller;

use app\api\model\GoodsExperience;
use app\api\model\User as UserModel;
use app\common\library\wechat\WxSubMsg;
use app\common\model\UserGoodsStock;
use app\common\model\UserGoodsStockLog;
use app\common\service\kuaidi100\Energy;
use app\common\service\ManageReward;
use app\store\model\Wxapp as WxappModel;
use think\Cache;
use think\Db;
use think\Exception;
use think\Hook;
use app\api\model\user\Fill;
use think\Request;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{
    /**
     * 用户自动登录
     * @return array
     * @throws Exception
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $model = new UserModel;
        $userData = $model->login($this->request->post());
        return $this->renderSuccess(array_merge($userData, ['token' => $model->getToken()]));
    }

    /**
     * 168用户注册【停用】
     * @return array
     */
    public function register(){
        try{
            $model = new UserModel;
            return $this->renderSuccess([
                'user_id' => $model->doRegister($this->request->post()),
                'token' => $model->getToken()
            ]);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 当前用户详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser();
        return $this->renderSuccess(compact('userInfo'));
    }

    /**
     * 注册发送验证码
     * @return array
     */
    public function sendVerifyCode(){
        try{
            $model = new UserModel;
            $model->sendVerifyCode($this->request->post());
            return $this->renderSuccess('发送成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 绑定手机号
     * @return array
     */
    public function bindMobile(){
        try{
            $user = $this->getUser();
            $model = new UserModel;
            if(!$model->bindMobile($this->request->post(), $user))throw new Exception($model->getError());
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 绑定邀请人
     * @return array
     */
    public function bindInvitation(){
        try{
            $user = $this->getUser();
            $user->bindInvitation();
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function test(){
//        var_dump(Request::instance());die;
//        echo request()->domain();die;
//        $str = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
//        $arr = str_split($str);
//        shuffle($arr);
//        $new_str = implode("", $arr);
//        echo $new_str;
//        print_r($arr);die;
//        echo $res = createCode(7);
//        echo decode(8);
//        phpinfo();
//        $file = "../source/runtime/image/10001/";
//        if(file_exists($file))echo 'asd';
//        var_dump(file_get_contents('test.txt'));
//        $rewardModel = new ManageReward();
//        $rewardModel->countReward();
//        $list = $rewardModel->getNumData();
//        print_r($list);
//        $rewardModel->insertRewardLog();
//        var_dump($rewardModel->getError());
//        $notify = new Notify();
//        $notify->order();

//        $user = $this->getUser();
//        print_r($user);
//        $user = $this->getUser();
//        $config = WxappModel::getWxappCache();
//        $wxSubMsg = new WxSubMsg($config['app_id'], $config['app_secret']);
//        $res = $wxSubMsg->sendCommonMsg(5,$user);
//        $res = $wxSubMsg->sendMsg($user,['jet lee', '15983587777'],'register_success');
//        var_dump($res);echo $wxSubMsg->getError();
//        $point = Fill::countPointBMI(6);
//        print_r($point) ;
//        $params = [
//            'user_id' => 7
//        ];
//        Hook::listen('agent_instant_grade',$params);

//        $list = Cache::get('');
//        print_r($list);

//        $list = Db::name('user_fill')->field(['fill_id', 'user_id'])->select();
//        foreach($list as $item){
//            $group_user_id = \app\common\model\User::getGroupUserId($item['user_id']);
//            Db::name('user_fill')->where(['fill_id'=>$item['fill_id']])->setField('group_user_id',$group_user_id);
//        }

        ######  物流信息  ######
        //参数设置
        $post_data = array();
        $post_data["customer"] = '246697124CED07C190975319669E8B64';
        $key= 'bDfciruN248' ;
        $post_data["param"] = '{"com":"zhongtong","num":"640229783136"}';

        $url='http://poll.kuaidi100.com/poll/query.do';
        $post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        $data = str_replace("\"",'"',$result );
        $data = json_decode($data,true);
        print_r($data);

        ######  物流信息  ######

//        $user_id = input('get.user_id',0,'intval');
//        $user = UserModel::get($user_id);

        ### 导入体验装线下排行信息 ###
        die;

        $file = request()->file('file');
        if (!$file) {
            $this->renderJson(303, '缺少file');
        }
        $file = $file->getRealPath();
        //调用导入excel方法
        $insert_datas = $this->importExcel($file);
//        print_r($insert_datas);die;
        $is_fake = input('post.is_fake',0,'intval');
        $data = [];
        if(!empty($insert_datas)){
            foreach($insert_datas as $key => $insert_data){
                if($key <= 1)continue;
                $user_id = 0;
                if($insert_data[4] == '是'){
                    $user_data = UserModel::where(['mobile'=>$insert_data[5]])->field(['user_id'])->find();
                    if($user_data)$user_id = $user_data['user_id'];
                }
                $per_data = [
                    'user_id' => $user_id,
                    'goods_id' => 37,
                    'is_online' => 0,
                    'is_fake' => $is_fake
                ];
                if($user_id){
                    $rebate_info = \app\common\model\User::getExperienceRebate($user_id, 1);
                    if(!empty($rebate_info)){
                        $per_data['first_user_id'] = isset($rebate_info[0])?$rebate_info[0]['user_id']:0;
                        $per_data['second_user_id'] = isset($rebate_info[1])?$rebate_info[1]['user_id']:0;
                    }
                }else{
                    $per_data['first_user_id'] = $insert_data[2];
                }

                $data[] = $per_data;
            }
            // print_r($data);die;
            $model = new GoodsExperience();
            $model->saveAll($data);
        }



    }

    public function test2(){
        $file = request()->file('file');
        if (!$file) {
            $this->renderJson(303, '缺少file');
        }
        $file = $file->getRealPath();
        //调用导入excel方法
        $insert_datas = $this->importExcel($file);
        print_r($insert_datas);die;

    }

    /**
     * 迁移代理
     * @return array
     */
    public function transferAgent(){
        try{
            $model = new UserModel();
            $res = $model->transferAgent();
            if(!$res)throw new Exception($model->getError());
            return $this->renderSuccess('迁移成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function transferTeam(){
//        $file = request()->file('file');
//        if (!$file) {
//            return $this->renderJson(303, '缺少file');
//        }
//        $file = $file->getRealPath();
        $file = 'temp/test7.txt';
        //调用导入excel方法
        $data = file_get_contents($file);
        $data = json_decode($data,true);
        try{
            if(!$data)throw new Exception('文件数据格式错误');
            $model = new UserModel();
            if(!$model->transferTeam($data))throw new Exception($model->getError());
            return $this->renderSuccess('迁移成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function filterTransfer(){
//        $file = request()->file('file');
//        if (!$file) {
//            return $this->renderJson(303, '缺少file');
//        }
//        $file = $file->getRealPath();
        //调用导入excel方法
        $file = 'temp/test7.txt';
        $data = file_get_contents($file);
        $data = json_decode($data,true);
        $model = new UserModel();
        $model->filterTransfer($data);
    }

    public function filterTransfer2(){
//        $file = request()->file('file');
//        if (!$file) {
//            return $this->renderJson(303, '缺少file');
//        }
//        $file = $file->getRealPath();
        //调用导入excel方法
        $file = 'users/stock-arr.text';
        $data = file_get_contents($file);
        $data = json_decode($data,true);
        $nums = array_sum(array_column($data,'stock'));
        echo $nums;die;
        $model = new UserModel();
        $model->filterTransfer2($data);
    }

    public function markTransferStock(){
        $model = new UserGoodsStock();
        $list = $model->alias('ugs')
            ->join('user u','u.user_id = ugs.user_id','LEFT')
            ->where(['u.is_transfer'=>1, 'u.user_id'=>['EGT', 14610]])
            ->field(['ugs.id', 'ugs.stock', 'ugs.history_stock', 'u.is_transfer', 'ugs.user_id'])
            ->select()->toArray();
        $bug_arr = [];
        foreach($list as $k => $v){
            if($v['history_stock'] > $v['stock'])$bug_arr[] = $v;
            $model->where(['id'=>$v['id']])->update(['transfer_stock'=>$v['history_stock'], 'transfer_stock_history'=>$v['history_stock']]);
        }
        print_r($bug_arr);
    }

    public function importExcel($file){
        include(__DIR__.'/../../common/library/phpExcel/PHPExcel.php');
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '16MB');
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);//文件缓存
        //当前空间不用\，非当前空间要加\
        $PHPExcel = new \PHPExcel();//创建一个excel对象
        $PHPReader = new \PHPExcel_Reader_Excel2007(); //建立reader对象，excel—2007以后格式
        if (!$PHPReader->canRead($file)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();//建立reader对象，excel—2007以前格式
            if (!$PHPReader->canRead($file)) {
                $msg = '不是excel格式文件';
                $this->renderJson(303,$msg);
            }
        }
        $PHPExcel = $PHPReader->load($file); //加载excel对象
        $sheet = $PHPExcel->getSheet(0); //获取指定的sheet表
        $rows = $sheet->getHighestRow();//行数
        $cols = $sheet->getHighestColumn();//列数

        $data = array();
        for ($i = 1; $i <= $rows; $i++){ //行数是以第1行开始
            $count = 0;
            for ($j = 'A'; $j <= $cols; $j++) { //列数是以A列开始
                $value = $sheet->getCell($j . $i)->getValue();
                if ($value) {
                    $data[$i - 1][$count] = (string)$sheet->getCell($j . $i)->getValue();
                    $count += 1;
                }
            }
        }
        return $data;
    }

    public function transferTeams(){
//        for($i=1;$i<500;$i++){
//            $res = $this->relFreezeUser($i);
//            if(!$res)break;
//        }
//        echo 'success';
        $page = input('page',1,'intval');
        $res = $this->didTransferTeams($page);
        var_dump($res);
    }

    public function didTransferTeams($page){
        $file = "users/users-all-{$page}.text";
        $users = file_get_contents($file);
        $users = json_decode($users,true);
        if($users){
            $model = new UserModel();
            try{
                $data = [];
                Db::startTrans();
                foreach($users as $k => $it){
                    $data[] =  $model->didTransferTeams($it);
                }
                $model->isUpdate(false)->saveAll($data);
                Db::commit();
                return true;
            }catch(Exception $e){
                Db::rollback();
                echo $e->getMessage();
                return false;
            }
        }else{
            return false;
        }
    }

    public function filterData(){
        for($i=1;$i<500;$i++){
            $res = $this->didFilterData($i);
            echo $i;
        }
        echo 'success';
    }

    public function filterData2(){
        $i = input('get.page',1,'intval');
        $res = $this->didFilterData($i);
        echo $res;
    }

    public function didFilterData($page){
        $file = "users/users-all-{$page}.text";
        $users = file_get_contents($file);
        $users = json_decode($users,true);

        $file_phone_arr = 'users/phone-arr.text';
        if(!file_exists($file_phone_arr))file_put_contents($file_phone_arr,'');

        $repeat_phone_arr = file_get_contents($file_phone_arr);
        $repeat_phone_arr = json_decode($repeat_phone_arr,true);
        $repeat_phone_arr = $repeat_phone_arr?:[];

        $file_repeat_phone_array = 'users/repeat-phone-array.text';
        if(!file_exists($file_repeat_phone_array))file_put_contents($file_repeat_phone_array,'');

        $repeat_phone_array = file_get_contents($file_repeat_phone_array);
        $repeat_phone_array = json_decode($repeat_phone_array,true);
        $repeat_phone_array = $repeat_phone_array?:[];

        $file_money_arr = 'users/money-arr.text';
        if(!file_exists($file_money_arr))file_put_contents($file_money_arr,'');

        $money_arr = file_get_contents($file_money_arr);
        $money_arr = json_decode($money_arr,true);
        $money_arr = $money_arr?:[];

        $file_stock_arr = 'users/stock-arr.text';
        if(!file_exists($file_stock_arr))file_put_contents($file_stock_arr,'');

        $stock_arr = file_get_contents($file_stock_arr);
        $stock_arr = json_decode($stock_arr,true);
        $stock_arr = $stock_arr?:[];

        $file_more_phone_arr = "users/more-phone-arr.text";
        if(!file_exists($file_more_phone_arr))file_put_contents($file_more_phone_arr,'');

        $more_phone_arr = file_get_contents($file_more_phone_arr);
        $more_phone_arr = json_decode($more_phone_arr,true);
        $more_phone_arr = $more_phone_arr?:[];

//        print_r($users);die;
        if($users){
            foreach($users as $item){
                if(in_array($item['phone'], $repeat_phone_arr)){
                    $item['page'] = $page;
                    $repeat_phone_array[] = $item;
                }else{
                    $repeat_phone_arr[] = $item['phone'];
                }
                if($item['phone']){
                    if(!isset($more_phone_arr[$item['phone']]))$more_phone_arr[$item['phone']] = [];
                    $more_phone_arr[$item['phone']][] = ['user_id'=>$item['id'], 'stock'=>$item['stock'], 'level'=>$item['level'], 'money'=>$item['money'], 'page'=>$page];
                }
                if($item['stock'] != 0){
                    $stock_arr[] = $item;
                }
                if($item['money'] >0){
                    $money_arr[] = $item;
                }
            }
            file_put_contents($file_phone_arr,json_encode($repeat_phone_arr));
            file_put_contents($file_repeat_phone_array,json_encode($repeat_phone_array));
            file_put_contents($file_money_arr,json_encode($money_arr));
            file_put_contents($file_stock_arr,json_encode($stock_arr));
            file_put_contents($file_more_phone_arr,json_encode($more_phone_arr));
            $status = 1;

        }else{
            $status = 0;
        }
        return $status;
    }

    public function repeatPhone(){
//        $file = "users/phone-arr.text";
//        $users = file_get_contents($file);
//        $users = json_decode($users,true);
//        echo count($users);
//        $page = 1;
        $page = input('page',1,'intval');
        $file_more_phone_arr = "users/money-arr.text";
        $users = file_get_contents($file_more_phone_arr);
        $users = json_decode($users,true);
        $ids = array_column($users,'id');
        $ids = implode(',',$ids);
        echo $ids;die;
        print_r($ids);die;
        $data = [];
        foreach($users as $k => $item){
            if(count($item) > 1)$data[$k] = $item;
        }
//        echo count($phone);die;
        print_r($data);die;
    }

    public function stockData(){
        $model = new UserModel();
        $stockModel = new UserGoodsStock();
        $list = $stockModel->alias('s')
            ->join('user u','s.user_id = u.user_id','LEFT')
            ->where(
                [
                    's.user_id' => ['GT', 14590],
//                    'u.relation' => ['LIKE', "%-17312-%"],
                    'u.is_delete' => 1
                ]
            )
            ->field(['s.id','s.user_id', 's.stock'])
            ->select()->toArray();

        echo $stockModel->getLastSql();die;

        print_r($list);die;


//        echo array_sum(array_column($list,'stock'));

        $user_ids = array_column($list,'user_id');

        $ids = array_column($list,'id');

        print_r($ids);die;

        $stockModel->where(['id'=>['IN', $ids]])->delete();

        $logModel = new UserGoodsStockLog();
        $logModel->where(['user_id'=>['IN', $user_ids]])->delete();

        echo 'success';

//        print_r($list);
    }

    public function transferStockRecord(){
        $list = db('user_goods_stock')->alias('ugs')
            ->join('user u','u.user_id = ugs.user_id','LEFT')
            ->where(['ugs.user_id'=>['IN', [32053, 32054, 32055, 32059, 32077, 32206, 32310, 37939, 38014]], 'transfer_stock_history'=>['GT', 0]])
            ->field(['ugs.user_id', 'u.ws_openid', 'ugs.transfer_stock_history', 'u.mobile'])
            ->select()
            ->toArray();
        $list = array_column($list,null,'ws_openid');
//        print_r($list);die;
//        $file = 'users/stock-arr.text';
        $file = 'temp/test7.txt';

        $users = file_get_contents($file);
        $users = json_decode($users,true);
//        $users = array_column($users, null, 'openid');
        $array = [];
        $array = $this->drawArray($users,$array);
        $array = array_column($array,null,'openid');
        foreach($list as $key => &$val){
            if(isset($array[$key])){
                $val['old_user_id'] = $array[$key]['id'];
                $val['money'] = $array[$key]['money'];
                $val['level'] = $array[$key]['level'];
            }else{
                echo "\r\n aa";
            }
        }
        $model = new UserModel();
        $model->transferStockRecord($list);
//        print_r($list);
    }

    public function drawArray($array, $data){
        foreach($array as $key => $val){
            $it = $val;
            unset($it['child']);
            $data[] = $it;
            if($val['child']){$data = $this->drawArray($val['child'], $data);}
        }
        return $data;
    }

    public function countStock(){
//        $stock = db('user_goods_stock')->where(['transfer_stock_history'=>['GT', 0]])->sum('transfer_stock_history');
//        $stock = db('user_goods_stock')->where(['transfer_stock_history'=>['GT', 0]])->sum('transfer_stock_history');
//        $stock = db('user_goods_stock')->where(['transfer_stock_history'=>['GT', 0], 'user_id'=>['GT', 14590]])->sum('transfer_stock_history');
        $stock = db('user_goods_stock')->where(['transfer_stock_history'=>['GT', 0], 'user_id'=>['BETWEEN', [14391, 14590]]])->sum('transfer_stock_history');
        echo $stock;
    }

    public function getPrintImg(){
        $order = [
            'receive_user' => 'WJWL666',
            'receive_mobile' => '15983587793',
            'receive_address' => '成都市青羊区铜丝街5号院',
            'send_user' => '胡哲哲',
            'send_mobile' => '15983587793',
            'send_address' => '成都市武侯区天府金融大厦A座1701',
            'express_code' => 'yunda',
            'goods_full_name' => '168',
            'goods_num' => '1',
            'remark' => 'hahah',
        ];
        $expressPrinter = new Energy('getPrintImg', $order);
        $expressPrinter->task();
    }

    public function printOrder(){
        $order = [
            'order_no' => '2020071153994857',
            'express_code' => 'yunda'
        ];
        $expressPrinter = new Energy('printOrder', $order);
        $expressPrinter->task();
    }

    public function eOrder(){
        $order = [
            'receive_user' => 'WJWL666',
            'receive_mobile' => '15983587793',
            'receive_address' => '成都市青羊区铜丝街5号院',
            'send_user' => '胡哲哲',
            'send_mobile' => '15983587793',
            'send_address' => '成都市武侯区天府金融大厦A座1701',
            'express_code' => 'yunda',
            'goods_full_name' => '168',
            'goods_num' => '1',
            'remark' => 'hahah',
        ];
        $expressPrinter = new Energy('eOrder', $order);
        $res = $expressPrinter->task();
        if(!$res)echo $expressPrinter->getError();
    }

    public function printOld(){
        $order = [
            'task_id' => '12332112312'
        ];
        $expressPrinter = new Energy('printOld', $order);
        $res = $expressPrinter->task();
        if(!$res)echo $expressPrinter->getError();
    }

    public function printCallBack(){
        $data = file_get_contents('php://input');
        print_r($data);
        file_put_contents('test.txt', $data,FILE_APPEND);
    }

}
