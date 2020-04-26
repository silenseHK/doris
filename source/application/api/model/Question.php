<?php


namespace app\api\model;

use app\common\model\Question as QuestionModel;
use think\Exception;

class Question extends QuestionModel
{

    /**
     * 检查答案
     * @param $item
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function checkAnswer($item){
        ##获取问题信息
        if(!isset($item['question_id']))throw new Exception('数据格式错误');
        $question_id = intval($item['question_id']);
        $question_info = self::get(['question_id'=>$question_id], ['option']);
        if(!$question_info)throw new Exception('数据异常[问题不存在]');

        $data = [
            'question_id' => $item['question_id']
        ];
        if(in_array($question_info['type']['value'], [10,40])){ ##输入框
            if(!isset($item['answer']) || !$item['answer'])throw new Exception('请完善调查表');
            $data['answer'] = str_filter($item['answer']);
        }
        if(in_array($question_info['type']['value'], [20,30])){ ##选择题
            if(!isset($item['answer_mark']) || !$item['answer_mark'])throw new Exception('请完善调查表');
            $answer_mark = $item['answer_mark'];
            if($question_info['type']['value'] == 20){ ##单选
                if(count($answer_mark) > 1)throw new Exception('答案格式错误');

            }
            $data['answer_mark'] = "-" . strtoupper(trim(implode('-',$answer_mark),'-')) . "-";
            if(isset($item['answer']) && $item['answer']){
                $data['answer'] = str_filter($item['answer']);
            }
        }
        return $data;
    }

}