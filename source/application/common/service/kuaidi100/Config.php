<?php


namespace app\common\service\kuaidi100;


class Config
{

    function __construct()
    {

    }

    protected $key = 'bDfciruN248';

    protected $secret = '5e228558d0fe4a4f930e7249235813a0';

    protected $customer = '246697124CED07C190975319669E8B64';

//    protected $partnerId = '6107561270';
    protected $partnerId = '61075612701';

    protected $partnerKey = 'G2eSN5pdhgsqxVw8mM9ryuKHQjYzaR';

//    protected $siid = 'SNPRC-1602-01';
//    protected $siid = '2013DJ1724';
//    protected $siid = 'WSD-4f049ac5-8506-4bc8-a928-739bb72b3a0d.003d';
    protected $siid = 'SDGOB-1392';

    protected $url = [
        'getPrintImg' => 'https://poll.kuaidi100.com/printapi/printtask.do?method=getPrintImg',
        'eOrder' => 'https://poll.kuaidi100.com/printapi/printtask.do?method=eOrder',
        'getElecOrder' => 'http://poll.kuaidi100.com/eorderapi.do',
        'printOrder' => 'http://poll.kuaidi100.com/printapi/printtask.do?method=printOrder',
        'printOld' => 'https://poll.kuaidi100.com/printapi/printtask.do?method=printOld'
    ];

    protected $methods = ['getPrintImg', 'eOrder', 'getElecOrder', 'printOrder', 'printOld'];

    protected $tempIds = [
        'yunda' => '4d8b2838de30411187b850793028f73d',
    ];

}