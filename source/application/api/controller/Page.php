<?php

namespace app\api\controller;

use app\api\model\Dietitian;
use app\api\model\Entry;
use app\api\model\Impression;
use app\api\model\QualitySpec;
use app\api\model\WxappPage;

/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class Page extends Controller
{
    /**
     * 页面数据
     * @param null $page_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($page_id = null)
    {
        // 页面元素
        $data = WxappPage::getPageData($this->getUser(false), $page_id);
        return $this->renderSuccess($data);
    }

    /**
     * 首页diy数据 (即将废弃)
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function home()
    {
        // 页面元素
        $data = WxappPage::getPageData($this->getUser(false));
        return $this->renderSuccess($data);
    }

    /**
     * 自定义页数据 (即将废弃)
     * @param $page_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function custom($page_id)
    {
        // 页面元素
        $data = WxappPage::getPageData($this->getUser(false), $page_id);
        return $this->renderSuccess($data);
    }

    /**
     * 小程序web_view页面参数
     * @return array
     */
    public function webView(){
        $page = [
            'experience_rank' => [
                'url' => request()->domain() . "/web_view/experience_rank/index.html"
            ],
            'new_questionnaire' => [
                'url' => request()->domain() . "/web_view/new_questionnaire/index.html",
                'title' => '问券调查',
                'questionnaire_no' => '202007290001'
            ],
        ];
        return $this->renderSuccess($page);
    }

    /**
     * 首页数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function indexData(){
        ##印象
        $impressionModel = new Impression();
        $impression_data = $impressionModel->impression();
        $impression = [
            'name' => '印象',
            'type' => 'impression',
            'style' => [
                'btnColor' => '#ffffff',
                'btnShape' => 'round'
            ],
            'params' => [
                'interval' => '2800'
            ],
            'data' => $impression_data
        ];
        ##优质
        $qualityModel = new QualitySpec();
        $quality_data = $qualityModel->quality();
        $high_quality = [
            'name' => '优质',
            'type' => 'bestValue',
            'style' => [
                'btnColor' => '#ffffff',
                'btnShape' => 'round'
            ],
            'params' => [
                'interval' => '2800'
            ],
            'data' => $quality_data
        ];
        ##营养师
        $dietitianModel = new Dietitian();
        $dietitian_data = $dietitianModel->dietitian();
        $dietitian = [
            'name' => '营养师',
            'type' => 'dietitian',
            'style' => [
                'btnColor' => '#ffffff',
                'btnShape' => 'round'
            ],
            'params' => [
                'interval' => '2800'
            ],
            'data' => $dietitian_data
        ];
        ##词条
        $entryModel = new Entry();
        $entry_data = $entryModel->entry();
        $entry = [
            'name' => '词条',
            'type' => 'entry',
            'style' => [
                'btnColor' => '#ffffff',
                'btnShape' => 'round'
            ],
            'params' => [
                'interval' => '2800'
            ],
            'data' => $entry_data
        ];
        return $this->renderSuccess(compact('impression','high_quality','dietitian','entry'));
    }

}
