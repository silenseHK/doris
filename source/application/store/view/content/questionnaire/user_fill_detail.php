<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>
    .am-form-label{
        color:#F37B1D !important;
    }
    [v-cloak] {
        display: none;
    }
    .wrap-form{
        position:relative;
    }
    .mask{
        width:100%;
        height:100%;
        position: absolute;
        z-index:1000;
        top:0;
    }
</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf" id="wrap" v-cloak>
                <el-row>
                    <el-col :span="2" style="height: 1px;"></el-col>
                    <el-col :span="12">
                        <el-form class="wrap-form" :rules="rules" :label-position="labelPosition" label-width="280px">

                            <el-form-item v-for="item in questions" :label="item.question.label" :prop="item.question.name">
                                <!-- 填空 -->
                                <el-input v-if="item.question.type.value == 10 && item.is_show" v-model="item.answer"></el-input>

                                <!-- 单选 -->
                                <el-radio-group v-if="item.question.type.value == 20 && item.is_show" v-model="item.answer_mark" size="small">
                                    <el-radio v-for="it in item.question.option" :label="it.mark" border>{{it.label}}</el-radio>
                                </el-radio-group>

                                <!-- 多选 -->
                                <el-checkbox-group v-if="item.question.type.value == 30 && item.is_show" v-model="item.answer_mark">
                                        <el-checkbox v-for="it in item.question.option" v-if="it.is_input == 0" :label="it.mark" name="">{{it.label}}</el-checkbox>

                                        <el-checkbox v-for="it in item.question.option" v-if="it.is_input == 1" :label="it.mark" name="">
                                            {{item.answer?'':it.label}}
                                            <el-input
                                                    v-if="item.answer"
                                                    placeholder="请输入内容"
                                                    v-model="item.answer"
                                                    size="small">
                                            </el-input>
                                        </el-checkbox>
                                </el-checkbox-group>

                                <!--文本框-->
                                <el-input
                                        v-if="item.question.type.value == 40 && item.is_show"
                                        type="textarea"
                                        :autosize="{ minRows: 2, maxRows: 4}"
                                        placeholder="请输入内容"
                                        v-model="item.answer">
                            </el-form-item>

                            <div class="mask" v-show="mode=='read'"></div>

                        </el-form>

                        <el-button v-if="mode=='read'" @click="changeMode('write')" type="primary" icon="el-icon-edit" circle ></el-button>
                        <el-button v-if="mode=='write'" @click="changeMode('read')" type="success" icon="el-icon-check" circle ></el-button>
                    </el-col>

                </el-row>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>

<script>
    var App;
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        App = new Vue({
            el : "#wrap",
            data : {
                title: '',
                questionnaire_no: '',
                status: 1,
                questions:<?= json_encode($data['user_answer']) ?>,

                labelPosition: 'top',
                rules: {
                    'name' : [{ required: true, message: '', trigger: 'blur' }]
                },
                mode: 'read'
            },
            methods : {
                drag: function(){},
                initData: function(){
                    let that = this;
                    let rules = {};
                    this.questions.forEach(function(v, k){
                        if(v.question.type.value == 20)that.questions[k].answer_mark = v.answer_mark.join('');
                        if(v.question.is_require){
                            rules[v.question.name] = [{ required: true, message: '123', trigger: 'blur' }];
                        }
                    })
                    this.rules = rules;
                },
                changeMode: function(mode){
                    this.mode = mode;
                }
            },

            mounted: function(){
                this.initData();
            }
        })

    });
</script>
