<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">代理中心</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2">
                                                <el-button type="primary" plain @click="add_agent_dialog=true">添加账号</el-button>
                                            </el-col>
                                            <el-col :span="4.5">
                                                <el-date-picker
                                                    v-model="search.date"
                                                    type="datetimerange"
                                                    :picker-options="pickerOptions"
                                                    range-separator="至"
                                                    start-placeholder="开始日期"
                                                    end-placeholder="结束日期"
                                                    align="right">
                                                </el-date-picker>
                                            </el-col>

                                            <el-col :span="2.5">
                                                <el-input v-model="search.mobile" placeholder="登录手机账号"></el-input>
                                            </el-col>

                                            <el-col :span="2.5">
                                                <el-input v-model="search.user_id" placeholder="用户、代理ID"></el-input>
                                            </el-col>

                                            <el-col :span="1">
                                                <el-button icon="el-icon-search" circle @click="getAgentList(1)"></el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <el-row>
                                            <el-col :xl={span:20}>
                                                <template>
                                                    <el-table
                                                            :data="list"
                                                            style="width: 100%">
                                                        <el-table-column
                                                                prop="agent_id"
                                                                label="代理ID">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="user.nickName"
                                                                label="昵称">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="头像"
                                                                width="180">
                                                            <template slot-scope="scope">
                                                                <el-image
                                                                        style="width: 60px; height: 60px"
                                                                        :src="scope.row.user.avatarUrl"
                                                                        fit="fill"></el-image>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="user_id"
                                                                label="用户ID">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="mobile"
                                                                label="登录账号">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="状态">
                                                            <template slot-scope="scope">
                                                                <el-tag style="cursor: pointer" @click="editStatus(scope)" v-if="scope.row.status == 0" type="info">冻结</el-tag>
                                                                <el-tag style="cursor: pointer" @click="editStatus(scope)" v-if="scope.row.status == 1" type="success">正常</el-tag>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="create_time"
                                                                label="创建时间">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="login_time"
                                                                label="上次登陆时间">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="操作">
                                                            <template slot-scope="scope">
                                                                <el-button plain size="mini" @click="edit(scope)">编辑</el-button>
                                                                <el-button plain size="mini" @click="editPwd(scope)">修改密码</el-button>
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
                                        </el-row>

                                    </el-main>
                                </el-container>
                            </el-col>
                        </el-row>

                        <el-dialog title="添加账号" :visible.sync="add_agent_dialog" width="30%">
                            <el-form :model="add_agent_form" label-position="top">
                                <el-form-item label="代理ID" :label-width="formLabelWidth">
                                    <el-input v-model="add_agent_form.user_id" class="input-with-select">
                                        <el-button slot="append" icon="el-icon-search" @click="searchAgent"></el-button>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="用户信息" :label-width="formLabelWidth">
                                    <el-row type="flex" align="middle" :gutter="10" v-show="add_agent_form.agent.user_id > 0">
                                        <el-col :span="3">
                                            <img style="height:40px;width:40px;border-radius: 50%;" :src="add_agent_form.agent.avatar" />
                                        </el-col>
                                        <el-col :span="6">
                                            <el-link type="success" :underline="false">{{add_agent_form.agent.nickname}}</el-link>
                                        </el-col>
                                        <el-col :span="2">
                                            <el-button type="danger" icon="el-icon-delete" circle size="mini" @click="delAgent"></el-button>
                                        </el-col>
                                    </el-row>
                                </el-form-item>
                                <el-form-item label="登录手机号" :label-width="formLabelWidth">
                                    <el-input v-model="add_agent_form.mobile" autocomplete="off"></el-input>
                                </el-form-item>
                                <el-form-item label="登录密码" :label-width="formLabelWidth">
                                    <el-input show-password v-model="add_agent_form.password" autocomplete="off"></el-input>
                                </el-form-item>
                            </el-form>
                            <div slot="footer" class="dialog-footer">
                                <el-button @click="cancelAdd">取 消</el-button>
                                <el-button type="primary" @click="confirmAdd">确 定</el-button>
                            </div>
                        </el-dialog>

                        <el-dialog title="编辑账号" :visible.sync="edit_agent_dialog" width="30%">
                            <el-form :model="edit_data" label-position="top">
                                <el-form-item label="代理ID" :label-width="formLabelWidth">
                                    <el-input v-model="edit_data.user_id" class="input-with-select">
                                        <el-button slot="append" icon="el-icon-search" @click="searchEditAgent"></el-button>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="用户信息" :label-width="formLabelWidth">
                                    <el-row type="flex" align="middle" :gutter="10" v-show="edit_data.agent.user_id > 0">
                                        <el-col :span="3">
                                            <img style="height:40px;width:40px;border-radius: 50%;" :src="edit_data.agent.avatar" />
                                        </el-col>
                                        <el-col :span="6">
                                            <el-link type="success" :underline="false">{{edit_data.agent.nickname}}</el-link>
                                        </el-col>
                                        <el-col :span="2">
                                            <el-button type="danger" icon="el-icon-delete" circle size="mini" @click="delEditAgent"></el-button>
                                        </el-col>
                                    </el-row>
                                </el-form-item>
                                <el-form-item label="登录手机号" :label-width="formLabelWidth">
                                    <el-input v-model="edit_data.mobile" autocomplete="off"></el-input>
                                </el-form-item>
                            </el-form>
                            <div slot="footer" class="dialog-footer">
                                <el-button @click="cancelEdit">取 消</el-button>
                                <el-button type="primary" @click="confirmEdit">确 定</el-button>
                            </div>
                        </el-dialog>

                        <el-dialog title="修改密码" :visible.sync="edit_pwd_dialog" width="30%">
                            <el-form :model="edit_data" label-position="top">
                                <el-form-item label="新密码" :label-width="formLabelWidth">
                                    <el-input show-password v-model="edit_pwd_data.password" autocomplete="off"></el-input>
                                </el-form-item>
                            </el-form>
                            <div slot="footer" class="dialog-footer">
                                <el-button @click="cancelEditPwd">取 消</el-button>
                                <el-button type="primary" @click="confirmEditPwd">确 定</el-button>
                            </div>
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
                formLabelWidth: 200,
                add_agent_dialog: false,
                edit_agent_dialog: false,
                edit_pwd_dialog: false,
                add_agent_form: {
                    user_id: '',
                    region: '',
                    mobile: '',
                    password: '',
                    agent: {
                        user_id: 0,
                        avatar: '',
                        nickname: ''
                    }
                },

                page:'',
                cur_page: 1,
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

                search: {
                    mobile: '',
                    date: '',
                    user_id: ''
                },

                edit_data: {
                    agent_id: 0,
                    user_id: '',
                    mobile: '',
                    agent: {
                        user_id: 0,
                        avatar: '',
                        nickname: ''
                    }
                },

                edit_pwd_data: {
                    agent_id: 0,
                    password: ''
                }

            },
            methods:{
                getAgentList: function(page){
                    this.cur_page = page;
                    let that = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.search.date){
                        start_time = this.initDate(this.search.date[0]);
                        end_time = this.initDate(this.search.date[1]);
                    }
                    let [user_id, mobile] = [this.search.user_id, this.search.mobile];
                    $.post("<?= url('user.agent/agentList') ?>", {page, user_id, mobile, start_time, end_time}, function(res){
                        that.page = res.data.page;
                        that.list = res.data.list;
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
                searchAgent(){
                    let user_id = this.add_agent_form.user_id;
                    if(!user_id){
                        this.$message({
                            showClose: true,
                            message: '请填写代理ID',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.agent/searchAgent') ?>", { user_id }, function(res){
                        if(res.code == 1){
                            that.add_agent_form.agent = {
                                user_id: res.data.user_id,
                                avatar: res.data.avatarUrl,
                                nickname: res.data.nickName
                            }
                        }else{
                            that.$message({
                                showClose: true,
                                message: res.msg,
                                type: 'error'
                            });
                        }
                    }, 'json')
                },
                searchEditAgent(){
                    let user_id = this.edit_data.user_id;
                    if(!user_id){
                        this.$message({
                            showClose: true,
                            message: '请填写代理ID',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.agent/searchAgent') ?>", { user_id }, function(res){
                        if(res.code == 1){
                            that.edit_data.agent = {
                                user_id: res.data.user_id,
                                avatar: res.data.avatarUrl,
                                nickname: res.data.nickName
                            }
                        }else{
                            that.$message({
                                showClose: true,
                                message: res.msg,
                                type: 'error'
                            });
                        }
                    }, 'json')
                },
                delAgent(){
                    this.add_agent_form.agent = {
                        user_id: 0,
                        avatar: '',
                        nickname: ''
                    }
                },

                cancelAdd(){
                    this.add_agent_dialog = false;
                    this.add_agent_form = {
                        user_id: '',
                        agent: {
                            user_id: 0,
                            avatar: '',
                            nickname: ''
                        },
                        mobile: '',
                        password: ''
                    }
                },

                confirmAdd(){
                    let data = this.add_agent_form;
                    let [user_id, mobile, password] = [parseInt(data.agent.user_id), data.mobile, data.password];
                    if(!user_id || !user_id < 0){
                        this.$message({
                            showClose: true,
                            message: '请选择代理用户',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!mobile || !(/^1[3456789]\d{9}$/.test(mobile))){
                        this.$message({
                            showClose: true,
                            message: '请填写正确的手机号',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!password || password.length < 6){
                        this.$message({
                            showClose: true,
                            message: '登录密码需要长度大于6位',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.agent/add') ?>", {user_id, mobile, password} , function(res){
                        let type = 'error';
                        if(res.code == 1){
                            type = 'success'
                            that.cancelAdd();
                            that.getAgentList();
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                edit(scope){
                    let detail = scope.row;
                    this.edit_data = {
                        idx: scope.$index,
                        agent_id: detail.agent_id,
                        user_id: detail.user_id,
                        mobile: detail.mobile,
                        agent: {
                            user_id: detail.user_id,
                            avatar: detail.user.avatarUrl,
                            nickname: detail.user.nickName
                        }
                    };
                    this.edit_agent_dialog = true;
                },

                editPwd(scope){
                    this.edit_pwd_data = {
                        agent_id: scope.row.agent_id,
                        password: ''
                    }
                    this.edit_pwd_dialog = true;
                },

                editStatus(scope){
                    let that = this;
                    $.post("<?= url('user.agent/editStatus') ?>", {agent_id:scope.row.agent_id}, function(res){
                        if(res.code == 1){
                            scope.row.status = res.data;
                        }else{
                            that.$message({
                                showClose: true,
                                message: res.msg,
                                type: 'error'
                            });
                        }
                    }, 'json')
                },

                delEditAgent(){
                    this.edit_data.agent = {
                        user_id: 0,
                        avatar: '',
                        nickname: ''
                    }
                },

                cancelEdit(){
                    this.edit_agent_dialog = false;
                    this.edit_data = {
                        salesperson_id: 0,
                        user_id: '',
                        agent: {
                            user_id: 0,
                            avatar: '',
                            nickname: ''
                        },
                        group_id: '',
                        name: ''
                    }
                },

                confirmEdit(){
                    let data = this.edit_data;
                    let [user_id, mobile, agent_id] = [parseInt(data.agent.user_id), data.mobile, data.agent_id];
                    if(!user_id || !user_id < 0){
                        this.$message({
                            showClose: true,
                            message: '请选择代理用户',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!mobile || !(/^1[3456789]\d{9}$/.test(mobile))){
                        this.$message({
                            showClose: true,
                            message: '请填写正确的手机号',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.agent/edit') ?>", {user_id, mobile, agent_id} , function(res){
                        let type = 'error';
                        if(res.code == 1){
                            type = 'success'
                            that.cancelEdit();
                            that.getAgentList();
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                confirmEditPwd(){
                    let [agent_id, password] = [this.edit_pwd_data.agent_id, this.edit_pwd_data.password];
                    if(!password || password.length < 6){
                        this.$message({
                            showClose: true,
                            message: '登录密码需要长度大于6位',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.agent/editPwd') ?>", {agent_id, password} , function(res){
                        let type = 'error';
                        if(res.code == 1){
                            type = 'success'
                            that.cancelEditPwd();
                            that.getAgentList();
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                cancelEditPwd(){
                    this.edit_pwd_data = {
                        agent_id: 0,
                        password: ''
                    }
                    this.edit_pwd_dialog = false;
                },

            },
            computed:{

            },
            created: function(){
                this.getAgentList();
            }
        });


    });
    function getAgentList(page){
        App.getAgentList(page);
    }
</script>

