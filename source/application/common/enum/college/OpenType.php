<?php


namespace app\common\enum\college;


use app\common\enum\EnumBasics;

class OpenType extends EnumBasics
{

    const YES = 1;

    const NO = 0;

    public static function publicData(){
        return [
            self::YES => [
                'text' => '公开',
                'value' => self::YES
            ],
            self::NO => [
                'text' => '不公开',
                'value' => self::NO
            ],
        ];
    }

    public static function privateData(){
        return [
            self::YES => [
                'text' => '大咖私享',
                'value' => self::YES
            ],
            self::NO => [
                'text' => '非私享',
                'value' => self::NO
            ],
        ];
    }

    public static function gradeData(){
        return [
            self::YES => [
                'text' => '级别可见',
                'value' => self::YES
            ],
            self::NO => [
                'text' => '非级别可见',
                'value' => self::NO
            ],
        ];
    }

}