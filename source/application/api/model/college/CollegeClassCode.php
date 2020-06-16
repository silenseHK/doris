<?php


namespace app\api\model\college;

use app\common\model\college\CollegeClassCode as CollegeClassCodeModel;

class CollegeClassCode extends CollegeClassCodeModel
{

    /**
     * 验证私享码
     * @param $class_id
     * @param $class_code
     * @param $user_id
     * @return int|string
     * @throws \think\Exception
     */
    public function check($class_id, $class_code, $user_id){
        $model = $this;
        $class = CollegeClass::get($class_id);
        $code_info = $model
            ->where(['code'=>$class_code, 'start_time'=>['LT', time()], 'expire_time'=>['GT', time()]])
            ->where(function($query) use ($class_id, $class){
                $query->where(['class_id'=>$class_id])->whereOr(['lesson_id'=>$class['lesson_id'], 'code_type'=>20]);
            })
            ->field(['code_id', 'can_use_num', 'had_use_num'])
            ->find();
        if(!$code_info){
            $model->error='无效私享码';
            return false;
        }
        ##验证是否已使用过
        $log_info = CollegeClassCodeLog::get(['user_id'=>$user_id, 'code_id'=>$code_info['code_id']]);
        if(!$log_info){
            if($code_info['can_use_num'] && $code_info['can_use_num'] <= $code_info['had_use_num']){
                $model->error = '使用人数已满';
                return false;
            }
            self::useCode($user_id, $code_info['code_id']);
        }
        return true;
    }

    /**
     * 使用私享码
     * @param $user_id
     * @param $code_id
     * @throws \think\Exception
     */
    public static function useCode($user_id, $code_id){
        ##增加使用记录
        CollegeClassCodeLog::add(compact('user_id','code_id'));
        ##增加使用次数
        self::where(['code_id'=>$code_id])->setInc('had_use_num',1);
    }

}