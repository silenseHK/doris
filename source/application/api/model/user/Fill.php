<?php


namespace app\api\model\user;

use app\api\model\User;
use app\common\model\user\Fill as FillModel;
use think\db\Query;
use think\Exception;

class Fill extends FillModel
{

    public function getIndexData(){
        $params = [
            'questionnaire_id' => input('questionnaire_id',0,'intval'),
            'start_time' => input('start_time','','str_filter'),
            'end_time' => input('end_time','','str_filter'),
            'keywords' => input('keywords','','str_filter'),
        ];
        $this->setWhere($params);
        $list = $this
            ->with(
                [
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'avatarUrl', 'mobile']);
                    }
                ]
            )
            ->paginate(15,false,['query'=>\request()->request()]);
        return array_merge(compact('list'),$params);
    }

    public function setWhere($params){
        if($params['questionnaire_id'] <= 0)throw new Exception('参数缺失');
        $where = [
            'questionnaire_id' => $params['questionnaire_id']
        ];
        if($params['start_time'] && $params['end_time']){
            $where['create_time'] = ['BETWEEN', [strtotime($params['start_time'] . " 00:00:01"), strtotime($params['end_time'] . " 23:59:59")]];
        }
        if($params['keywords']){
            $user_ids = User::where(['mobile'=>['LIKE', "%{$params['keywords']}%"]])->column('user_id');
            $where['user_id'] = ['IN', $user_ids];
        }
        $this->where($where);
    }

    public function userFillDetail(){
        $fill_id = input('fill_id',0,'intval');
        ##答卷详情
        $data = self::get(['fill_id'=>$fill_id], ['userAnswer.question.option']);
        return compact('data');
    }

    public static function countPointBMI($fill_id){
        $data = self::get(['fill_id'=>$fill_id], ['userAnswer.question.option']);
        $point = 0;
        $weight = $height = 0;
        foreach($data['user_answer'] as $item){
            if(in_array($item['question']['type']['value'], [20, 30])){
                $option = $item['question']['option'];
                $option = $option->toArray();
                $new_option = array_column($option, null, 'mark');
                foreach($item['answer_mark'] as $it){
                    $point += $new_option[$it]['point'];
                }
            }
            if($item['question']['name'] == 'weight'){
                $weight = floatval($item['answer']);
            }
            if($item['question']['name'] == 'height'){
                $height = floatval($item['answer']);
            }
        }
        $bmi = 0;
        if($weight && $height){
            $bmi = $weight / ($height * $height /10000);
            $bmi = round($bmi,4);
        }
        return compact('point','bmi');
    }

}