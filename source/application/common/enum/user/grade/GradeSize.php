<?php


namespace app\common\enum\user\grade;


use app\common\enum\EnumBasics;

class GradeSize extends EnumBasics
{

    const PARTNER = 80;  //合伙人

    const DIRECTOR = 70;  //董事

    const STRATEGY = 60;  //战略董事

    const AGENT = 50;  //总代

    const VIP = 40;  //VIP特约

    const MONTH = 30;  //月体验

    const WEEK = 20;  //周体验

    const VISITOR = 10;  //游客

    /**
     * 获取配置
     * @return array
     */
    public static function data(){
        return [
            self::PARTNER => [
                'text' => '合伙人',
                'value' => 80,
            ],
            self::DIRECTOR => [
                'text' => '董事',
                'value' => 70,
            ],
            self::STRATEGY => [
                'text' => '战略董事',
                'value' => 60,
            ],
            self::AGENT => [
                'text' => '总代',
                'value' => 50,
            ],
            self::VIP => [
                'text' => 'VIP特约',
                'value' => 40,
            ],
            self::MONTH => [
                'text' => '月体验',
                'value' => 30,
            ],
            self::WEEK => [
                'text' => '周体验',
                'value' => 20,
            ],
            self::VISITOR => [
                'text' => '游客',
                'value' => 10,
            ],
        ];
    }

}