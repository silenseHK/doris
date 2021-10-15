<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">转换团队</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table" v-cloak>
                        <el-row :gutter="20">
                            <el-col :span="7">
                                <el-row>
                                    <el-col :span="18">
                                        <el-form label-position="top" label-width="80px" :model="formExchange">
                                            <el-form-item label="需要转换团队用户user_id">
                                                <el-input v-model="formExchange.user_id" @change="getUserInfo(1)"></el-input>
                                            </el-form-item>

                                            <el-row v-show="formExchange.user_info.mobile" type="flex" justify="justify-start" align="middle">
                                                <el-col :span="4">
                                                    <el-avatar shape="square" :size="50" :src="formExchange.user_info.avatar"></el-avatar>
                                                </el-col>
                                                <el-col :span="4">
                                                    <el-row>
                                                        <el-col>{{formExchange.user_info.nickname}}</el-col>
                                                        <el-col style="margin-top:10px;">{{formExchange.user_info.mobile}}</el-col>
                                                    </el-row>
                                                </el-col>
                                            </el-row>

                                            <el-form-item label="新的上级代理user_id">
                                                <el-input v-model="formExchange.receive_user_id" @change="getUserInfo(2)"></el-input>
                                            </el-form-item>

                                            <transition name="el-zoom-in-top">
                                            <el-row v-show="formExchange.receive_user_info.mobile" type="flex" justify="justify-start" align="middle">
                                                <el-col :span="4">
                                                    <el-avatar shape="square" :size="50" :src="formExchange.receive_user_info.avatar"></el-avatar>
                                                </el-col>
                                                <el-col :span="4">
                                                    <el-row>
                                                        <el-col>{{formExchange.receive_user_info.nickname}}</el-col>
                                                        <el-col style="margin-top:10px;">{{formExchange.receive_user_info.mobile}}</el-col>
                                                    </el-row>
                                                </el-col>
                                            </el-row>
                                            </transition>

                                            <el-button type="primary" plain @click="submit">确定转换</el-button>

                                        </el-form>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="16">
                                <el-row :gutter="20">
                                    <el-col :span="24">
                                        <el-container>
                                            <el-header>
                                                <el-row :gutter="10">
                                                    <el-col :span="4.5">
                                                        <el-date-picker
                                                                v-model="date"
                                                                type="datetimerange"
                                                                :picker-options="pickerOptions"
                                                                range-separator="至"
                                                                start-placeholder="开始日期"
                                                                end-placeholder="结束日期"
                                                                align="right">
                                                        </el-date-picker>
                                                    </el-col>
                                                    <el-col :span="2.5">
                                                        <el-input v-model="keywords" placeholder="转换人用户昵称"></el-input>
                                                    </el-col>
                                                    <el-col :span="1">
                                                        <el-button icon="el-icon-search" circle @click="getExchangeList(1)"></el-button>
                                                    </el-col>
                                                </el-row>
                                            </el-header>
                                            <el-main>
                                                <template>
                                                    <el-table
                                                            :data="list"
                                                            style="width: 100%">
                                                        <el-table-column
                                                                prop="log_id"
                                                                label="ID"
                                                                width="120">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="转换人">
                                                            <template slot-scope="scope">
                                                                <el-row>
                                                                    <el-col :span="24">{{scope.row.user_id}}</el-col>
                                                                    <el-col :span="24">{{scope.row.nickName}}</el-col>
                                                                </el-row>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="转换前邀请人	">
                                                            <template slot-scope="scope">
                                                                <el-row v-if="scope.row.old_invitation">
                                                                    <el-col :span="24">{{scope.row.old_invitation.user_id}}</el-col>
                                                                    <el-col :span="24">{{scope.row.old_invitation.nickName}}</el-col>
                                                                </el-row>
                                                                <el-link v-else  :underline="false" type="primary">无</el-link>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="转换后邀请人	">
                                                            <template slot-scope="scope">
                                                                <el-row v-if="scope.row.new_invitation">
                                                                    <el-col :span="24">{{scope.row.new_invitation.user_id}}</el-col>
                                                                    <el-col :span="24">{{scope.row.new_invitation.nickName}}</el-col>
                                                                </el-row>
                                                                <el-link v-else  :underline="false" type="primary">无</el-link>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="create_time"
                                                                label="操作时间"
                                                                width="220">
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
                formExchange:{
                    user_id: '',
                    receive_user_id: '',
                    user_info: {
                        avatar: '',
                        nickname: '',
                        mobile: '',
                    },
                    receive_user_info: {
                        avatar: '',
                        nickname: '',
                        mobile: '',
                    }
                },

                page:1,
                size: 10,
                list:[],
                total: 0,
                pickerOptions: {
                    shortcuts: [{
                        text: '最近一周',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '最近一个月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '最近三个月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                            picker.$emit('pick', [start, end]);
                        }
                    }]
                },
                date:'',
                keywords: ''
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getExchangeList();
                },
                getExchangeList: function(){
                    let that = this;
                    let {page, keywords} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    $.post("<?= url('user.team/getExchangeTeamLog') ?>", {page, search:keywords, start_time, end_time}, function(res){
                        that.list = res.data.data;
                        that.total = res.data.total;
                    }, 'json')
                },
                initDate: function(date){
                    let year = date.getFullYear();
                    let month = date.getMonth() + 1;
                    let day = date.getDate();
                    let hour = date.getHours();
                    let minute = date.getMinutes();
                    let second = date.getSeconds();
                    return `${year}-${month}-${day} ${hour}:${minute}:${second}`;
                },

                getUserInfo: function(type){
                    let user_id = type == 1?this.formExchange.user_id:this.formExchange.receive_user_id;
                    let that = this;
                    $.post("<?= url('user.stock/userInfo') ?>", {user_id}, function(res){
                        let user_info = {
                            avatar: '',
                            nickname: '',
                            mobile: ''
                        }
                        if(res.code == 1){
                            user_info.nickname = res.data.nickName;
                            user_info.mobile = res.data.mobile_hide;
                            user_info.avatar = res.data.avatarUrl;
                            if(type == 2){
                                that.formExchange.receive_user_info = user_info;
                            }else{
                                that.formExchange.user_info = user_info;
                            }
                        }else{
                            if(type == 2){
                                that.formExchange.receive_user_info = {};
                                that.formExchange.receive_user_id = 0;
                            }else{
                                that.formExchange.user_info = {};
                                that.formExchange.user_id = '';
                            }
                        }
                    }, 'json')
                },

                submit: function(){
                    let data = this.formExchange;
                    let json_ = {
                        user_id: data.user_id,
                        exchange_user_id: data.receive_user_id,
                    }
                    if(!json_.user_id || (!json_.exchange_user_id && json_.exchange_user_id != 0)){
                        this.$message({
                            message: '请将数据补充完整',
                            type: 'warning'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.team/exchange') ?>", json_, function(res){
                        if(res.code == 1){
                            that.$message({
                                message: res.msg,
                                type: 'success'
                            });
                        }else{
                            that.$message.error(res.msg);
                        }

                    }, 'json')
                }
            },
            computed:{

            },
            created: function(){
                this.getExchangeList(1);
            }
        });
    });
</script>

