<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">问卷信息</div>
                            </div>

                            <el-row>
                                <el-link target="_blank" style="margin-bottom: 15px;">基础信息</el-link>
                            </el-row>

                            <el-row>
                                <el-form :model="ruleForm" ref="ruleForm" label-width="100px" class="demo-ruleForm" style="width:500px;">

                                    <el-form-item label="标题" prop="title">
                                        <el-input v-model="ruleForm.title" maxlength="50" show-word-limit></el-input>
                                    </el-form-item>

                                    <el-form-item label="编号" prop="questionnaire_no">
                                        <el-input v-model="ruleForm.questionnaire_no" maxlength="50" show-word-limit></el-input>
                                    </el-form-item>

                                    <el-form-item label="状态" prop="status">
                                        <el-switch v-model="ruleForm.status" active-value="1" inactive-value="2" active-text="上线" inactive-text="下线"></el-switch>
                                    </el-form-item>

                                </el-form>
                            </el-row>

                            <el-row>
                                <el-link target="_blank" style="margin-bottom: 15px;">问卷问题</el-link>
                            </el-row>

                            <el-row>

                                <el-collapse v-model="activeName" style="margin-left: 100px; width:600px;">
                                    <draggable  v-model="questions" @end="drag" :options="{delay:30,touchStartThreshold: 1,preventOnFilter: false,animation:300,chosenClass:'sortable-chosen',forceFallback:true,fallbackOnBody:false,scroll:true,scrollSensitivity:120,filter: '.undraggable'}">
                                    <el-collapse-item v-for="(item, index) in questions" :title="item.title" :name="index">
                                        <el-form label-width="100px" class="demo-ruleForm" style="width:700px;padding: 20px 0 0 0;">

                                            <draggable  v-model="item.questions" @end="drag" :options="{delay:30,touchStartThreshold: 1,preventOnFilter: false,animation:300,chosenClass:'sortable-chosen',forceFallback:true,fallbackOnBody:false,scroll:true,scrollSensitivity:120,filter: '.undraggable'}">
                                            <el-template v-for="(it, idx) in item.questions">

                                                <el-form-item v-if="it.type == 10" :label="it.label">
                                                    <el-row>
                                                        <el-col :span="18">
                                                            <el-input v-model="input" maxlength="50"></el-input>
                                                        </el-col>
                                                        <el-col :span="4">
                                                            <el-tooltip class="item" effect="dark" :content="it.show_limit_txt" placement="right">
                                                                <i style="cursor: pointer;" class="el-icon-setting" @click="showLimitList(index, idx)"></i>
                                                            </el-tooltip>
                                                            <i style="cursor: pointer;" class="el-icon-delete" @click="delQuestion(index, idx)"></i>
                                                        </el-col>
                                                    </el-row>
                                                </el-form-item>


                                                <el-form-item v-if="it.type == 20" :label="it.label">
                                                    <el-row>
                                                        <el-col :span="18">
                                                            <el-radio-group v-model="radio" size="mini">
                                                                <el-radio style="margin-right: 0px;" v-for="(v, k) in it.option" :label="k" border>{{v.label}}</el-radio>
                                                            </el-radio-group>
                                                        </el-col>
                                                        <el-col :span="4">
                                                            <el-tooltip class="item" effect="dark" :content="it.show_limit_txt" placement="right">
                                                                <i style="cursor: pointer;" class="el-icon-setting" @click="showLimitList(index, idx)"></i>
                                                            </el-tooltip>
                                                            <i style="cursor: pointer;" class="el-icon-delete" @click="delQuestion(index, idx)"></i>
                                                        </el-col>
                                                    </el-row>

                                                </el-form-item>

                                                <el-form-item v-if="it.type == 30" :label="it.label">
                                                    <el-row>
                                                        <el-col :span="18">
                                                            <el-checkbox-group v-model="checkbox" size="mini">
                                                                <el-checkbox style="margin-right: 0px;" v-for="(v, k) in it.option" :label="k" border>{{v.label}}</el-checkbox>
                                                            </el-checkbox-group>
                                                        </el-col>
                                                        <el-col :span="4">
                                                            <el-tooltip class="item" effect="dark" :content="it.show_limit_txt" placement="right">
                                                                <i style="cursor: pointer;" class="el-icon-setting" @click="showLimitList(index, idx)"></i>
                                                            </el-tooltip>
                                                            <i style="cursor: pointer;" class="el-icon-delete" @click="delQuestion(index, idx)"></i>
                                                        </el-col>
                                                    </el-row>
                                                </el-form-item>

                                                <el-form-item v-if="it.type == 40" :label="it.label">
                                                    <el-row>
                                                        <el-col :span="18">
                                                            <el-input
                                                                    type="textarea"
                                                                    :autosize="{ minRows: 2, maxRows: 4}"
                                                                    placeholder="请输入内容"
                                                                    v-model="text">
                                                            </el-input>
                                                        </el-col>
                                                        <el-col :span="4">
                                                            <el-tooltip class="item" effect="dark" :content="it.show_limit_txt" placement="right">
                                                                <i style="cursor: pointer;" class="el-icon-setting" @click="showLimitList(index, idx)"></i>
                                                            </el-tooltip>
                                                            <i style="cursor: pointer;" class="el-icon-delete" @click="delQuestion(index, idx)"></i>
                                                        </el-col>
                                                    </el-row>
                                                </el-form-item>
                                            </el-template>
                                            </draggable>

                                            <el-button style="margin: 20px 0 0 60px;" type="success" plain size="small" @click="showQuestionList(index)">添加问题</el-button>
                                        </el-form>
                                    </el-collapse-item>
                                    </draggable>
                                </el-collapse>


                                <el-button style="margin: 20px 0 0 60px;" type="success" plain size="small" @click="showCateList">添加分类</el-button>

                            </el-row>

                            <el-row>
                                <el-button type="primary" style="margin-top: 20px;" @click="submit">提交问卷</el-button>
                            </el-row>

                        </fieldset>

                        <!-- 分类 -->
                        <el-drawer
                                title="我是标题"
                                :visible.sync="drawer"
                                :with-header="false">
                                <el-table
                                        style="padding: 20px;"
                                        ref="multipleTable"
                                        :data="cate_list"
                                        tooltip-effect="dark"
                                        style="width: 100%"
                                        @selection-change="handleSelectionChange">
                                    <el-table-column
                                            type="selection"
                                            width="55">
                                    </el-table-column>
                                    <el-table-column
                                            label="ID"
                                            width="120">
                                        <template slot-scope="scope">{{ scope.row.cate_id }}</template>
                                    </el-table-column>
                                    <el-table-column
                                            prop="title"
                                            label="分类名"
                                            width="120">
                                    </el-table-column>
                                    <el-table-column
                                            prop="alias"
                                            label="别名"
                                            show-overflow-tooltip>
                                    </el-table-column>
                                </el-table>
                                <div style="margin-top: 20px;padding:20px;">
                                    <el-button @click="addCate()">确定选择</el-button>
                                </div>
                        </el-drawer>

                        <!-- 问题 -->
                        <el-drawer
                                title="我是标题"
                                :visible.sync="question_drawer"
                                :with-header="false">
                            <el-table
                                    style="padding: 20px;"
                                    ref="multipleTable"
                                    :data="question_list"
                                    tooltip-effect="dark"
                                    style="width: 100%"
                                    @selection-change="handleSelectionChange2">
                                <el-table-column
                                        type="selection"
                                        width="55">
                                </el-table-column>
                                <el-table-column
                                        label="ID"
                                        width="120">
                                    <template slot-scope="scope">{{ scope.row.question_id }}</template>
                                </el-table-column>
                                <el-table-column
                                        prop="label"
                                        label="问题"
                                        width="120">
                                </el-table-column>
                                <el-table-column
                                        prop="name"
                                        label="name"
                                        width="120">
                                </el-table-column>
                                <el-table-column
                                        prop="type"
                                        label="类型"
                                        width="120"
                                        show-overflow-tooltip>
                                </el-table-column>
                            </el-table>

                            <el-pagination
                                    style="padding: 20px;"
                                    background
                                    :page-size="10"
                                    :pager-count="7"
                                    layout="prev, pager, next"
                                    @current-change="initQuestionList2"
                                    :total="question_total">
                            </el-pagination>

                            <div style="margin-top: 20px;padding:20px;">
                                <el-button @click="addQuestion()">确定选择</el-button>
                            </div>
                        </el-drawer>

                        <!-- 出现条件 -->
                        <el-drawer
                                title="设置出现条件"
                                :visible.sync="limit_wrap_drawer"
                                :wrapperClosable="false"
                                :with-header="false"
                                :show-close="true"
                                size="40%">
                            <div>
                                <el-table
                                        style="padding: 20px;"
                                        ref="multipleTable"
                                        :data="limit_question_list"
                                        tooltip-effect="dark"
                                        style="width: 100%"
                                        @selection-change="handleSelectionChange2">
                                    <el-table-column
                                            label="ID"
                                            width="120">
                                        <template slot-scope="scope">{{ scope.row.question_id }}</template>
                                    </el-table-column>
                                    <el-table-column
                                            prop="label"
                                            label="问题"
                                            width="120">
                                    </el-table-column>
                                    <el-table-column
                                            prop="name"
                                            label="name"
                                            width="120">
                                    </el-table-column>
                                    <el-table-column
                                            prop="type"
                                            label="类型"
                                            width="120"
                                            >
                                    </el-table-column>
                                    <el-table-column
                                            prop="type"
                                            label="操作"
                                            width="120"
                                            show-overflow-tooltip>
                                        <template slot-scope="scope">
                                            <el-link type="success" @click="showMarkList(scope.row)">设置</el-link>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <el-button style="margin:20px 0 0 60px;" @click="cancelShowLimit">无条件</el-button>
                                <el-drawer
                                        title="设置出现条件"
                                        :append-to-body="true"
                                        :with-header="false"
                                        :wrapperClosable="false"
                                        :before-close="handleClose"
                                        :visible.sync="limit_drawer">
                                    <el-table
                                            style="padding: 20px;"
                                            ref="multipleTable"
                                            :data="limit_option.option"
                                            tooltip-effect="dark"
                                            style="width: 100%"
                                            @selection-change="handleSelectionChange3">
                                        <el-table-column
                                                type="selection"
                                                width="55">
                                        </el-table-column>
                                        <el-table-column
                                                label="MARK"
                                                width="120">
                                            <template slot-scope="scope">{{ scope.row.mark }}</template>
                                        </el-table-column>
                                        <el-table-column
                                                prop="label"
                                                label="选项"
                                                width="120">
                                        </el-table-column>
                                    </el-table>
                                    <el-button style="margin:20px 0 0 60px;" @click="setShowLimit">设置</el-button>
                                    <el-button style="margin:20px 0 0 60px;" @click="disSetShowLimit">取消</el-button>
                                </el-drawer>
                            </div>
                        </el-drawer>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/vue.min.js"></script>
