<?php


namespace app\common\model;


use app\common\enum\VerifyCode as VerifyCodeEnum;
use app\common\library\sms\Driver as SmsDriver;
use app\common\model\Setting as SettingModel;
use think\Exception;

class MobileVerifyCode extends BaseModel
{

    protected $name = 'verify';

    protected $updateTime = false;

    protected $insert = ['expire_time'];

    /**
     * 获取器 设置过期时间
     * @param $value
     * @param $data
     * @return int|mixed
     */
    public function setExpireTimeAttr($value, $data){
        return time() + VerifyCodeEnum::data()[$data['code_type']]['expire_time'];
    }

    /**
     * 检查验证码
     * @param $mobile
     * @param $code
     * @param $type
     * @return bool
     */
    public static function checkVerifyCode($mobile, $code, $type){
        $verifyId = self::where(['mobile'=>$mobile, 'code'=>$code, 'code_type'=>$type, 'is_used'=>0, 'expire_time'=>['EGT', time()]])->value('verify_id');
        if($verifyId){
            self::useVerifyCode($verifyId);
            return true;
        }
        return false;
    }

    /**
     * 使用验证码
     * @param $verifyId
     * @return int
     */
    public static function useVerifyCode($verifyId){
        return self::where(['verify_id'=>$verifyId])->setField('is_used', 1);
    }

    /**
     * 发送验证码
     * @param $mobile
     * @param $wxappId
     * @param $codeType
     * @throws \think\Exception
     */
    public static function sendVerifyCode($mobile, $wxappId, $codeType=VerifyCodeEnum::REGISTER){

        $code = self::createVerify();
        (new self)->isUpdate(false)->save([
            'mobile' => $mobile,
            'code' => $code,
            'code_type' => $codeType,
            'wxapp_id' => $wxappId
        ]);
        $msgType = VerifyCodeEnum::data()[$codeType]['msg_type'];
        $smsConfig = SettingModel::getItem('sms', $wxappId);
        $smsConfig['engine'][$smsConfig['default']][$msgType]['accept_phone'] = $mobile;
        $SmsDriver = new SmsDriver($smsConfig);
        $SmsDriver->sendSms($msgType, ['code' => $code]);
    }

    /**
     * 生成6位的随机数
     * @return int
     */
    public static function createVerify(){
        return rand(100000,999999);
    }

    /**
     * 检查短信是否能够发送
     * @param $mobile
     * @param $wxappId
     * @param int $codeType
     * @throws Exception
     */
    public static function checkSendRight($mobile, $wxappId, $codeType=VerifyCodeEnum::REGISTER){
        $config = VerifyCodeEnum::data()[$codeType];
        ##判断今日发送条数
        $count = self::where([
                'mobile'=>$mobile,
                'wxapp_id' => $wxappId,
                'create_time'=>['GT', get_day_start_timestamp()],
                'code_type'=>$codeType
            ])
            ->count('verify_id');
        if($count >= $config['days_num'])throw new Exception('每日短信条数已达上限');
        ##判断短时间连续发短信
        $sendExpireTime = time() - $config['send_expire_time'];
        $check = self::where([
                'mobile' => $mobile,
                'wxapp_id' => $wxappId,
                'create_time' => ['GT', $sendExpireTime],
                'code_type'=>$codeType
            ])->count('verify_id');
        if($check)throw new Exception('请勿频繁发送短信');
    }

}