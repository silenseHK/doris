<?php


namespace app\common\enum\user\grade;


use app\common\enum\EnumBasics;

class Rebate extends EnumBasics
{
    ## 返利
    const DID = 10;
    ## 不返利
    const NOT = 20;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data(){
        return [
            self::DID => [
                'text' => '返利',
                'value' => self::DID
            ],
            self::NOT => [
                'text' => '不返利',
                'value' => self::NOT
            ]
        ];
    }
}