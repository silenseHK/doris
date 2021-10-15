<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>
    .el-table .success-row {
        background: #f0f9eb;
    }
</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">提交列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2">
                                                <el-input v-model="keywords" placeholder="用户电话"></el-input>
                                            </el-col>
                                            <el-col :span="2">
                                                <el-button icon="el-icon-search" circle @click="getUserFillList()"></el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <el-row :gutter="20">
                                            <el-col :span="12">
                                                <template>
                                                    <el-table
                                                            :data="list"
                                                            style="width: 100%"
                                                            :highlight-current-row="true"
                                                            :current-row-key="cur_idx">
                                                        <el-table-column
                                                                label="下标"
                                                                width="100">
                                                            <template slot-scope="scope">{{scope.$index + 1}}</template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="nickName"
                                                                label="用户名">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="user_id"
                                                                label="用户ID">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="头像">
                                                            <template slot-scope="scope">
                                                                <el-image
                                                                        style="width: 80px; height: 80px"
                                                                        :src="scope.row.avatarUrl"
                                                                        fit="fit">
                                                                </el-image>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="count"
                                                                label="提交问卷数">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="团队长">
                                                            <template slot-scope="scope">
                                                                {{scope.row.group_user?scope.row.group_user.nickName:'--'}}
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                fixed="right"
                                                                label="操作"
                                                                width="200">
                                                            <template slot-scope="scope">
                                                                <el-button type="text" size="small">
                                                                    <el-link type="primary" :underline="false" :underline="false" @click="showFillList(scope)" target="_self">问卷列表</el-link>
                                                                </el-button>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                </template>

                                                <div class="am-u-lg-12 am-cf">
                                                    <div class="am-fr" v-html="page"></div>
                                                    <div class="am-fr pagination-total am-margin-right">
                                                        <div class="am-vertical-align-middle">总记录：{{total}}</div>
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <template>
                                                    <el-table
                                                            :data="list2"
                                                            style="width: 100%">
                                                        <el-table-column
                                                                prop="fill_id"
                                                                label="ID"
                                                                width="100">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="bmi"
                                                                label="BMI">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="point"
                                                                label="得分">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="配餐图"
                                                                width="180">
                                                            <template slot-scope="scope">
                                                                <div class="demo-image__preview">
                                                                    <el-image
                                                                            style="width: 100px; height: 100px"
                                                                            :src="scope.row.src_list[0]"
                                                                            :preview-src-list="scope.row.src_list">
                                                                    </el-image>
                                                                </div>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="邀请人">
                                                                <template slot-scope="scope">
                                                                    {{scope.row.invite_user?scope.row.invite_user.nickName:'--'}}
                                                                </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="create_time"
                                                                label="提交时间">
                                                        </el-table-column>
                                                        <el-table-column
                                                                fixed="right"
                                                                label="操作"
                                                                width="220">
                                                            <template slot-scope="scope">
                                                                <el-button type="text" size="small">
                                                                    <el-link type="primary" :underline="true" @click="goDetail(scope)" target="_self">问卷详情</el-link>
                                                                    <el-link type="primary" :underline="true" @click="showAdvice(scope)" target="_self">健康建议</el-link>
                                                                </el-button>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                </template>

                                                <div class="am-u-lg-12 am-cf">
                                                    <div class="am-fr pagination-total am-margin-right">
                                                        <div class="am-vertical-align-middle">总记录：{{total2}}</div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                    </el-main>
                                </el-container>
                            </el-col>
                        </el-row>

                        <el-dialog title="健康建议" :visible.sync="show_advice">
                            <el-collapse>
                                <el-collapse-item v-for="(item, key) in advice" v-if="item.advice.length > 0" :title="item.title" :name="key">
                                    <div v-for="(it, k) in item.advice">{{k+1}} . {{it}}</div>
                                </el-collapse-item>
                            </el-collapse>
                        </el-dialog>

                    </div>
                </div>
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

        App = new Vue({
            el: '#my-table',
            data: {
                page:'',
                cur_page: 1,
                list:[],
                list2:[],
                total: 0,
                total2: 0,
                keywords: '',
                idx: 0,
                questionnaire_id: <?= $questionnaire_id ?>,
                cur_user_id: 0,
                show_advice: false,
                advice: [],
                cur_idx: -1,
            },
            methods:{
                getUserFillList: function(){
                    let that = this;
                    let [page, keywords] = [this.cur_page, this.keywords]
                    let questionnaire_id = this.questionnaire_id;
                    $.post("<?= url('content.questionnaire/getUserFillList') ?>", {questionnaire_id, page, keywords}, function(res){
                        that.page = res.data.page;
                        that.list = res.data.list;
                        that.total = res.data.total;
                    }, 'json');
                },

                getFillList: function(){
                    let [user_id, questionnaire_id] = [this.cur_user_id, this.questionnaire_id]
                    let that = this;
                    $.post("<?= url('content.questionnaire/getFillList') ?>", {questionnaire_id, user_id}, function(res){
                        that.list2 = res.data.list;
                        that.total2 = res.data.total;
                    }, 'json');
                },

                showFillList: function(scope){
                    let detail = this.list[scope.$index]
                    this.cur_user_id = detail.user_id;
                    this.cur_idx = scope.$index;
                    this.getFillList();
                },

                goDetail: function(scope){
                    let detail = this.list2[scope.$index]
                    location.href = `<?= url('content.questionnaire/userFillDetail') ?>&fill_id=${detail.fill_id}`
                },

                showAdvice: function(scope){
                    this.advice = this.list2[scope.$index].advice
                    this.show_advice = true;
                }
            },
            computed:{

            },

            created: function(){
                this.getUserFillList();
            }
        });


    });

    function getUserFillList(page=1){
        App.cur_page = page;
        App.getUserFillList();
    }

</script>

