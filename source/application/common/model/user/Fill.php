<?php


namespace app\common\model\user;


use app\common\model\BaseModel;
use app\common\model\User;
use traits\model\SoftDelete;

class Fill extends BaseModel
{

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $name = 'user_fill';

    protected $pk = 'fill_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id', 'group_user_id'];

    protected $append = ['bmi_advice'];

    /**
     * bmi建议列表
     * @var array[]
     */
    protected $bmi_advice_list = [
        [
            'max' => 18.5,
            'min' => 0,
            'message' => "您的身体质量指数BMI=%s，体重低于健康范围，会增加许多健康风险。"
        ],
        [
            'max' => 24,
            'min' => 18.5,
            'message' => "您的身体质量指数BMI=%s，属于正常范围，可适当优化体型。保持均衡饮食+合理运动。"
        ],
        [
            'max' => 28,
            'min' => 24,
            'message' => "您的身体质量指数BMI=%s，已经超重，增加了患脂肪肝、糖尿病及其他慢性病的风险。建议您调整饮食结构+合理运动。"
        ],
        [
            'max' => 9999,
            'min' => 28,
            'message' => "您的身体质量指数BMI=%s，属于肥胖，生活质量下降，且增加了许多慢性病风险，如三高与冠心病。建议您调整饮食结构+合理运动。"
        ],
    ];

    public function setWxappIdAttr(){
        return static::$wxapp_id ? : 10001;
    }

    public function getAdviceAttr($value){
        return json_decode($value,true);
    }

    public function getPainPointAnalysisAttr($value){
        return json_decode($value,true);
    }

    /**
     * 处理团队user_id
     * @param $value
     * @param $data
     * @return int
     */
    public function setGroupUserIdAttr($value, $data){
        return User::getGroupUserId($data['user_id']);
    }

    /**
     * 初始化bmi建议
     * @param $value
     * @param $data
     * @return string
     */
    public function getBmiAdviceAttr($value, $data){
        if(!isset($data['bmi']))return '';
        foreach($this->bmi_advice_list as $item){
            if($item['min'] < $data['bmi'] && $item['max'] <= $data['bmi']){
                return sprintf($item['message'], $data['bmi']);
            }
        }
        return '';
    }

    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    public function userAnswer(){
        return $this->hasMany('app\common\model\user\FillAnswer','fill_id','fill_id');
    }

    public function foodGroup(){
        return $this->hasOne('app\common\model\FoodGroup','id','food_group_id');
    }

    public function groupUser(){
        return $this->belongsTo('app\common\model\User','group_user_id','user_id');
    }

    public function inviteUser(){
        return $this->belongsTo('app\common\model\User','invite_user_id','user_id');
    }

}