<script src="assets/common/js/Sortable.min.js?v=<?= $version ?>"></script>
<script src="assets/common/js/vuedraggable.min.js?v=<?= $version ?>"></script>
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
            el : "#my-form",
            data : {
                multipleSelection: [],
                multipleSelection2: [],
                multipleSelection3: [],
                ruleForm: {
                    title: '',
                    questionnaire_no: '',
                    status: 1,
                },
                questions:[
                    // {
                    //     cate_id: 1,
                    //     title: '基本信息',
                    //     questions:[
                    //         // {
                    //         //     question_id: 3,
                    //         //     label: '性别',
                    //         //     type: 20,
                    //         //     is_require: 1,
                    //         //     icon: '',
                    //         //     options:[
                    //         //         {
                    //         //             mark: 'A',
                    //         //             label: '男',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         },
                    //         //         {
                    //         //             mark: 'B',
                    //         //             label: '女',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         }
                    //         //     ],
                    //         //     show_limit: [
                    //         //         {
                    //         //             question_id: 3,
                    //         //             mark: 'A'
                    //         //         }
                    //         //     ],
                    //         //     show_limit_txt: '设置出现条件'
                    //         // },
                    //         // {
                    //         //     question_id: 4,
                    //         //     label: '年龄',
                    //         //     type: 10,
                    //         //     is_require: 1,
                    //         //     icon: '',
                    //         //     options: [],
                    //         //     show_limit: [
                    //         //         {
                    //         //             question_id: 3,
                    //         //             mark: 'A'
                    //         //         }
                    //         //     ],
                    //         //     show_limit_txt: '性别:男'
                    //         // },
                    //         // {
                    //         //     question_id: 5,
                    //         //     label: '爱好',
                    //         //     type: 30,
                    //         //     is_require: 1,
                    //         //     icon: '',
                    //         //     options:[
                    //         //         {
                    //         //             mark: 'A',
                    //         //             label: '足球',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         },
                    //         //         {
                    //         //             mark: 'B',
                    //         //             label: '篮球',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         },
                    //         //         {
                    //         //             mark: 'C',
                    //         //             label: '乒乓球',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         }
                    //         //     ],
                    //         //     show_limit: [
                    //         //         {
                    //         //             question_id: 3,
                    //         //             mark: 'A'
                    //         //         }
                    //         //     ],
                    //         //     show_limit_txt: '性别:男'
                    //         // },
                    //         // {
                    //         //     question_id: 6,
                    //         //     label: '意见建议',
                    //         //     type: 40,
                    //         //     is_require: 0,
                    //         //     icon: '',
                    //         //     options:[
                    //         //         {
                    //         //             mark: 'A',
                    //         //             label: '男',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         },
                    //         //         {
                    //         //             mark: 'B',
                    //         //             label: '女',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         }
                    //         //     ],
                    //         //     show_limit: [
                    //         //         {
                    //         //             question_id: 3,
                    //         //             mark: 'A'
                    //         //         }
                    //         //     ],
                    //         //     show_limit_txt: '性别:男'
                    //         // },
                    //     ]
                    // },
                    // {
                    //     cate_id: 2,
                    //     title: '健康目标',
                    //     questions:[
                    //         // {
                    //         //     question_id: 3,
                    //         //     label: '性别',
                    //         //     type: 20,
                    //         //     is_require: 1,
                    //         //     icon: '',
                    //         //     options:[
                    //         //         {
                    //         //             mark: 'A',
                    //         //             label: '男',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         },
                    //         //         {
                    //         //             mark: 'B',
                    //         //             label: '女',
                    //         //             is_input: 0,
                    //         //             icon: ''
                    //         //         }
                    //         //     ],
                    //         //     show_limit: [
                    //         //         {
                    //         //             question_id: 3,
                    //         //             mark: 'A'
                    //         //         }
                    //         //     ],
                    //         //     show_limit_txt: '性别:男'
                    //         // }
                    //     ]
                    // }
                ],
                activeName: '1',
                checkbox:[],
                radio:'',
                input:'',
                text:'',
                drawer: false,
                cate_list: [],
                question_drawer: false,
                question_list: [],
                question_total: 0,
                idx: 0,
                limit_wrap_drawer: false,
                limit_drawer: false,
                limit_question_list: [],
                limit_option: [],
                limit_index: 0,
                limit_idx: 0,

            },
            methods : {
                drag:function(e){
                    console.log(this.questions);
                },
                delOption: function(e, index){
                    this.questions.splice(index,1)
                },

                submit: function(){
                    let [title, status, questions, questionnaire_no] = [this.ruleForm.title, this.ruleForm.status, this.questions, this.ruleForm.questionnaire_no]
                    if(!title || !status || !questionnaire_no || questions.length <= 0){
                        layer.msg('请将数据补充完整', {icon:2})
                        return false;
                    }
                    let questions_post = [];
                    questions.forEach((v, k)=>{
                        let question_list = [];
                        v.questions.forEach((vv, kk)=>{
                            let question = {
                                question_id: vv.question_id,
                                show_limit: vv.show_limit,
                            }
                            question_list.push(question)
                        })
                        questions_post.push({
                            cate_id: v.cate_id,
                            questions: question_list
                        })
                    })
                    $.post("<?= url('content.questionnaire/add') ?>", {title, status, questionnaire_no, questions:questions_post}, function(res){
                        if(res.code == 1){
                            layer.msg('操作成功',{icon:1})
                            setTimeout(function(){
                                location.href = "<?= url('content.questionnaire/index') ?>"
                            }, 1000)
                        }else{
                            layer.msg(res.msg, {icon:2})
                        }
                    }, 'json')
                },

                showCateList: function(){
                    this.initCateList()
                    this.drawer = true;
                },

                addCate: function() {
                    let that = this;
                    this.multipleSelection.forEach((v, k)=>{
                        let data = {
                            cate_id: v.cate_id,
                            title: v.title,
                            questions: []
                        }
                        that.questions.push(data);
                        that.drawer = false;
                    });
                },

                addQuestion: function(){
                    let that = this;
                    this.multipleSelection2.forEach((v, k)=>{
                        let data = {
                            question_id: v.question_id,
                            label: v.label,
                            type: v.type.value,
                            is_require: v.is_require,
                            icon: v.icon,
                            option: v.option,
                            show_limit:[],
                            show_limit_txt: '设置出现条件',
                            name: v.name
                        }
                        that.questions[that.idx].questions.push(data);
                        that.question_drawer = false;
                    });
                },

                handleSelectionChange(val) {
                    this.multipleSelection = val;
                },

                handleSelectionChange2(val) {
                    this.multipleSelection2 = val;
                },

                handleSelectionChange3(val) {
                    this.multipleSelection3 = val;
                },

                initCateList(){
                    let that = this;
                    let cate_ids = "";
                    this.questions.forEach((v, k)=>{
                        console.log(v);
                        cate_ids = cate_ids + ',' + v.cate_id
                    })
                    $.post("<?= url("content.questionnaire.question/cateList") ?>", {cate_ids}, function(res){
                        that.cate_list = res.data;
                    })
                },

                showQuestionList: function(index){
                    this.idx = index;
                    this.initQuestionList(1);
                    this.question_drawer = true;
                },

                initQuestionList: function(page){
                    let that = this;
                    $.post("<?= url("content.questionnaire.question/questionList") ?>", {page}, function(res){
                        that.question_list = res.data.list;
                        that.question_total = res.data.total;
                    })
                },

                initQuestionList2: function(e){
                    this.initQuestionList(e);
                },

                delQuestion: function(index, idx){
                    console.log(123)
                    this.questions[index].questions.splice(idx, 1);
                },

                showLimitList: function(index, idx){
                    this.limit_index = index;
                    this.limit_idx = idx;
                    let limit_question_list = [];
                    try{
                        this.questions.forEach((v, k)=>{
                            v.questions.forEach((vv, kk)=>{
                                if(k >= index && kk >= idx){
                                    throw 'jump';
                                }
                                if(vv.type == 30 || vv.type == 20){
                                    limit_question_list.push(vv)
                                }
                            })
                        })
                    }catch(e){
                        console.log(e)
                    }
                    console.log(limit_question_list);
                    this.limit_question_list = limit_question_list;
                    this.limit_wrap_drawer = true;
                },

                showMarkList: function(row){
                    this.limit_option = row;
                    this.limit_drawer = true;
                    console.log(row)
                },

                setShowLimit: function(){
                    let that = this;
                    let show_limit = [];
                    let show_limit_txt = [];
                    this.limit_option.option.forEach((v, k)=>{
                        that.multipleSelection3.forEach((vv, kk)=>{
                            if(v.mark == vv.mark){
                                show_limit.push(vv.mark)
                                show_limit_txt.push(vv.label);
                            }
                        })
                    })
                    show_limit_txt = show_limit_txt.join(',');
                    show_limit_txt = this.limit_option.label + ":" + show_limit_txt
                    this.questions[this.limit_index].questions[this.limit_idx].show_limit = {question_id: this.limit_option.question_id, option: show_limit};
                    this.questions[this.limit_index].questions[this.limit_idx].show_limit_txt = show_limit_txt;
                    this.limit_drawer = false;
                    this.limit_wrap_drawer = false;
                },

                cancelShowLimit: function(){
                    this.questions[this.limit_index].questions[this.limit_idx].show_limit = [];
                    this.questions[this.limit_index].questions[this.limit_idx].show_limit_txt = '设置出现条件';
                    this.limit_drawer = false;
                    this.limit_wrap_drawer = false;
                },

                disSetShowLimit: function(){
                    this.limit_drawer = false;
                },

                handleClose(done) {
                    this.limit_wrap_drawer = false;
                }
            }
        })

    });
</script>
