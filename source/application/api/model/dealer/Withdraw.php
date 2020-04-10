<?php

namespace app\api\model\dealer;

use app\api\model\user\BankCard;
use app\api\model\user\GoodsStock;
use app\common\exception\BaseException;
use app\common\model\dealer\Withdraw as WithdrawModel;
use app\api\model\User as UserModel;

/**
 * 分销商提现明细模型
 * Class Withdraw
 * @package app\api\model\dealer
 */
class Withdraw extends WithdrawModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    /**
     * 获取分销商提现明细
     * @param $user_id
     * @param int $apply_status
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $apply_status = -1)
    {
        $this->where('user_id', '=', $user_id);
        $apply_status > -1 && $this->where('apply_status', '=', $apply_status);
        return $this->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 提交申请
     * @param UserModel $dealer
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function submit($dealer, $data)
    {
        // 数据验证
        $this->validation($dealer, $data);
        // 新增申请记录
        $this->allowField(true)->save(array_merge($data, [
            'user_id' => $dealer['user_id'],
            'apply_status' => 10,
            'wxapp_id' => self::$wxapp_id,
        ]));
        // 冻结用户资金
        return $dealer->freezeMoney($data['money']);
    }

    /**
     * 数据验证
     * @param $dealer
     * @param $data
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function validation($dealer, &$data)
    {
        //判断[云库存]是否有负库存
        if(!GoodsStock::checkAllStock($dealer['user_id'])){
            throw new BaseException(['msg' => '有商品库存为负,请先补充库存']);
        }
        // 结算设置
        $settlement = Setting::getItem('settlement');
        // 最低提现佣金
        if ($data['money'] <= 0) {
            throw new BaseException(['msg' => '提现金额不正确']);
        }
        if ($dealer['balance'] <= 0) {
            throw new BaseException(['msg' => '当前用户没有可提现佣金']);
        }
        if ($data['money'] > $dealer['balance']) {
            throw new BaseException(['msg' => '提现金额不能大于可提现佣金']);
        }
        if ($data['money'] < $settlement['min_money']) {
            throw new BaseException(['msg' => '最低提现金额为' . $settlement['min_money']]);
        }
        if (!in_array($data['pay_type'], $settlement['pay_type'])) {
            throw new BaseException(['msg' => '提现方式不正确']);
        }
        if ($data['pay_type'] == '20') { ##支付宝提现
            if (empty($data['alipay_name']) || empty($data['alipay_account'])) {
                throw new BaseException(['msg' => '请补全提现信息']);
            }
        } elseif ($data['pay_type'] == '30') { ##银行卡提现
//            if (empty($data['bank_name']) || empty($data['bank_account']) || empty($data['bank_card'])) {
//                throw new BaseException(['msg' => '请补全提现信息']);
//            }
            if(empty($data['card_id'])){
                throw new BaseException(['msg' => '请选择银行卡']);
            }
            ##获取银行卡信息
            $bankInfo = BankCard::getInfo($dealer['user_id'], $data['card_id']);
            if(!$bankInfo)throw new BaseException(['msg' => '银行卡信息错误']);
            $data['bank_name'] = $bankInfo['bank']['bank_name'];
            $data['bank_account'] = $bankInfo['card_account'];
            $data['bank_card'] = $bankInfo['card_number'];
        }
    }

    /**
     * 待提现的金额
     * @param $user_id
     * @return float|int
     */
    public static function getWaitWithdrawMoney($user_id){
        $money = self::where(
                [
                    'user_id' => $user_id,
                    'apply_status' => ['IN', [10, 20]]
                ]
            )
            ->sum('money');
        return $money;
    }

    /**
     * 已提现金额
     * @param $user_id
     * @return float|int
     */
    public static function getDidWithDrawMoney($user_id){
        $money = self::where(
                [
                    'user_id' => $user_id,
                    'apply_status' => 40
                ]
            )
            ->sum('money');
        return $money;
    }

}