<?php


namespace app\api\model;

use app\api\model\user\Fill;
use app\api\model\user\FillAnswer;
use app\api\validate\user\questionnaireValidate;
use app\common\model\FoodGroup;
use app\common\model\Questionnaire as QuestionnaireModel;
use app\api\model\Question as QuestionModel;
use app\api\model\user\Fill as FillModel;
use app\api\model\user\FillAnswer as FillAnswerModel;
use app\common\model\QuestionnaireCate;
use app\api\model\QuestionnaireCate as ApiQuestionnaireCateModel;
use think\Db;
use think\Exception;

class Questionnaire extends QuestionnaireModel
{

    protected $hidden = ['delete_time', 'update_time', 'create_time', 'wxapp_id', 'status'];

    /**
     * 问卷信息
     * @param $questionnaire_no
     * @param $user
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($questionnaire_no, $user){
        $info = $this
            ->where(compact('questionnaire_no'))
            ->with(
                [
                    'questions.option'
                ]
            )
            ->find();
        if(!$info)throw new Exception('问卷不存在');
        if($info['status'] != 1)throw new Exception('问卷已下架');
        ##判断用户是否已提交
        $answer = Fill::where(['user_id'=>$user['user_id'], 'questionnaire_id'=>$info['questionnaire_id']])->find();
        $is_submit = 0;
        $img = '';
        if($answer){
            $is_submit = 1;
            $img = FoodGroup::getUserImg($answer['bmi'],1);
            if($answer['create_time'] <= (time() - 7 * 24 * 60 * 60))$is_submit = 2;
        }
        $info['is_submit'] = $is_submit;
        $info['img'] = $img;
        return $info;
    }

    public function newInfo($questionnaire_no, $user){
        $info = self::get(compact('questionnaire_no'), ['cate', 'questions.option']);
        if(!$info)throw new Exception('问卷不存在');
        if($info['status'] != 1)throw new Exception('问卷已下架');
        $info = $info->toArray();
        $cate_list = $info['cate'];
        $question_list = $info['questions'];
        ##获取问题数据
        foreach($cate_list as &$cate){
            $cate['questions'] = [];
            foreach($question_list as $question){
                if($question['pivot']['question_cate_id'] == $cate['pivot']['question_cate_id']){
                    $question['show_limit'] = $question['pivot']['show_limit'];
                    $cate['questions'][] = $question;
                }
            }
        }
        $data = [
            'questionnaire_id' => $info['questionnaire_id'],
            'questionnaire_no' => $questionnaire_no,
            'title' => $info['title'],
            'questions' => $cate_list
        ];
        return $data;
    }

    /**
     * 提交问卷
     * @param $user
     * @return array|string
     * @throws Exception
     */
    public function submitQuestionnaire($user){
        ##验证
        $validate = new questionnaireValidate();
        $params = @file_get_contents('php://input');
        $params = json_decode($params,true);
        if(!$validate->scene('submit')->check($params))throw new Exception($validate->getError());
        ##参数
        $questionnaire_id = intval($params['questionnaire_id']);
        ##检查是否已提交
        $fillModel = new FillModel();
//        if($fillModel->where(['user_id'=>$user['user_id'], 'questionnaire_id'=>$questionnaire_id])->count() > 0)throw new Exception('请勿重复提交');
        $answer = $params['answer'];
        if(empty($answer))throw new Exception('参数错误');
        Db::startTrans();
        try{
            $data = [
                'user_id' => $user['user_id'],
                'questionnaire_id' => $questionnaire_id
            ];

            $res = $fillModel->isUpdate(false)->save($data);
            if($res === false)throw new Exception('提交失败');
            $fill_id = $fillModel->getLastInsID();
            $questionModel = new QuestionModel();
            $fillAnswerModel = new FillAnswer();
            $question_data = [];
            foreach($answer as $item){
                $item_data = $questionModel->checkAnswer($item);
                $item_data['user_id'] = $user['user_id'];
                $item_data['questionnaire_id'] = $questionnaire_id;
                $item_data['fill_id'] = $fill_id;
                $question_data[] = $item_data;
            }
            $res = $fillAnswerModel->isUpdate(false)->saveAll($question_data);
            if($res === false)throw new Exception('提交失败...');

            ##计算得分和BMI
            $pointBMI = Fill::countPointBMI($fill_id);
            $res = $fillModel->where(['fill_id'=>$fill_id])->update($pointBMI);
            if($res === false)throw new Exception('提交失败');
            Db::commit();
            return $pointBMI;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 新问卷提交
     * @param $user
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function newSubmitQuestionnaire($user){
        ##验证
        $validate = new questionnaireValidate();
        $params = @file_get_contents('php://input');
//        print_r($params);die;
        $params = json_decode($params,true);
        if(!$validate->scene('submit')->check($params))throw new Exception($validate->getError());
        file_put_contents('questionnaire', json_encode($params));
        ##参数
        $questionnaire_id = intval($params['questionnaire_id']);
        $info = self::get($questionnaire_id, ['questions', 'cate']);
        if(!$info)throw new Exception('问卷信息不存在');
        $answer = $params['answer'];
        if(empty($answer))throw new Exception('参数错误');
        Db::startTrans();
        try{
            ##添加表单填写数据
            $data = [
                'user_id' => $user['user_id'],
                'questionnaire_id' => $questionnaire_id,
                'invite_user_id' => isset($params['referee_id'])?intval($params['referee_id']):0
            ];
            $fillModel = new FillModel();
            $res = $fillModel->isUpdate(false)->save($data);
            if($res === false)throw new Exception('提交失败');
            $fill_id = $fillModel->getLastInsID();
            $questionModel = new QuestionModel();
            $fillAnswerModel = new FillAnswer();
            $question_data = [];
            foreach($answer as $item){
                $item_data = $questionModel->checkAnswer($item);
                $item_data['user_id'] = $user['user_id'];
                $item_data['questionnaire_id'] = $questionnaire_id;
                $item_data['fill_id'] = $fill_id;
                $question_data[] = $item_data;
            }
            $res = $fillAnswerModel->isUpdate(false)->saveAll($question_data);
            if($res === false)throw new Exception('提交失败...');

            ##计算得分和BMI
            $pointBMI = Fill::countPointBMI($fill_id);
            ##获取配餐图
            $suggestion = $this->distributeFoodGroup($info->toArray(), $answer, $pointBMI['bmi']);
            if(!$suggestion)return false;
            $res = $fillModel->where(['fill_id'=>$fill_id])->update([
                'bmi' => $pointBMI['bmi'],
                'point' => $pointBMI['point'],
                'advice' => json_encode($suggestion['advice']),
                'pain_point_analysis' => json_encode($suggestion['pain_point_analysis']),
                'food_group_id' => $suggestion['food_group_id']
            ]);
            if($res === false)throw new Exception('提交失败');
            Db::commit();
            return $fill_id;
        }catch(Exception $e){
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 生成配餐图和健康建议
     * @param $info
     * @param $answers
     * @param $bmi
     * @return array|bool
     */
    public function distributeFoodGroup($info, $answers, $bmi){
        ##获取身体状况字段sub_health H.高尿酸 M.高血压 N.高血脂 O.高血糖
        $questions = $info['questions'];
        $questions = array_column($questions, null, 'name');
        $answers = array_column($answers, null, 'question_id');
//        $food_group_id = 0;
        ##判断身体胖瘦标准 1: 偏瘦 2:正常 3:肥胖
        $figure_standard = 2;
        if($bmi < 18.5)
            $figure_standard = 1;
        if($bmi >= 18.5 && $bmi <= 23.9)
            $figure_standard = 2;
        if($bmi > 23.9)
            $figure_standard = 3;
        ##判断是否选择减脂
        if(!isset($questions['health_goals'])){
            $this->error = '问卷异常';
            return false;
        }
        $health_goals_id = $questions['health_goals']['question_id'];
        if(!isset($answers[$health_goals_id])){
            $this->error = '问题未填写完整';
            return false;
        }
        $is_reduce = 0;
        if(in_array('E',$answers[$health_goals_id]['answer_mark']))$is_reduce = 1;
        ##获取配餐图
        if(!isset($questions['diet_prefer'])){
            $this->error = '问卷异常';
            return false;
        }
        $food_group_id = 0;

        ##判断纯素食
        $diet_prefer_id = $questions['diet_prefer']['question_id'];
        if(!isset($answers[$diet_prefer_id])){
            $this->error = '问题未填写完整';
            return false;
        }
        if(in_array('G',$answers[$diet_prefer_id]['answer_mark'])){ ##纯素食者
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>7])->value('id')); ##素食减脂食谱
            }else if($figure_standard == 2){
                if($is_reduce)
                    $food_group_id = (int)(FoodGroup::where(['type'=>7])->value('id')); ##素食减脂食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>8])->value('id')); ##素食非减脂食谱
            }
        }
        ##判断老年人
        $new_age_id = $questions['new_age']['question_id'];
        if(!isset($answers[$new_age_id])){
            $this->error = '问题未填写完整';
            return false;
        }
        if(intval($answers[$new_age_id]['answer']) > 60 && !$food_group_id){ ##老年人
            if($figure_standard == 3){
                $food_group_id = FoodGroup::getUserFoodsGroupId($bmi,2); ## 普通减脂食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>6])->value('id')); ##老年人食谱
            }
        }
        ##判断生病
        $sub_health_id = $questions['sub_health']['question_id'];
        if(!isset($answers[$new_age_id])){
            $this->error = '问题未填写完整';
            return false;
        }
        $sub_health_remark = $answers[$sub_health_id]['answer_mark'];
        if(in_array('H', $sub_health_remark) && !$food_group_id){ ## 高尿酸
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>2])->value('id')); ##高尿酸食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>2])->value('id')); ##高尿酸食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }
        }
        if(in_array('N', $sub_health_remark) && !$food_group_id){ ## 高血糖
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>3])->value('id')); ##高血糖食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>3])->value('id')); ##高血糖食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }
        }
        if(in_array('M', $sub_health_remark) && !$food_group_id){ ## 高血压
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>4])->value('id')); ##高血压食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>4])->value('id')); ##高血压食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }
        }
        if(in_array('K', $sub_health_remark) && !$food_group_id){ ## 低血压
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>11])->value('id')); ##低血压食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>11])->value('id')); ##低血压食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }
        }
        if(in_array('O', $sub_health_remark) && !$food_group_id){ ## 高血脂
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>5])->value('id')); ##高血脂食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>5])->value('id')); ##高血脂食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }
        }
        if(in_array('Q', $sub_health_remark) && !$food_group_id){ ## 甲减
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>13])->value('id')); ##甲减食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>13])->value('id')); ##甲减食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }
        }
        if(in_array('P', $sub_health_remark) && !$food_group_id){ ## 多囊
            if($figure_standard == 3){
                $food_group_id = (int)(FoodGroup::where(['type'=>12])->value('id')); ##多囊食谱
            }else if($figure_standard == 2 && $is_reduce){
                $food_group_id = (int)(FoodGroup::where(['type'=>12])->value('id')); ##多囊食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }

        }
        if(!$food_group_id){
            ##判断BMI
            if($figure_standard == 3){
                $food_group_id = FoodGroup::getUserFoodsGroupId($bmi,2); ## 普通减脂食谱
            }else if($figure_standard == 2){
                if($is_reduce)
                    $food_group_id = FoodGroup::getUserFoodsGroupId($bmi,2); ## 普通减脂食谱
                else
                    $food_group_id = (int)(FoodGroup::where(['type'=>9])->value('id')); ##一般人群食谱
            }else{
                $food_group_id = (int)(FoodGroup::where(['type'=>10])->value('id')); ## 增重食谱
            }
        }

        ##健康建议
        $advice = $this->makeAdvice($questions, $answers);
        if(!$advice)return false;

        ##痛点分析
        $pain_point_analysis = $this->makePainPointAnalysis($questions, $answers);
        if(!$pain_point_analysis)return false;

        return compact('food_group_id','advice','pain_point_analysis');

        ##健康建议
        $advice = array_column($info['cate'], null, 'cate_id');
        foreach($advice as &$ad){
            $ad['advice'] = [];
            $ad['question_cate_id'] = $ad['pivot']['question_cate_id'];
        }
        foreach($questions as &$q){
            $q['question_cate_id'] = $q['pivot']['question_cate_id'];
        }
        #1.健康目标
        if(isset($questions['health_goals'])){
            $health_goals_id = $questions['health_goals']['question_id'];
            if(!isset($answers[$health_goals_id])){
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.皮肤 B.情绪、压力、抑郁 C.脑力 D.疲劳 E.控体 F.调理
            if(in_array('B', $answers[$health_goals_id]['answer_mark'])){
                $advice[$questions['health_goals']['question_cate_id']]['advice'][] = "您可能缺乏b族维生素，168太空素食中各种粗粮豆类，富含B族维生素和钙，能起到舒缓神经的作用。其中的决明子、菊花、绿豆等，能去肝火，减缓因肝火淤积导致的情绪上的波动。";
            }
            if(in_array('D', $answers[$health_goals_id]['answer_mark'])){
                $advice[$questions['health_goals']['question_cate_id']]['advice'][] = "168太空素食中蛋白质富含优质的植物蛋白，能有效抵抗疲劳。其中的B族维生素以及适量的玛咖粉，还能有效激活神经。人参等具有双向调节神经系统的食物，缓解疲劳；红枣、枸杞等帮助补气养血；黑糯米、黑木耳、黑蒜等黑色食物帮助滋阴补肾、补血益气。";
            }
            if(in_array('E', $answers[$health_goals_id]['answer_mark']) || in_array('F', $answers[$health_goals_id]['answer_mark'])){
                $advice[$questions['health_goals']['question_cate_id']]['advice'][] = "168太空素食，应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务原理，从而科学提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。";
            }
            if(in_array('A', $answers[$health_goals_id]['answer_mark']) && isset($questions['new_age'])){
                $age_id =  $questions['new_age']['question_id'];
                if(!isset($answers[$age_id])){
                    $this->error = '请填写年龄';
                    return false;
                }
                $age_answer = (int)$answers[$age_id]['answer'];
                if($age_answer > 25){
                    $advice[$questions['health_goals']['question_cate_id']]['advice'][] = "您需要担心皮肤老化，角质层增厚的问题。168太空素食中的针叶樱桃、猕猴桃等水果蔬菜含有丰富的维生素C，配合其中坚果中所含的维生素E，既美白，又延缓衰老，双重对皮肤进行保护。其中的膳食纤维能清除体内垃圾，避免各类色斑的形成。另外还有五种花朵植物，能改善气血，娇养肌肤。";
                }
            }
            if(in_array('C', $answers[$health_goals_id]['answer_mark']) && isset($questions['sub_health']) && isset($questions['new_age']) && isset($questions['sleep_time'])){
                ###年龄
                $age_id =  $questions['new_age']['question_id'];
                if(!isset($answers[$age_id])){
                    $this->error = '请填写年龄';
                    return false;
                }
                $age_answer = (int)$answers[$age_id]['answer'];
                ###亚健康
                $sub_health_id = $questions['sub_health']['question_id'];
                if(!isset($answers[$sub_health_id])){
                    $this->error = '请选择急需改善的问题';
                    return false;
                }
                ###睡觉时间
                $sleep_time_id = $questions['sleep_time']['question_id'];
                if(!isset($answers[$sleep_time_id])){
                    $this->error = '请选择每晚入睡时间';
                    return false;
                }
                if($age_answer > 28 && in_array('C', $answers[$sub_health_id]['answer_mark']) && in_array('B', $answers[$sleep_time_id]['answer_mark'])){
                    $advice[$questions['health_goals']['question_cate_id']]['advice'][] = "您可能有脑供血不足的状况，建议去医院做详细检查。如无其他疾病，可以服用168太空素食。168太空素食中蛋白质含量高，则能增强大脑的活动性，提高脑力劳动的效率。其中的维生素B1能够促进碳水化合物的代谢，为大脑提供能量，而不用大脑动用自己的能量储备或者用蛋白质作为能量，有保护大脑的功能。其中的不饱和脂肪酸能提高脑细胞的活性，增强记忆力和思维能力。";
                }
            }
        }

        #2.亚健康问题
        if(isset($questions['sub_health'])){
            $sub_health_id = $questions['sub_health']['question_id'];
            if(!isset($answers[$sub_health_id])){
                $this->error = '问题未填写完整..';
                return false;
            }
            ## A.脱发 B.精神萎靡 C.记忆力下降 D.情绪低落 E.睡眠质量差 F.免疫力下降 G.性功能减退 H.高尿酸 I.肠胃功能紊乱 J.食欲减退 K.低血压 L.低血糖
            if(in_array('A', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "最常见的脱发是由于内分泌失调造成的，长期内分泌失调脱发的患者，我们会提醒规避脂溢性皮炎的风险，并建议长期食用富含维生素E（主）和维生素C（辅）的食物。而168太空素食中添加了23种坚果籽种，富含维生素E和不饱和脂肪酸，在调解内分泌方面有良好作用。多种蔬菜水果，补充维生素C。与维生素E相辅相承，起到调理的作用。";
            }
            if(in_array('B', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "营养不足是导致精力下降的原因之一。这时候，“黑五类”食物就起到了很大的作用。
  黑五类，五种黑色补元气的食物，就是指黑豆、黑芝麻、黑木耳、黑枣、黑核桃这五种黑色食物。经常吃这些食物，可以益气固本，起到补充元气的作用。168太空素食当中，就添加了这些成分，在补充营养的同时，还增添了醇厚口感。";
            }
            if(in_array('C', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "排除因为身体机能老化造成记忆力下降的原因。中青年群体出现记忆力下降的原因都是可以通过饮食来改善。碱性食物，富含卵磷脂和不饱和脂肪酸的食物，都对改善记忆力有好处。碱性食物：多指蔬菜，水果，豆类，奶类。现代人的饮食多油腻而少清淡。讲求食物多样化、保证荤素合理搭配是平衡膳食的重要原则，也是健康的重要原则。卵磷脂：能增强脑部活力，延续脑细胞老化，并且有护肝、降血脂、预防脑中风等作用。不饱和脂肪酸：降低血中胆固醇和甘油三酯；降低血液粘稠度，改善血液微循环；提高脑细胞的活性，增强记忆力和思维能力。168太空素食中添加大量蔬菜瓜果，有富含大量卵磷脂的原料食物，还特别添加大豆磷脂。有23种坚果籽种，可以补充不饱和脂肪酸。";
            }
            if(in_array('D', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "是抑郁症的前兆。轻度抑郁的患者，可以适量的补充一些复合维生素B。在168太空素食当中，B族维生素NRV非常高，其中B1占51%，B2占54%，B6占52%。在基本膳食正常的情况下，一包168完全可以补充每日所需。";
            }
            if(in_array('E', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "失眠是现在非常常见的一个亚健康问题，是一种由多种原因引起的睡眠障碍症。原因众多。168太空素食注重药食同源，其中酸枣仁就是一种针对失眠有奇效的中药材。酸枣仁具有养肝，宁心，安神，敛汗等作用。主治虚烦不眠，惊悸怔忡。又特别添加γ-氨基丁酸，这是一种强神经抑制性氨基酸，具有镇静、催眠、抗惊厥的生理作用。";
            }
            if(in_array('F', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "富含蛋白质的食物，是可以增强机体免疫力，在我们的168太空素食当中，每100g就富含5.3g优质蛋白（吸收率超过90%的蛋白质称为优质蛋白）。所以，168太空素食具有一定的提高人体免疫力的能力。";
            }
            if(in_array('G', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "是因为性激素分泌减少导致的。而玛咖，被称为天然的荷尔蒙发动机，可以明显改善男性雄性激素分泌不足，还可以调节女性雌激素的分泌水平。在我们的168太空素食当中，也添加了适量的天然玛咖粉，对于改善这一亚健康状态，是有一定的益处的。";
            }
            if(in_array('M', $answers[$sub_health_id]['answer_mark']) || in_array('N', $answers[$sub_health_id]['answer_mark']) || in_array('O', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "慢性疾病会引起心脑血管疾病发生风险明显增加，特别是冠心病、心绞痛、心梗的风险明显的增加，所以临床上应该通过包括生活方式干预在内的治疗控制“三高”。例如，要吃一些清淡的高纤维、高维生素的食物，同时要减少影响血糖的食物摄入，要控制主食的量。168太空素食，富含膳食纤维，可以完全满足一天的碳水的需求，不需要吃其他主食。";
            }
            if(in_array('I', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "饥一顿饱一顿的生活状态，就会导致我们的肠胃遭罪。所以，没有时间吃饭的忙碌时刻，给自己五分钟，一杯营养丰富的168，暖胃暖心，给你更好的状态继续投身工作。";
            }
            if(in_array('J', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "长期食欲消退，就会导致营养不良。而168太空素食不仅包含了人体每天所需的营养素，更添加了鸡内金，山楂，陈皮等温脾养胃，促进消化的中药。可以有效提高食欲，改善营养不良的状态。";
            }
            if(in_array('H', $answers[$sub_health_id]['answer_mark'])) {
                $advice[$questions['sub_health']['question_cate_id']]['advice'][] = "高尿酸是体内嘌呤物质因代谢发生紊乱，致使血液中尿酸增多而引起，易引起痛风。需要避免摄入高嘌呤食物，少食用中嘌呤食物，可以自由摄入低含嘌呤很少的食物。而168太空素食只含有小部分中嘌呤食物，其余食物全为低嘌呤食物。168太空素食以植物蛋白为主，不会阻碍肾脏排泄尿酸的作用。建议每日饮水2000~3000ml，忌饮酒。";
            }
        }

        #3.饮食习惯--饮食偏好
        if(isset($questions['diet_prefer'])){
            $diet_prefer_id = $questions['diet_prefer']['question_id'];
            if(!isset($answers[$diet_prefer_id])){
                $this->error = '问题未填写完整...';
                return false;
            }
            ## A.荤素均衡 B.荤食为主 C.素食为主 D.嗜盐 E.嗜油 F.嗜糖
            if(in_array('B', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice[$questions['diet_prefer']['question_cate_id']]['advice'][] = "荤菜摄入过多易导致热量过剩和各种维生素及矿物质的缺乏。建议饮食荤素搭配，均衡营养。168太空素食富含50余种蔬菜水果，可以补充荤食偏爱者日常饮食所缺乏的各种微量元素。且一袋168太空素食仅仅只有98大卡，营养丰富，热量很低，可配合日常饮食食用。";
            }
            if(in_array('C', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice[$questions['diet_prefer']['question_cate_id']]['advice'][] = "长期素食为主，食物种类不均衡，优质蛋白质的食物来源会大大受到限制，长此以往容易加快人体痩组织流失，建议额外补充优质蛋白提供机体生命活动所需。168太空素食含有20余种谷物豆类，添加了乳清蛋白等优质蛋白成分，富含丰富的植物蛋白和动物蛋白，可为素食人群提供优质蛋白，以及补充日常生命活动所需脂溶性维生素。可配合日常饮食食用。";
            }
            if(in_array('D', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice[$questions['diet_prefer']['question_cate_id']]['advice'][] = "长期高盐饮食容易诱发上呼吸道感染、高血压、骨质疏松、胃炎及胃癌等多种疾病。世界卫生组织建议成人每日盐摄入量不应超过5g。";
            }
            if(in_array('E', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice[$questions['diet_prefer']['question_cate_id']]['advice'][] = "长期嗜吃油腻食物、奶类等高脂肪饮食者，会使体内血脂增高，容易导致心脑血管疾病。《中国居民膳食指南》推荐，健康成年人每人每天烹调用油量不超过25~30克。";
            }
            if(in_array('F', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice[$questions['diet_prefer']['question_cate_id']]['advice'][] = "长期食用过多甜食，容易使人发胖，血糖波动较大，导致体内维生素和矿物质的缺乏，是诱发心脑血管疾病的重要诱因。可用168太空素食代替甜食，降低添加糖摄入的同时补充微量元素。";
            }
        }

        #4.饮食习惯--用餐习惯
        if(isset($questions['eat_habit'])) {
            $eat_habit_id = $questions['eat_habit']['question_id'];
            if(!isset($answers[$eat_habit_id])){
                $this->error = '问题未填写完整....';
                return false;
            }
            ## A.常吃快餐，饮食单一 B.暴饮暴食 C.三餐规律饮食 D.常吃零食 E.在外就餐多 F.进餐速度快
            if(in_array('A', $answers[$eat_habit_id]['answer_mark'])) {
                $advice[$questions['eat_habit']['question_cate_id']]['advice'][] = "在外就餐不易控制，往往油盐热量超标，饮食结构却不够均衡，尤其是快餐食品营养成分简单不够满足日常身体所需。168太空素食营养均衡，符合机体日常的营养所需。在家人忙碌或者饮食结构过于单一化的时候，可以服用一袋168太空素食，满足机体日常营养所需。";
            }
            if(in_array('B', $answers[$eat_habit_id]['answer_mark'])) {
                $advice[$questions['eat_habit']['question_cate_id']]['advice'][] = "长期的暴饮暴食会导致肠胃负担大消化不良，容易引发消化功能紊乱，甚至引起胃肠道疾病，建议从定量饮食做起，适量补充益生元维护胃肠健康。168太空素食所含的低聚果糖和螺旋藻可以帮助恢复胃肠功能，配合专业的食谱，减轻肠胃负担，逐渐恢复正常饮食规律。";
            }
            if(in_array('D', $answers[$eat_habit_id]['answer_mark'])) {
                $advice[$questions['eat_habit']['question_cate_id']]['advice'][] = "一袋168太空素食仅仅只有98大卡，营养丰富，热量很低，控体好搭档，可以替代掉薯片、蜜饯、雪糕等高能量、高油脂但低营养密度的不良零食。";
            }
            if(in_array('F', $answers[$eat_habit_id]['answer_mark'])) {
                $advice[$questions['eat_habit']['question_cate_id']]['advice'][] = "进食速度过快会加重肠胃负担，容易多吃，导致肥胖。一餐进食时间15-30分钟为宜。建议餐前半小时服用168太空素食一袋，补充营养和能量，减缓饥饿感，延缓进餐速度，降低肠胃负担，有效帮助控体。";
            }
        }

        #5.饮食习惯--吃豆类概率
        if(isset($questions['eat_bean_manic'])){
            $eat_bean_manic_id = $questions['eat_bean_manic']['question_id'];
            if(!isset($answers[$eat_bean_manic_id])){
                $this->error = '问题未填写完整.....';
                return false;
            }
            ## A.1周0-2次 B.1周3-6次 C.1周7次以上
            if(in_array('A', $answers[$eat_bean_manic_id]['answer_mark']) || in_array('B', $answers[$eat_bean_manic_id]['answer_mark'])) {
                $advice[$questions['eat_bean_manic']['question_cate_id']]['advice'][] = "谷类是蛋白质、膳食纤维、维生素B族的良好来源，建议每天都要粗细搭配，避免维生素B族缺乏。大豆含丰富的优质蛋白质、必须脂肪酸、多种维生素和膳食纤维，且含有磷脂、低聚糖，以及异黄酮、植物固醇等多种植物化学物质。建议每人每天摄入30-50克大豆或相当量的豆制品。168太空素食含有20余种谷物豆类，可补充日常饮食中谷物豆类摄入不足的问题。";
            }
        }

        #6.饮食习惯--坚果摄入量
        if(isset($questions['nut_intake'])){
            $nut_intake_id = $questions['nut_intake']['question_id'];
            if(!isset($answers[$nut_intake_id])){
                $this->error = '问题未填写完整......';
                return false;
            }
            ## A.每天一小把以上 B.每天一小把以内 C.几乎不吃
            if(in_array('A', $answers[$nut_intake_id]['answer_mark'])) {
                $advice[$questions['nut_intake']['question_cate_id']]['advice'][] = "坚果含有Omega-3不饱和脂肪酸，Omega-3不饱和脂肪酸具有抗炎、降血压、降血脂、舒张血管、抗血栓形成的作用。坚果热量较高，适量摄入即可，每天总共一小把，如果不小心多吃坚果，就要减小一日三餐用油量和饮食量。";
            }
            if(in_array('C', $answers[$nut_intake_id]['answer_mark'])) {
                $advice[$questions['nut_intake']['question_cate_id']]['advice'][] = "坚果含有Omega-3不饱和脂肪酸，Omega-3不饱和脂肪酸具有抗炎、降血压、降血脂、舒张血管、抗血栓形成的作用。建议每周至少使用3次。同时也含有维生素E、磷酯等，具有一定量的蛋白质、微量元素等营养物质。如果饮食安排不方便，可用168太空素食作为营养补给，其中含有20余种坚果籽种，可以提供人体维生素（维生素B、维生素E等）、微量元素（磷、钙、锌、铁）、膳食纤维以及人体必需脂肪酸等。";
            }
        }

        #7.饮食习惯--食用烧烤、油炸、火锅、腌制食物的频率
        if(isset($questions['bad_food_manic'])){
            $bad_food_manic_id = $questions['bad_food_manic']['question_id'];
            if(!isset($answers[$bad_food_manic_id])){
                $this->error = '问题未填写完整.......';
                return false;
            }
            ## A.几乎不吃 B.每月1-2次 C.每月3次以上
            if(in_array('C', $answers[$bad_food_manic_id]['answer_mark'])) {
                $advice[$questions['bad_food_manic']['question_cate_id']]['advice'][] = "食物经过烧烤煎炸腌制会生成致癌物质，使细胞生长的酶发生变异，细胞失去控制生长的能力而发生癌变。长期进食营养密度低的高热量食物很容易引起脂肪堆积，从而导致肥胖，诱发一系列慢性病。长期摄入高胆固醇食物容易导致高血脂、高血压和肥胖，以及心脑血管疾病。建议摄入富含植物甾醇的食物，植物甾醇具有较强的抗炎作用，能够抑制人体对胆固醇的吸收、促进胆固醇的降解代谢、抑制胆固醇的生化合成等。建议尽量避免烧烤煎炸腌制的烹调方式，同时补充抗氧化剂如维生素A、C、E以及螺旋藻等营养素。168太空素食含有50余种蔬菜水果帮助摄入足量的维生素，且含有螺旋藻粉，有抗氧化、抗肿瘤、防癌抑癌作用。一袋168太空素食仅仅只有98大卡，营养丰富，热量很低，控体好搭档，可以替代掉肥肉、奶油、巧克力等高脂肪、高热量但低营养密度的食物。另外，168太空素食成分内含植物甾醇粉，可配合日常食用预防心脑血管疾病。";
            }
        }

        #8.饮食习惯--饮水量
        if(isset($questions['drink_amount'])) {
            $drink_amount_id = $questions['drink_amount']['question_id'];
            if (!isset($answers[$drink_amount_id])) {
                $this->error = '问题未填写完整........';
                return false;
            }
            ## A.<500ML B.500-1500ML C.1500-2000ML D.>2000ML
            if(in_array('A', $answers[$drink_amount_id]['answer_mark']) || in_array('B', $answers[$drink_amount_id]['answer_mark'])) {
                $advice[$questions['drink_amount']['question_cate_id']]['advice'][] = "水是最好的饮品，请注意多喝水，少量多次主动饮水为佳，每天饮水量建议1500-2000ml，如果夏季流汗多，需要额外增加饮水量，同时补充相应的微量元素。";
            }
        }

        #9.饮食习惯--饮水习惯
        if(isset($questions['drink_habit'])) {
            $drink_habit_id = $questions['drink_habit']['question_id'];
            if (!isset($answers[$drink_habit_id])) {
                $this->error = '问题未填写完整.........';
                return false;
            }
            ## A.随时饮水 B.常忘记饮水 C.饮水次数少，每次饮水量很多
            if(in_array('B', $answers[$drink_habit_id]['answer_mark']) || in_array('C', $answers[$drink_habit_id]['answer_mark'])) {
                $advice[$questions['drink_habit']['question_cate_id']]['advice'][] = "水是最好的饮品，请注意多喝水，少量多次主动饮水为佳，每天饮水量建议1500-2000ml，如果夏季流汗多，需要额外增加饮水量，同时补充相应的微量元素。";
            }
        }

        #10.饮食习惯--碳酸饮料
        if(isset($questions['drink_carbonic'])) {
            $drink_carbonic_id = $questions['drink_carbonic']['question_id'];
            if (!isset($answers[$drink_carbonic_id])) {
                $this->error = '问题未填写完整...........';
                return false;
            }
            ## A.是 B.否
            if(in_array('A', $answers[$drink_carbonic_id]['answer_mark'])) {
                $advice[$questions['drink_carbonic']['question_cate_id']]['advice'][] = "易缺钙：168太空素食含有20余种谷物豆类，含有丰富的钙，有助于补充每日所需。";
            }
        }

        #11.饮食习惯--早餐习惯
        if(isset($questions['breakfast_habit'])) {
            $breakfast_habit_id = $questions['breakfast_habit']['question_id'];
            if (!isset($answers[$breakfast_habit_id])) {
                $this->error = '问题未填写完整...........';
                return false;
            }
            ## A.每天规律饮食 B.偶尔不吃 C.经常不吃
            if(in_array('B', $answers[$breakfast_habit_id]['answer_mark']) || in_array('C', $answers[$breakfast_habit_id]['answer_mark'])) {
                $advice[$questions['breakfast_habit']['question_cate_id']]['advice'][] = "易疲劳，有低血糖风险，有胆结石风险";
            }
        }

        #12.运动问题
        if(isset($questions['job_type']) && isset($questions['motion_habit']) && isset($questions['motion_manic'])) {
            $job_type_id = $questions['job_type']['question_id'];
            $motion_habit_id = $questions['motion_habit']['question_id'];
            $motion_manic_id = $questions['motion_manic']['question_id'];
            if (!isset($answers[$job_type_id]) || !isset($answers[$motion_habit_id])) {
                $this->error = '问题未填写完整............';
                return false;
            }
            ## A.轻体力劳动者 B.中体力劳动者 C.重体力劳动者
            ## A.是 B.否
            ## A.每天都运动(每周5-7次） B.每周2-3次
            if(in_array('A', $answers[$job_type_id]['answer_mark']) || in_array('B', $answers[$job_type_id]['answer_mark'])) {
                if(in_array('B', $answers[$motion_habit_id]['answer_mark']))
                    $advice[$questions['job_type']['question_cate_id']]['advice'][] = "因为您缺乏运动，建议您循序渐进地安排适量的适合自己的运动，建议前期从散步、瑜伽等较为柔和的运动做起，然后过渡到跑步、打球等激烈运动，如此循序渐进进行。长期不运动的人，身体一般会比较虚，在运动过程中，身体需要消耗大量能量，所以建议您运动前大于半小时以上进食一些碳水化合物，或者高蛋白质食物。不建议饿着肚子锻炼，如果你是饿着肚子去运动的，可能会由于低血糖而晕倒。";
            }
            if(in_array('C', $answers[$job_type_id]['answer_mark']) && in_array('B', $answers[$motion_habit_id]['answer_mark'])) {
                $advice[$questions['job_type']['question_cate_id']]['advice'][] = "因为您是重体力劳动者，活动强度每天是足够的。当您不工作的情况下，建议您增加适当地额外运动。";
            }
            if(in_array('A', $answers[$motion_habit_id]['answer_mark']) && in_array('A', $answers[$motion_manic_id]['answer_mark'])) {
                $advice[$questions['job_type']['question_cate_id']]['advice'][] = "您拥有良好的运动习惯，很好！经常运动的人最好在运动前1小时，适量补充低GI的碳水化合物，和低脂高蛋白的肉类。比如一些膳食纤维较高的谷物粗粮蔬菜，168里面富含优质的谷物杂粮，比如像藜麦、小米，薏米，燕麦等。也要记得充足大量饮水哦，每天2500ml以上。";
            }
            if(in_array('A', $answers[$motion_habit_id]['answer_mark']) && in_array('B', $answers[$motion_manic_id]['answer_mark'])) {
                $advice[$questions['job_type']['question_cate_id']]['advice'][] = "鉴于您每周大概运动2-3次，故建议您每次运动时间保持至少一个小时，推荐强度选择中等强度或者中高强度的运动。比如像一些无氧运动：抗阻力的肌肉力量训练，间歇性的快跑，俯卧撑等。";
            }
        }

        #13.生活习惯--吸烟
        if(isset($questions['cigarette_habit'])) {
            $cigarette_habit_id = $questions['cigarette_habit']['question_id'];
            if (!isset($answers[$cigarette_habit_id])) {
                $this->error = '问题未填写完整1';
                return false;
            }
            ## A.吸烟 B.不吸烟
            if(in_array('A', $answers[$cigarette_habit_id]['answer_mark'])) {
                $advice[$questions['cigarette_habit']['question_cate_id']]['advice'][] = "吸烟是慢性支气管炎、肺气肿、肺癌的主要诱因之一。长期吸烟可使支气管粘膜的纤毛受损、变短,影响纤毛的清除功能。吸烟者的冠心病、高血压病、脑血管病及周围血管病的发病率均明显升高。168太空素食中所含的维生素A能保护呼吸道和肺的黏膜组织；其中的维生素E和硒能清除自由基，减缓吸烟族的老化速度，还能有效预防癌症的发生。最佳建议--戒烟。";
            }
        }

        #14.生活习惯--饮酒
        if(isset($questions['wine_habit'])) {
            $wine_habit_id = $questions['wine_habit']['question_id'];
            if (!isset($answers[$wine_habit_id])) {
                $this->error = '问题未填写完整2';
                return false;
            }
            ## A.不饮酒 B.偶尔饮酒 C.经常饮酒
            if(in_array('A', $answers[$wine_habit_id]['answer_mark'])) {
                $advice[$questions['wine_habit']['question_cate_id']]['advice'][] = "有脂肪肝的风险。酒精进入身体以后，大多是从肝脏排泄的，对肝脏的危害相当大，会严重损害肝脏细胞，从而导致酒精性肝炎、肝硬化、脂肪肝、肝癌等等疾病的发生。喝酒能够刺激胃黏膜，引起胃炎、胃溃疡、胃癌、结肠癌等消化系统的肿瘤的发生。168太空素食中所含的维生素A能保护消化道及胃的黏膜组织；其中所含的B族维生素能促进酒精的分解,保护肝脏。";
            }
        }

        #15.生活习惯--入睡时间
        if(isset($questions['sleep_time'])) {
            $sleep_time_id = $questions['sleep_time']['question_id'];
            if (!isset($answers[$sleep_time_id])) {
                $this->error = '问题未填写完整3';
                return false;
            }
            ## A.0点以前 B.0点以后
            if(in_array('B', $answers[$sleep_time_id]['answer_mark'])) {
                $advice[$questions['sleep_time']['question_cate_id']]['advice'][] = "经常熬夜容易引起免疫功能失调；会伤害皮肤；引发神经衰弱、头痛失眠等。168太空素食中所含的人参、蛹虫草等药食同源，能补正气，增强免疫力；其中的坚果籽种含有丰富的维生素E，能对抗自由基，保护皮肤，延缓衰老；其中的各种粗粮豆类，富含B族维生素和钙，能起到舒缓神经的作用；GABA氨基丁酸和酸枣仁还能养心安神，帮助睡眠。";
            }
        }

        #16.生活习惯--每天面对电子设备的时间
        if(isset($questions['watch_screen_time'])) {
            $watch_screen_time_id = $questions['watch_screen_time']['question_id'];
            if (!isset($answers[$watch_screen_time_id])) {
                $this->error = '问题未填写完整4';
                return false;
            }
            ## A.三小时以内 B.高于三小时
            if(in_array('B', $answers[$watch_screen_time_id]['answer_mark'])) {
                $advice[$questions['sleep_time']['question_cate_id']]['advice'][] = "过高的电磁辐射污染会对视觉系统造成影响，表现为视力下降，引起白内障等。易导致人的精力和体力减退。对心血管系统和免疫系统也具有一定危害。对生殖系统也会造成危害，主要表现为男子精子质量降低，孕妇发生自然流产和胎儿畸形等。168太空素食原材料极大螺旋藻，是天然的防辐射防护衣。能提高人体抵抗辐射的能力，对辐射引起的白细胞减少有很明显的改善作用。另外，其中的成分维生素A，维生素C，维生素E等都具有抗氧化活性，可以减轻电脑辐射导致的过氧化反应。其中所含的矿物质钙、锌等能提高男性精子活性；所含的叶酸，能预防胎儿畸形。";
            }
        }

        #17.生活习惯--宵夜的频率
        if(isset($questions['night_snack_manic'])) {
            $night_snack_manic_id = $questions['night_snack_manic']['question_id'];
            if (!isset($answers[$night_snack_manic_id])) {
                $this->error = '问题未填写完整5';
                return false;
            }
            ## A.从不 B.每周一、两次 C.每周三次以上 D.每天都会
            if(in_array('B', $answers[$night_snack_manic_id]['answer_mark']) || in_array('C', $answers[$night_snack_manic_id]['answer_mark']) || in_array('D', $answers[$night_snack_manic_id]['answer_mark'])) {
                $advice[$questions['night_snack_manic']['question_cate_id']]['advice'][] = "168太空素食中的B族维生素可以提高肝脏的代谢能力。膳食纤维能刺激肠道蠕动，促进大便的排出；并延缓对糖分吸收的速度。";
            }
        }

        #18.生活习惯--排便
        if(isset($questions['defecation_size']) && isset($questions['defecation_manic'])) {
            $defecation_size_id = $questions['defecation_size']['question_id'];
            $defecation_manic_id = $questions['defecation_manic']['question_id'];
            if (!isset($answers[$defecation_size_id]) || !isset($answers[$defecation_manic_id])) {
                $this->error = '问题未填写完整6';
                return false;
            }
            ##形状 A.干硬 B.香蕉状，表面光滑 C.糊状、液体状
            ##频率 A.每天一至两次 B.小于每天一次
            if(in_array('A', $answers[$defecation_size_id]['answer_mark']) && in_array('B', $answers[$defecation_manic_id]['answer_mark'])) {
                $advice[$questions['defecation_size']['question_cate_id']]['advice'][] = "长期便秘者，容易诱发痔疮，甚至是结肠癌。168太空素食含有丰富的食用真菌，帮助调节肠道功能，改善肠道健康；丰富的膳食纤维，如燕麦、荞麦、黑豆、红豆、红薯、菠菜、西兰花、苹果等具有促进肠道蠕动；含有B族维生素丰富食物如花生、芝麻、核桃等，可促进消化液分泌，维持和促进肠道蠕动；含有胀气因子的产气食物如豆类，帮助促进肠蠕动加快；适量的脂肪能润肠通便，且分解产物脂肪酸有刺激肠蠕动作用，有利于粪便的排出；适宜的碳水化合物及蛋白质能帮助维持正常生理功能；同时需要适当增加运动；每天保证八杯水；建立良好的排便行为，定时排便的习惯，能帮助预防和缓解便秘。";
            }
            if(in_array('C', $answers[$defecation_size_id]['answer_mark'])) {
                $advice[$questions['defecation_size']['question_cate_id']]['advice'][] = "168太空素食含有丰富的食用真菌，帮助调节肠道功能，改善肠道菌群。其中含有的南瓜、丝瓜、芹菜等具有非水溶性纤维素，有利于包裹粪便，结成形。";
            }
        }

        #19.身体状况
        if(isset($questions['body_status'])) {
            $body_status_id = $questions['body_status']['question_id'];
            if (!isset($answers[$body_status_id])) {
                $this->error = '问题未填写完整7';
                return false;
            }
            ## A.过敏 B.乳糖不耐受 C.心脏病x D.痛风 F.胆结石 G.脂肪肝 H.无
            if(in_array('A', $answers[$body_status_id]['answer_mark'])) {
                $advice[$questions['body_status']['question_cate_id']]['advice'][] = "对168太空素食所含成分有过敏者，不建议食用。";
            }
            if(in_array('B', $answers[$body_status_id]['answer_mark'])) {
                $advice[$questions['body_status']['question_cate_id']]['advice'][] = "因168太空素食中含有脱脂乳粉，故乳糖不耐受严重者，不建议食用。";
            }
            if(in_array('D', $answers[$body_status_id]['answer_mark'])) {
                $advice[$questions['body_status']['question_cate_id']]['advice'][] = "痛风急性发作期不能食用本产品。";
            }
            if(in_array('F', $answers[$body_status_id]['answer_mark'])) {
                $advice[$questions['body_status']['question_cate_id']]['advice'][] = "168太空素食中所含丰富的B族维生素，能加速肝脏的代谢。其中还含有不饱和脂肪酸亚油酸，能清除血液中脂质垃圾，降低血液中胆固醇和甘油三酯。从而避免脂类在肝脏的堆积。";
            }
        }

        return compact('advice','food_group_id');
    }

    /**
     * 生成痛点分析
     * @param $questions
     * @param $answers
     * @return array|bool
     */
    public function makePainPointAnalysis($questions, $answers){
        $pain_point = [];
        ##健康目标
        $health_goals_id = $questions['health_goals']['question_id'];
        if (!isset($answers[$health_goals_id])) {
            $this->error = '问题未填写完整.';
            return false;
        }
        ## A.改善皮肤 B.调整情绪 C.增强脑力 D.消除疲劳 E.减脂控体 F.慢病调理 G.增重
        if(in_array('A', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有阿胶、重瓣红玫瑰、葛根、红枣、百合、银耳、针叶樱桃、坚果籽种等。美容养颜、改善气血、延缓衰老，避免各类色斑的形成，娇养肌肤。'
                ],
                'pain_point' => '您存在皮肤问题',
                'analysis' => [
                    '维生素C、维生素E可美白、延缓衰老；',
                    '胶原蛋白，能增加皮肤弹性；',
                    '膳食纤维能清除体内垃圾，避免各类色斑的形成；',
                    '花朵植物，能改善气血，娇养肌肤。',
                ]
            ];
        }
        if(in_array('B', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '甄选168种天然营养食材，所含多种能够改善情绪的植物活性成分。'
                ],
                'pain_point' => '您需要调整情绪',
                'analysis' => [
                    '情绪是主观因素、环境因素、神经-内分泌相互作用的结果。',
                    'B族维生素与钙的缺乏、肠道菌群紊乱，都可能影响情绪。日常可多食用一些富含B族维生素、钙、功能低聚糖的食物。',
                ]
            ];
        }
        if(in_array('C', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有乳清蛋白粉、大豆磷脂、20余种坚果籽种、粗粮豆类。提高脑力劳动效率、增强记忆力和思维能力。'
                ],
                'pain_point' => '您需要增强脑力',
                'analysis' => [
                    '及时给大脑补充足够的蛋白质、不饱和脂肪酸、微量元素等营养物质。'
                ]
            ];
        }
        if(in_array('D', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有玛咖、人参、蛹虫草、螺旋藻、枸杞、黄精、牡蛎、桑葚等。抗疲劳、激活神经、补中益气。'
                ],
                'pain_point' => '您需要消除疲劳',
                'analysis' => [
                    '慢性疲劳是亚健康的一种表现。主要为容易疲劳、休息后不易改善，还伴有记忆力减退、头晕头痛、咽喉不适、关节疼痛、睡眠差、容颜早衰、面色暗淡、皮肤粗糙等多种现象。'
                ]
            ];
        }
        if(in_array('E', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务，科学减脂，健康吃轻松瘦。'
                ],
                'pain_point' => '您需要减脂控体',
                'analysis' => [
                    '肥胖与许多慢性病有关，控制肥胖是减少慢性病发病率和病死率的一个关键因素。'
                ]
            ];
        }
        if(in_array('F', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务，从而科学提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => '您需要调理慢病',
                'analysis' => [
                    '调理慢性病不能仅仅依靠药物，还应当改变不良生活方式、科学饮食、适量运动。'
                ]
            ];
        }
        if(in_array('G', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务，科学控体，增重不增肥。'
                ],
                'pain_point' => '您需要增重',
                'analysis' => [
                    '体重过低除了影响成年人体力，还会导致免疫力低下、月经不调或闭经、骨质疏松、贫血、抑郁等。'
                ]
            ];
        }

        ##急需改善问题
        $sub_health_id = $questions['sub_health']['question_id'];
        if (!isset($answers[$sub_health_id])) {
            $this->error = '问题未填写完整.';
            return false;
        }
        ## A.脱发 B.精神萎靡 C.记忆力下降 D.情绪低落 E.睡眠质量差 F.免疫力下降 G.性功能减退 H.高尿酸 I.肠胃功能紊乱 J.食欲减退 K.低血压 L.低血糖 M.高血压 N.高血糖 O.高血脂 P.多囊 Q.甲减
        if(in_array('A', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有168种天然食材，能提供7大营养素42种营养成分。含有玛咖、葛根等，能调节内分泌。'
                ],
                'pain_point' => '您有脱发的困扰',
                'analysis' => [
                    '脱发的主要因素是内分泌失调，也与头皮营养缺乏有关系。应均衡饮食，多食用富含优质蛋白质、VA、VE、VB、铁、锌的食物。并戒烟限酒、少吃辛辣刺激油腻的食物。'
                ]
            ];
        }
        if(in_array('B', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有人参、阿胶、玛咖、蛹虫草、黑枸杞、黑豆、黑芝麻等。营养丰富，能补中益气、固本培元、提高精力。'
                ],
                'pain_point' => '您需要提高精力',
                'analysis' => [
                    '营养缺乏、气血不足是导致精力下降的主要原因。应补充营养、均衡饮食，多食用一些提高精力的食物。'
                ]
            ];
        }
        if(in_array('C', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有核桃、黑芝麻等20余种坚果籽种，及人参，蜂蜜、大豆磷脂等。为脑细胞提供营养，改善记忆力。'
                ],
                'pain_point' => '您需要提升记忆力',
                'analysis' => [
                    '排除因身体机能老化导致的记忆力衰退。中青年群体出现记忆力下降，可以通过调整饮食结构、补充营养来改善。'
                ]
            ];
        }
        if(in_array('D', $answers[$sub_health_id]['answer_mark']) && in_array('B', $answers[$health_goals_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '甄选168种天然食材，其中含多种能够改善情绪的植物活性成分。'
                ],
                'pain_point' => '您需要调节情绪',
                'analysis' => [
                    '莫名原因的情绪低落，应警惕抑郁症的发生。轻度抑郁者，可以通过调整饮食和生活方式，以及补充某些营养素来缓解。'
                ]
            ];
        }
        if(in_array('E', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有酸枣仁、阿胶、百合、γ-氨基丁酸等。宁心安神、舒缓神经、改善睡眠质量。'
                ],
                'pain_point' => '您需要提高睡眠质量',
                'analysis' => [
                    '失眠、入睡困难、易醒、多梦、睡后疲惫等，是由多种原因引起，也是常见的亚健康问题。'
                ]
            ];
        }
        if(in_array('F', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋168太空素食',
                    'foods' => '168太空素食',
                    'content' => '含有人参、蛹虫草、玛咖、极大螺旋藻、猴头菇、红枣、枸杞、牡蛎、乳清蛋白粉等。提高机体免疫力、增强体质。'
                ],
                'pain_point' => '您需要提升免疫力',
                'analysis' => [
                    '一些慢性疾病、肠道菌群紊乱、某种营养素缺乏，都可能会引起免疫力降低。',
                    '免疫力下降增加了患病几率，并且患病后恢复能力差。'
                ]
            ];
        }
        if(in_array('G', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '选用天然玛咖粉作为原料之一。玛咖被称为天然的荷尔蒙发动机，可以明显改善男女性激素分泌水平。'
                ],
                'pain_point' => '您有性功能减退的困扰',
                'analysis' => [
                    '内分泌失调、不良的生活方式都可能导致性功能减退。'
                ]
            ];
        }
        if(in_array('H', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋168太空素食',
                    'foods' => '168太空素食',
                    'content' => '原料以低嘌呤食物为主。适合痛风缓解期食用，以补充各种营养素。'
                ],
                'pain_point' => '您的尿酸高',
                'analysis' => [
                    '高尿酸血症是一种因嘌呤代谢紊乱而导致的代谢性疾病，如不积极管理，易导致肾脏疾病。'
                ]
            ];
        }
        if(in_array('I', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '粉质细腻好消化。含有猴头菇、鸡内金、小米、山药、茯苓、芡实、蒲公英、桔梗、黄精、菊苣粉、低聚果糖、水苏糖等。养胃健脾、改善肠道功能。'
                ],
                'pain_point' => '您的胃肠功能紊乱',
                'analysis' => [
                    '这是最常见的消化系统疾病。常表现为慢性或反复发作的消化不良、腹痛、腹泻、便秘、恶心等。'
                ]
            ];
        }
        if(in_array('J', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '含有鸡内金、山楂、橘皮、茯苓、灰树花等。促进消化、提高食欲。'
                ],
                'pain_point' => '您的食欲减退',
                'analysis' => [
                    '长期食欲减退，容易导致营养不良，进而出现免疫力下降、消瘦、脸色发黄、皮肤松弛、贫血，四肢无力、容易感冒、便秘等。',
                ]
            ];
        }
        if(in_array('K', $answers[$sub_health_id]['answer_mark']) || in_array('L', $answers[$sub_health_id]['answer_mark']) || in_array('M', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point_str = "";
            if(in_array('K', $answers[$sub_health_id]['answer_mark']))$pain_point_str .= "、血压偏高";
            if(in_array('L', $answers[$sub_health_id]['answer_mark']))$pain_point_str .= "、血糖偏高";
            if(in_array('M', $answers[$sub_health_id]['answer_mark']))$pain_point_str .= "、血脂偏高";
            $pain_point_str = "您的" . trim($pain_point_str,'、');
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务，从而科学提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => $pain_point_str,
                'analysis' => [
                    '三高引起心脑血管疾病发生风险明显增加，特别是冠心病、心梗、脑梗。除了积极配合医生治疗之外，改善生活方式和饮食调理对控制“三高”非常重要。'
                ]
            ];
        }
        if(in_array('N', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务，从而科学提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => '您有脂肪肝',
                'analysis' => [
                    '脂肪肝分为酒精性脂肪肝和非酒精性脂肪肝。',
                    '酒精性脂肪肝主要是由于长期喝酒或大量酗酒导致。非酒精性脂肪肝主要与身体肥胖、某些代谢综合症有关。',
                    '诸多研究结果证明，脂肪肝可能发展为肝炎、肝纤维化、肝硬化和肝癌。',
                    '伴随肥胖的非酒精性脂肪肝患者，体重下降3-5%就能改善症状，体重下降7-10%可以使血清转氨酶降至正常水平，并可改善肝纤维化。',
                ]
            ];
        }
        if(in_array('O', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比，提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => '您血压偏低',
                'analysis' => [
                    '长期低血压会使身体的各项机能下降，从而出现乏力、头痛、头晕、食欲不振、视听能力下降。',
                    '严重的低血压可诱发心脑梗、心肌缺血、休克等危及生命。',
                ]
            ];
        }
        if(in_array('P', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比，提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => '您血糖偏低',
                'analysis' => [
                    '低血糖有心悸、大汗、饥饿、乏力、神志改变的现象。持续时间较长的低血糖，可导致不可逆转的脑损伤，和死亡风险。',
                ]
            ];
        }
        if(in_array('Q', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比+低GI+低热量+高营养+均衡膳食+专业营养师服务，从而科学提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => '您有“多囊”（PCOS）',
                'analysis' => [
                    'PCOS不仅会导致不孕，远期发生糖尿病、高血压、子宫内膜癌等风险比正常人高，严重影响生命质量。调整生活方式是PCOS的基础治疗。',
                ]
            ];
        }
        if(in_array('R', $answers[$sub_health_id]['answer_mark'])) {
            $pain_point[] = [
                'resolve' => [
                    'dosage' => '每天2-3袋',
                    'foods' => '168太空素食',
                    'content' => '应用航天育种科研成果技术，甄选168种天然食材，通过科学配比，提供人体细胞所需营养，调整细胞新陈代谢，提高机体的自我修复能力，由内而外改善身体健康。'
                ],
                'pain_point' => '您有“甲减”',
                'analysis' => [
                    '甲减会导致机体代谢降低、畏寒、乏力、苍白虚肿、皮肤干燥增厚、声音嘶哑、记忆力减退、心动过缓、便秘等。
严重影响正常的工作、学习、生活，应及时就医，并积极配合饮食调理。',
                ]
            ];
        }
        return $pain_point;
    }

    /**
     * 生成健康建议
     * @param $questions
     * @param $answers
     * @return array[]|bool
     */
    public function makeAdvice($questions, $answers){
        $advice = [
            'eat_habit' => [
                'title' => '饮食习惯',
                'advice' => []
            ],
            'life_habit' => [
                'title' => '生活习惯',
                'advice' => []
            ],
            'body_status' => [
                'title' => '身体情况',
                'advice' => []
            ],
        ];
        ##1.饮食习惯

        ##》》》饮食习惯
        if(isset($questions['diet_prefer'])){
            $diet_prefer_id = $questions['diet_prefer']['question_id'];
            if (!isset($answers[$diet_prefer_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.荤素均衡 B.荤食为主 C.素食为主 D.嗜盐 E.嗜油 F.嗜糖 G.纯素食
            if(in_array('B', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "注意选择脂肪含量较低的肉类，比如瘦牛肉、兔肉、鸡胸肉、鱼虾等。168太空素食富含50余种蔬菜水果、20余种谷物豆类，可以补充荤食偏好者日常饮食所缺乏的维生素和膳食纤维。";
            }
            if(in_array('C', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "偏素食人群的缺铁风险较高。动物性食物（如肉类）中含有较多的铁，相比植物中的铁也更容易被人体吸收。建议日常饮食中增加一些瘦肉、动物肝、动物血、蛋黄、豆类、芝麻等含铁丰富食物。";
            }
            if(in_array('D', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "高盐饮食容易诱发高血压、骨质疏松、慢性胃炎等多种疾病。减少盐的摄入，每日摄入量不超过6g。少吃过咸的食品，如：午餐肉、腌制食物等。";
            }
            if(in_array('E', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "油脂摄入较多易热量过剩，引起肥胖、高脂血症、脂肪肝等。建议减少烹调用油，并选择优质油脂，如：橄榄油、亚麻籽油、红花籽油、山茶籽油等。";
            }
            if(in_array('F', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "高糖饮食容易引起肥胖和高血糖。少食用甜食与甜饮料，主食建议粗细搭配。";
            }
            if(in_array('G', $answers[$diet_prefer_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "素食人群多缺乏蛋白质和各种矿物质。日常应适当增加豆类及豆制品的食用量。建议每天2-3袋168太空素食，补充各种营养素。";
            }
        }

        ##》》》用餐习惯
        if(isset($questions['eat_habit'])) {
            $eat_habit_id = $questions['eat_habit']['question_id'];
            if (!isset($answers[$eat_habit_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.常吃快餐，饮食单一 B.暴饮暴食 C.三餐饮食规律 D.常吃零食 E.在外就餐多 F.进餐速度快(小于15分钟)
            if(in_array('A', $answers[$eat_habit_id]['answer_mark']) || in_array('E', $answers[$eat_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "在外就餐往往饮食单一，不易控制油盐糖摄入量，容易热量过剩，造成营养失衡或肥胖。建议外出就餐前，先吃1袋168太空素食，替代主食，有利于控制总热量，均衡营养。";
            }
            if(in_array('B', $answers[$eat_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "长期的暴饮暴食，会增加肠胃负担，引发消化功能紊乱，且容易发胖。建议从定量饮食做起。可食用富含维生素B族和功能低聚糖的食物，以改善肠道功能，促进能量代谢。";
            }
            if(in_array('D', $answers[$eat_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "零食往往是高热量低营养密度的食物，过多摄入容易发胖。建议优选高蛋白高纤维的零食，如：风干牛肉、豆腐干、泡椒竹笋、坚果等，并且限量食用。";
            }
            if(in_array('F', $answers[$eat_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "进食速度过快会加重肠胃负担，还容易摄入过多热量，导致肥胖。一定要细嚼慢咽，每餐进食时间在20分钟以上为宜。";
            }
        }

        ##》》》早餐习惯
        if(isset($questions['breakfast_habit'])) {
            $breakfast_habit_id = $questions['breakfast_habit']['question_id'];
            if (!isset($answers[$breakfast_habit_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.每天规律饮食 B.偶尔不吃 C.经常不吃
            if(in_array('B', $answers[$breakfast_habit_id]['answer_mark']) || in_array('C', $answers[$breakfast_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "不吃早餐容易诱发胆结石、慢性胃炎，易发生低血糖。而且还容易导致中晚餐过量进食，发生肥胖。";
            }
        }

        ##》》》宵夜的频率
        if(isset($questions['night_snack_manic'])) {
            $night_snack_manic_id = $questions['night_snack_manic']['question_id'];
            if (!isset($answers[$night_snack_manic_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.从不 B.每周1-2次 C.每周三次以上 D.每天都会
            if(in_array('B', $answers[$night_snack_manic_id]['answer_mark']) || in_array('C', $answers[$night_snack_manic_id]['answer_mark']) || in_array('D', $answers[$night_snack_manic_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "减少宵夜次数。经常吃宵夜导致热量过剩，引起肥胖，并且会加重肝肾的代谢负担。";
            }
        }

        ##》》》您使用烧烤、油炸、火锅、腌制食物的频率
        if(isset($questions['bad_food_manic'])) {
            $bad_food_manic_id = $questions['bad_food_manic']['question_id'];
            if (!isset($answers[$bad_food_manic_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.几乎不吃 B.每月1-2次 C.每月三次以上
            if(in_array('C', $answers[$bad_food_manic_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "尽量避免食用烧烤、油炸、腌制食物。这类食物在烹调过程中容易生成致癌物质，诱发癌症。且这类食物往往高油高盐高热量，长期食用容易发生肥胖，诱发一系列慢性疾病。";
            }
        }

        ##》》》您平均每天坚果的摄入量是
        if(isset($questions['nut_intake'])) {
            $nut_intake_id = $questions['nut_intake']['question_id'];
            if (!isset($answers[$nut_intake_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.每天一小把以上 B.每天一小把以内 C.几乎不吃
            if(in_array('A', $answers[$nut_intake_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "坚果营养丰富，但脂肪含量较高，过量摄入容易引起肥胖。建议每天摄入25g即可。如果当天摄入超量，则需要减少一部分饮食热量。";
            }
            if(in_array('C', $answers[$nut_intake_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "坚果含有丰富的VE、磷脂、矿物质，并且所含的不饱和脂肪酸具有降血压、降血脂、舒张血管、抗血栓的作用。建议每天食用25g坚果。";
            }
        }

        ##》》》您吃粗粮豆类的频率是
        if(isset($questions['eat_bean_manic'])) {
            $eat_bean_manic_id = $questions['eat_bean_manic']['question_id'];
            if (!isset($answers[$eat_bean_manic_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.1周0-2次 B.1周3-6次 C.1周7次以上
            if(in_array('A', $answers[$eat_bean_manic_id]['answer_mark']) || in_array('B', $answers[$eat_bean_manic_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "粗粮是膳食纤维和维生素B族的良好来源。豆类及豆制品能够给我们提供优质蛋白、大豆磷脂、大豆异黄酮、植物固醇等营养物质。建议每天食用2-3袋168太空素食。所含20余种谷物豆类，能丰富您的膳食营养。";
            }
        }

        ##》》》每日饮水量 && 饮水习惯
        if(isset($questions['drink_amount']) && isset($questions['drink_habit'])) {
            $drink_amount_id = $questions['drink_amount']['question_id'];
            $drink_habit_id = $questions['drink_habit']['question_id'];
            if (!isset($answers[$drink_amount_id]) || !isset($answers[$drink_habit_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.＜500ml B.500-1500ml C.1500-2000ml D.＞2000ml
            ## A.随时饮水 B.常忘记饮水 C.饮水次数少，每次饮水量很多
            if(in_array('A', $answers[$drink_amount_id]['answer_mark']) || in_array('B', $answers[$drink_amount_id]['answer_mark']) || in_array('B', $answers[$drink_habit_id]['answer_mark']) || in_array('C', $answers[$drink_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "白开水是最好的饮品。健康人群每天保持1500-1700ml以上饮水量，减脂人群建议达到2000ml以上。注意少量多次，小口均匀慢饮。";
            }
        }

        ##》》》是否常喝碳酸饮料
        if(isset($questions['drink_carbonic'])) {
            $drink_carbonic_id = $questions['drink_carbonic']['question_id'];
            if (!isset($answers[$drink_carbonic_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.是 B.否
            if(in_array('A', $answers[$drink_carbonic_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "常喝碳酸饮料，容易引起钙的丢失。减少碳酸饮料的摄入，并尽量选择零卡路里的饮品。";
            }
        }

        ##》》》饮酒情况
        if(isset($questions['wine_habit'])) {
            $wine_habit_id = $questions['wine_habit']['question_id'];
            if (!isset($answers[$wine_habit_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.不饮酒 B.偶尔饮酒 C.经常饮酒
            if(in_array('C', $answers[$wine_habit_id]['answer_mark'])) {
                $advice['eat_habit']['advice'][] = "长期饮酒者，有酒精性脂肪肝的风险，并且记忆力容易衰退。酒精还会刺激胃黏膜，引起慢性胃炎、胃溃疡。建议限制饮酒量。";
            }
        }

        ##2.生活习惯

        ##》》》每晚入睡时间
        if(isset($questions['sleep_time'])) {
            $sleep_time_id = $questions['sleep_time']['question_id'];
            if (!isset($answers[$sleep_time_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.0点以前 B.0点以后
            if(in_array('B', $answers[$sleep_time_id]['answer_mark'])) {
                $advice['life_habit']['advice'][] = "经常熬夜容易引起免疫功能失调，引发神经衰弱、头痛失眠等。尽量作息规律，避免在睡前暴饮暴食、剧烈运动、长时间看电子屏幕。睡前可做一些轻松的瑜伽或听舒缓的音乐放松心情，睡觉环境安静避光。可食用一些有助于睡眠的食物。";
            }
        }

        ##》》》每天面对电子设备的时间
        if(isset($questions['watch_screen_time'])) {
            $watch_screen_time_id = $questions['watch_screen_time']['question_id'];
            if (!isset($answers[$watch_screen_time_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.三小时以内 B.高于三小时
            if(in_array('B', $answers[$watch_screen_time_id]['answer_mark'])) {
                $advice['life_habit']['advice'][] = "长时间使用电子设备，会引起视力下降、眼睛干涩、颈椎问题等。建议站坐交替工作，使用电子设备超过1小时后，休息10分钟，以缓解疲劳。";
            }
        }

        ##》》》运动习惯
        if(isset($questions['motion_habit']) && isset($questions['motion_manic'])) {
            $motion_habit_id = $questions['motion_habit']['question_id'];
            $motion_manic_id = $questions['motion_manic']['question_id'];
            if (!isset($answers[$motion_habit_id]) || !isset($answers[$motion_manic_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.是 B.否
            ## A.每周1-3次 B.每周4-7次
            if(in_array('B', $answers[$motion_habit_id]['answer_mark'])) {
                $advice['life_habit']['advice'][] = "经常运动能增强心肺功能、增强免疫力、协调力、抗压能力等。建议您增加有氧运动，短期目标2-3次/周，长期目标增至5次/周，每次至少30分钟，例如健身操、游泳、慢跑等；无氧训练2-3次/周，例如举哑铃、卷腹等。";
            }
            if(in_array('A', $answers[$motion_habit_id]['answer_mark']) && in_array('A', $answers[$motion_manic_id]['answer_mark'])) {
                $advice['life_habit']['advice'][] = "可适当增加有氧运动和无氧运动。有氧运动至少5次/周，每次至少30分钟，例如健身操、游泳、慢跑；无氧训练至少2-3次/周，例如举哑铃、卷腹等。";
            }
        }

        ##》》》排便
        if(isset($questions['shitshape']) && isset($questions['shitrate'])) {
            $shitshape_id = $questions['shitshape']['question_id'];
            $shitrate_id = $questions['shitrate']['question_id'];
            if (!isset($answers[$shitshape_id]) || !isset($answers[$shitrate_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.干硬 B.香蕉状，表面光滑 C.糊状、液体状
            ## A.每天一至两次 B.小于每天一次
            if(in_array('A', $answers[$shitshape_id]['answer_mark']) || in_array('B', $answers[$shitrate_id]['answer_mark'])) {
                $advice['life_habit']['advice'][] = "改善便秘，建议多喝水，并增加膳食纤维的摄入。海带、豆类、菌菇类、绿叶蔬菜，都是很好的膳食纤维来源。养成每日定时排便的习惯，睡前可以进行腹部按摩。";
            }
            if(in_array('C', $answers[$shitshape_id]['answer_mark'])) {
                $advice['life_habit']['advice'][] = "日常的饮食习惯和肠道菌群失调，都可能引起大便稀溏或腹泻。少吃高糖高脂饮食，多吃富含膳食纤维、维生素、矿物质的食物。腹泻期间多喝水，饮食要清淡好消化，避免生冷刺激和油腻。";
            }
        }

        ##》》》身体情况
        if(isset($questions['body_status'])) {
            $body_status_id = $questions['body_status']['question_id'];
            if (!isset($answers[$body_status_id])) {
                $this->error = '问题未填写完整.';
                return false;
            }
            ## A.过敏 B.乳糖不耐受 D.痛风 E.肾结石 F.脂肪肝
            if(in_array('A', $answers[$body_status_id]['answer_mark'])) {
                $advice['body_status']['advice'][] = "对168太空素食所含成分有过敏者，不建议食用。";
            }
            if(in_array('B', $answers[$body_status_id]['answer_mark'])) {
                $advice['body_status']['advice'][] = "168太空素食含脱脂乳粉，严重乳糖不耐受者，不建议食用。";
            }
            if(in_array('D', $answers[$body_status_id]['answer_mark'])) {
                $advice['body_status']['advice'][] = "痛风缓解期，建议每天2袋168太空素食。痛风发作期不能食用本品。";
            }
        }
        return $advice;
    }

}