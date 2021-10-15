<?php


namespace app\common\enum\user\grade;


use app\common\enum\EnumBasics;

class GradeType extends EnumBasics
{

    ## 合伙人OR董事
    const HIDE = 30;

    ## 代理
    const HIGH = 20;

    ## 消费者
    const LOW = 10;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data(){
        return [
            self::HIDE => [
                'text' => '创始人',
                'value' => self::HIDE
            ],
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