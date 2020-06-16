<?php


namespace app\common\enum\user\grade;

class RebateConfig extends GradeSize
{

    const STRATEGY_INDIRECT = 601;

    const DIRECTOR_INDIRECT = 701;

    const PARTNER_INDIRECT = 801;

    public static function getConf(){
        return [
            self::VIP => [
                self::VIP => [
                    'text' => 'VIP平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 10
                    ]
                ]
            ],
            self::AGENT => [
                self::VIP => [
                    'text' => 'VIP低推高奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ],
                self::AGENT => [
                    'text' => '总代平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 10
                    ]
                ],
            ],
            self::STRATEGY => [
                self::AGENT => [
                    'text' => '总代低推高奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ],
                self::STRATEGY => [
                    'text' => '战略董事平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 10
                    ]
                ],
                self::STRATEGY_INDIRECT => [
                    'text' => '战略董事间接奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ]
            ],
            self::DIRECTOR => [
                self::DIRECTOR => [
                    'text' => '董事合伙人平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ],
                self::PARTNER => [
                    'text' => '董事合伙人平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ],
                self::DIRECTOR_INDIRECT => [
                    'text' => '董事合伙人间接奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 2
                    ]
                ],
                self::PARTNER_INDIRECT => [
                    'text' => '董事合伙人间接奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 2
                    ]
                ],
            ],
            self::PARTNER => [
                self::DIRECTOR => [
                    'text' => '董事合伙人平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ],
                self::PARTNER => [
                    'text' => '董事合伙人平级奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 5
                    ]
                ],
                self::DIRECTOR_INDIRECT => [
                    'text' => '董事合伙人间接奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 2
                    ]
                ],
                self::PARTNER_INDIRECT => [
                    'text' => '董事合伙人间接奖励',
                    'rebate' => [
                        '0' => 0,
                        '1' => 2
                    ]
                ],
            ]
        ];
    }

}