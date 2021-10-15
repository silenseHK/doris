<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">招商管理</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="4">
                                                <el-button type="primary" plain @click="add_agent_dialog=true">添加招商</el-button>
                                                <el-button type="primary" plain @click="exportData">导出销售数据</el-button>
                                            </el-col>
                                            <el-col :span="2">
                                                <el-select v-model="search.type" placeholder="请选择">
                                                    <el-option label="全部" :value="0"></el-option>
                                                    <el-option
                                                            v-for="(item, key) in type_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>

                                            <el-col :span="2">
                                                <el-select v-model="search.group_id" placeholder="请选择">
                                                    <el-option label="全部" :value="0"></el-option>
                                                    <el-option
                                                            v-for="(item, key) in group_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>

                                            <el-col :span="2">
                                                <el-select v-model="search.status" placeholder="请选择">
                                                    <el-option label="全部" :value="-1"></el-option>
                                                    <el-option label="正常" :value="1"></el-option>
                                                    <el-option label="冻结" :value="0"></el-option>
                                                </el-select>
                                            </el-col>

                                            <el-col :span="2.5">
                                                <el-input v-model="search.name" placeholder="招商姓名"></el-input>
                                            </el-col>

                                            <el-col :span="2.5">
                                                <el-input v-model="search.user_id" placeholder="用户、招商ID"></el-input>
                                            </el-col>

                                            <el-col :span="1">
                                                <el-button icon="el-icon-search" circle @click="getSalespersonList(1)"></el-button>
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
                                                                prop="salesperson_id"
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
                                                                prop="name"
                                                                label="真实姓名">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="type.text"
                                                                label="职位">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="group_id.text"
                                                                label="部门">
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
                                                                label="操作">
                                                            <template slot-scope="scope">
                                                                <el-button plain size="mini" @click="edit(scope)">编辑</el-button>
                                                                <el-button plain size="mini" @click="del(scope)">删除</el-button>
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

                        <el-dialog title="添加招商" :visible.sync="add_agent_dialog" width="30%">
                            <el-form :model="add_agent_form" label-position="top">
                                <el-form-item label="招商ID" :label-width="formLabelWidth">
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
                                <el-form-item label="真实姓名" :label-width="formLabelWidth">
                                    <el-input v-model="add_agent_form.name" class="input-with-select"></el-input>
                                </el-form-item>
                                <el-form-item label="选择部门" :label-width="formLabelWidth">
                                    <el-select v-model="add_agent_form.group_id" placeholder="请选择">
                                        <el-option
                                                v-for="(item, key) in group_list"
                                                :key="item.value"
                                                :label="item.text"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="选择职位" :label-width="formLabelWidth">
                                    <el-select v-model="add_agent_form.type" placeholder="请选择">
                                        <el-option
                                                v-for="(item, key) in type_list"
                                                :key="item.value"
                                                :label="item.text"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                            </el-form>
                            <div slot="footer" class="dialog-footer">
                                <el-button @click="cancelAdd">取 消</el-button>
                                <el-button type="primary" @click="confirmAdd">确 定</el-button>
                            </div>
                        </el-dialog>

                        <el-dialog title="编辑招商" :visible.sync="edit_agent_dialog" width="30%">
                            <el-form :model="edit_data" label-position="top">
                                <el-form-item label="招商ID" :label-width="formLabelWidth">
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
                                <el-form-item label="真实姓名" :label-width="formLabelWidth">
                                    <el-input v-model="edit_data.name" class="input-with-select"></el-input>
                                </el-form-item>
                                <el-form-item label="选择部门" :label-width="formLabelWidth">
                                    <el-select v-model="edit_data.group_id" placeholder="请选择">
                                        <el-option
                                                v-for="(item, key) in group_list"
                                                :key="item.value"
                                                :label="item.text"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="选择职位" :label-width="formLabelWidth">
                                    <el-select v-model="edit_data.type" placeholder="请选择">
                                        <el-option
                                                v-for="(item, key) in type_list"
                                                :key="item.value"
                                                :label="item.text"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                            </el-form>
                            <div slot="footer" class="dialog-footer">
                                <el-button @click="cancelEdit">取 消</el-button>
                                <el-button type="primary" @click="confirmEdit">确 定</el-button>
                            </div>
                        </el-dialog>

                        <el-dialog title="导出销售数据" :visible.sync="export_dialog" width="30%">
                            <el-form :model="export_data" label-position="top">
                                <el-form-item label="选择日期" :label-width="formLabelWidth">
                                    <el-date-picker
                                            v-model="export_data.date"
                                            type="datetimerange"
                                            :picker-options="pickerOptions"
                                            range-separator="至"
                                            start-placeholder="开始日期"
                                            end-placeholder="结束日期"
                                            align="right">
                                    </el-date-picker>
                                </el-form-item>
                                <el-form-item label="" :label-width="formLabelWidth">
                                    <el-button @click="cancelExport">取 消</el-button>
                                    <el-button type="primary" @click="confirmExport">导 出</el-button>
                                </el-form-item>
                            </el-form>
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
                export_dialog: false,
                add_agent_form: {
                    user_id: '',
                    name: '',
                    group_id: '',
                    type: '',
                    agent: {
                        user_id: 0,
                        avatar: '',
                        nickname: ''
                    }
                },
                type_list: <?= json_encode($typeList) ?>,
                group_list: <?= json_encode($groupList) ?>,
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
                    name: '',
                    user_id: '',
                    group_id: 0,
                    type: 0,
                    status: -1,
                },

                export_data: {
                    date: []
                },

                edit_data: {
                    user_id: '',
                    name: '',
                    group_id: '',
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
                cancelExport(){
                    this.export_data = {
                        date: []
                    }
                    this.export_dialog = false;
                },
                confirmExport(){
                    let [start_time, end_time] = ['', ''];
                    if(!this.export_data.date){
                        this.$message({
                            showClose: true,
                            message: '请选择日期',
                            type: 'error'
                        });
                        return false;
                    }
                    start_time = this.initDate(this.export_data.date[0]);
                    end_time = this.initDate(this.export_data.date[1]);
                    let [user_id, name, type, group_id, status] = [this.search.user_id, this.search.name, this.search.type, this.search.group_id, this.search.status];
                    this.cancelExport();
                    window.open('<?= url('user.salesperson/exportSaleData') ?>'+`&start_time=${start_time}&end_time=${end_time}&id=${user_id}&name=${name}&type=${type}&group_id=${group_id}&status=${status}`,'_blank');
                },
                exportData(){
                    this.export_dialog = true;
                },
                getSalespersonList: function(page){
                    this.cur_page = page;
                    let that = this;
                    let [user_id, name, type, group_id, status] = [this.search.user_id, this.search.name, this.search.type, this.search.group_id, this.search.status];
                    $.post("<?= url('user.salesperson/salespersonList') ?>", {page, id:user_id, name, type, group_id, status}, function(res){
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
                        name: '',
                        group_id: '',
                        type: '',
                        agent: {
                            user_id: 0,
                            avatar: '',
                            nickname: ''
                        }
                    }
                },

                confirmAdd(){
                    let data = this.add_agent_form;
                    let [user_id, name, group_id, type] = [parseInt(data.agent.user_id), data.name, data.group_id, data.type];
                    if(!user_id || !user_id < 0){
                        this.$message({
                            showClose: true,
                            message: '请选择代理用户',
                            type: 'error'
                        });
                        return false;
                    }
                    if($.trim(name) == ''){
                        this.$message({
                            showClose: true,
                            message: '请填写真实姓名',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!group_id){
                        this.$message({
                            showClose: true,
                            message: '请选择部门',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!type){
                        this.$message({
                            showClose: true,
                            message: '请选择职位',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.salesperson/add') ?>", {user_id, name, group_id, type} , function(res){
                        let type = 'error';
                        if(res.code == 1){
                            type = 'success'
                            that.cancelAdd();
                            that.getSalespersonList();
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
                        salesperson_id: detail.salesperson_id,
                        user_id: detail.user_id,
                        group_id: detail.group_id.value,
                        type: detail.type.value,
                        name: detail.name,
                        agent: {
                            user_id: detail.user_id,
                            avatar: detail.user.avatarUrl,
                            nickname: detail.user.nickName
                        }
                    };
                    this.edit_agent_dialog = true;
                },

                del(scope){
                    this.$confirm('确定删除?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post('<?= url('user.salesperson/del') ?>', {salesperson_id:scope.row.salesperson_id}, function(res){
                            if(res.code == 1){
                                that.list.splice(scope.$index,1);
                            }else{
                                that.$message({
                                    showClose: true,
                                    message: res.msg,
                                    type: 'error'
                                });
                            }
                        }, 'json')
                    }).catch();
                },

                editStatus(scope){
                    let that = this;
                    $.post("<?= url('user.salesperson/editStatus') ?>", {salesperson_id:scope.row.salesperson_id}, function(res){
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
                        agent_id: 0,
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

                confirmEdit(){
                    let data = this.edit_data;
                    let [user_id, group_id, salesperson_id, type, name] = [parseInt(data.agent.user_id), data.group_id, data.salesperson_id, data.type, data.name];
                    if(!user_id || !user_id < 0){
                        this.$message({
                            showClose: true,
                            message: '请选择代理用户',
                            type: 'error'
                        });
                        return false;
                    }
                    if($.trim(name) == ''){
                        this.$message({
                            showClose: true,
                            message: '请填写真实姓名',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!group_id){
                        this.$message({
                            showClose: true,
                            message: '请选择部门',
                            type: 'error'
                        });
                        return false;
                    }
                    if(!type){
                        this.$message({
                            showClose: true,
                            message: '请选择职位',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.salesperson/edit') ?>", {user_id, salesperson_id, group_id, type, name} , function(res){
                        let type = 'error';
                        if(res.code == 1){
                            type = 'success'
                            that.cancelEdit();
                            that.getSalespersonList();
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

            },
            computed:{

            },
            created: function(){
                this.getSalespersonList();
            }
        });


    });
    function getSalespersonList(page){
        App.getSalespersonList(page);
    }
</script>

