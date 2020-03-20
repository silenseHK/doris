<?php


namespace app\common\enum\user\grade;


use app\common\enum\EnumBasics;

class GradeType extends EnumBasics
{
    ## 高等级
    const HIGH = 20;

    ##低等级
    const LOW = 10;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data(){
        return [
            self::HIGH => [
                'text' => '高阶',
                'value' => self::HIGH
            ],
            self::LOW => [
                'text' => '低阶',
                'value' => self::LOW
            ]
        ];
    }
}