<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">团队列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2.5">
                                                <el-select v-model="grade_id" placeholder="请选择">
                                                    <el-option label="全部" :value="0"></el-option>
                                                    <el-option
                                                            v-for="item in grade_list"
                                                            :key="item.grade_id"
                                                            :label="item.name"
                                                            :value="item.grade_id">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input v-model="keywords" placeholder="请输入用户昵称"></el-input>
                                            </el-col>
                                            <el-col :span="1">
                                                <el-button icon="el-icon-search" circle @click="search(1)"></el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list"
                                                    style="width: 100%">
                                                <el-table-column
                                                        prop="user_id"
                                                        label="user_id"
                                                        width="120">
                                                </el-table-column>
                                                <el-table-column
                                                        label="微信头像"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 80px; height: 80px"
                                                                :src="scope.row.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="nickName"
                                                        label="微信昵称">
                                                </el-table-column>
                                                <el-table-column
                                                        label="代理等级">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.grade.name}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="mobile_hide"
                                                        label="电话">
                                                </el-table-column>
                                                <el-table-column
                                                        label="邀请人">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="info">{{scope.row.invitation_user.nickName}}({{scope.row.invitation_user.grade.name}})</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="create_time"
                                                        label="变动时间">
                                                </el-table-column>
                                            </el-table>
                                        </template>
                                    </el-main>

                                    <el-footer>
                                        <el-pagination
                                                background
                                                :page-size="size"
                                                :current-page="page"
                                                layout="prev, pager, next, total, ->"
                                                @current-change="search"
                                                hide-on-single-page
                                                :total="total">
                                        </el-pagination>
                                    </el-footer>

                                </el-container>
                            </el-col>
                        </el-row>


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
                size: 15,
                user_id: <?= $user_id ?>,
                page:1,
                list:[],
                total: 0,
                grade_list: <?= json_encode($grade_list) ?>,
                keywords: '',
                grade_id: 0,
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getTeamList();
                },
                getTeamList: function(){
                    let that = this;
                    let {page, grade_id, keywords, user_id} = this;
                    $.post("<?= url('user.team/getTeamLists') ?>", {page, grade_id, keywords, user_id}, function(res){
                        that.list = res.data.data;
                        that.total = res.data.total;
                    }, 'json')
                },
            },
            computed:{

            },
            created: function(){
                this.getTeamList();
            }
        });


    });
</script>

