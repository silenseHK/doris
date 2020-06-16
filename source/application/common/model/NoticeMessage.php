<?php


namespace app\common\model;


use think\Exception;
use traits\model\SoftDelete;

class NoticeMessage extends BaseModel
{

    protected $name = 'notice_message';

    protected $updateTime = false;

    protected $insert = ['effect_time'];

    use SoftDelete;
    protected $deleteTime = 'delete_time';

    protected $hidden = ['delete_time'];

    /**
     * 生效时间
     * @param $value
     * @param $data
     * @return int
     */
    public function setEffectTimeAttr($value, $data){
        return isset($data['effect_time'])? $data['effect_time'] : time();
    }

    /**
     * 消息类型
     * @var array
     */
    protected $type = [
        '10' => [
            'value' => 10,
            'text' => '系统消息'
        ],
        '20' => [
            'value' => 20,
            'text' => '余额变动'
        ],
        '30' => [
            'value' => 30,
            'text' => '库存变动'
        ],
        '40' => [
            'value' => 40,
            'text' => '审核通知'
        ],
        '50' => [
            'value' => 50,
            'text' => '团队消息'
        ],
        '60' => [
            'value' => 60,
            'text' => '订单通知'
        ],
    ];

    /**
     * 初始化消息类型
     * @param $value
     * @return mixed
     */
    public function getTypeAttr($value){
        return $this->type[$value];
    }

    /**
     * 初始化detail
     * @param $value
     * @return string
     */
    public function getDetailAttr($value){
        return htmlspecialchars_decode($value);
    }

    /**
     * 插入提现结果通知
     * @param $order
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function addCashResultMsg($order){
        $result_msg = $order['apply_status'] == 20 ? "审核通过" : "驳回，驳回理由:{$order['reject_reason']}";
        $params = [
            'id' => $order['id']
        ];
        $data = [
            'type' => 40,
            'title' => '提现审核结果',
            'content' => "您的提现申请已{$result_msg}",
            'url' => 'pages/dealer/withdraw/list/list',
            'params' => json_encode($params)
        ];
        return $this->addWithBind($data, $order['user_id']);
    }

    /**
     * 新伙伴加入通知
     * @param $user
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function newChildRegisterMsg($user){
        $data = [
            'type' => 50,
            'title' => '新伙伴加入',
            'content' => "您的新伙伴【{$user['nickName']}】成功加入团队",
            'url' => 'pages/dealer/team/team',
            'params' => ""
        ];
        return $this->addWithBind($data, $user['invitation_user_id']);
    }

    /**
     * 用户余额变动通知
     * @param $param ['order_no', 'money', 'user_id']
     * @param $type
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function balanceChangeMsg($param, $type){
        $content_txt = $url = '';
        switch($type){
            case 10:
                $content_txt = "出货货款到账";
                $url = 'pages/dealer/money/sale';
                break;
            case 20:
                $content_txt = "推荐收益到账";
                $url = "pages/dealer/money/rebate";
                break;
            case 30:
                $content_txt = "支出推荐奖励";
                $url = 'pages/user/index';
                break;
            default:
                break;
        }
        $params = [
            'order_no' => $param['order_no']
        ];
        $data = [
            'type' => 20,
            'title' => '余额变动',
            'content' => "{$content_txt}，金额{$param['money']}元",
            'url' => $url,
            'params' => json_encode($params)
        ];
        return $this->addWithBind($data, $param['user_id']);
    }

    /**
     * 用户库存变动通知
     * @param $param ['goods_name', 'diff_num', 'order_id', 'cur_num', 'user_id']
     * @param $type
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function stockChangeMsg($param, $type){
        $content_txt = $type == 10 ? "补充商品【{$param['goods_name']}】库存数量{$param['diff_num']}" : "商品【{$param['goods_name']}】出货数量{$param['diff_num']}";
        $content_txt .= "，当前剩余库存数量{$param['cur_num']}";
        $url = $type == 10 ? "pages/order/detail" : "pages/dealer/sale/record";
        $params = [
            'order_id' => $param['order_id']
        ];
        $data = [
            'type' => 30,
            'title' => '库存变动',
            'content' => $content_txt,
            'url' => $url,
            'params' => json_encode($params)
        ];
        return $this->addWithBind($data, $param['user_id']);
    }

    public function orderMsg($param, $type){
        ##10.订单取消 20.订单发货 30.订单收货
    }

    /**
     * 添加信息 绑定用户
     * @param $data
     * @param $user_id
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function addWithBind($data, $user_id){
        $this->startTrans();
        try{
            $res = $this->isUpdate(false)->save($data);
            if($res === false)throw new Exception('通知消息创建失败');
            $message_id = $this->getLastInsID();
            ##增加用户绑定消息
            $res = NoticeMessageUser::bindMsg($user_id, $message_id);
            if($res === false)throw new Exception('通知消息绑定失败');
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 添加系统消息
     * @param $data
     * @return false|int
     */
    public function addSystemMsg($data){
        return $this->isUpdate(false)->save(array_merge($data, ['type'=>10]));
    }

}