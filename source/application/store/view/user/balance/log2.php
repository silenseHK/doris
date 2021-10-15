<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">余额明细</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
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
                                                <el-select v-model="scene" placeholder="请选择">
                                                    <el-option label="全部" :value="0"></el-option>
                                                    <el-option
                                                            v-for="item in scene_list"
                                                            :key="item.value"
                                                            :label="item.name"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input v-model="nickname" placeholder="请输入用户昵称"></el-input>
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
                                                        prop="log_id"
                                                        label="ID"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        label="微信头像"
                                                        width="120">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.user.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="user.nickName"
                                                        label="微信昵称">
                                                </el-table-column>
                                                <el-table-column
                                                        label="余额变动场景">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.scene.text}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="变动金额">
                                                    <template slot-scope="scope">
                                                        <el-link v-if="scope.row.money > 0" :underline="false" type="primary">+{{scope.row.money}}</el-link>
                                                        <el-link v-else :underline="false" type="danger">{{scope.row.money}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="describe"
                                                        label="描述说明">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="remark"
                                                        label="管理员备注">
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
                scene_list: <?= json_encode($attributes['scene']) ?>,
                scene: 0,
                nickname: ''
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getLogList();
                },
                getLogList: function(){
                    let that = this;
                    let {page, scene, nickname, user_id} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    $.post("<?= url('user.balance/getLogList') ?>", {page, scene, search:nickname, user_id, start_time, end_time}, function(res){
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
            },
            computed:{

            },
            created: function(){
                this.getLogList();
            }
        });


    });
</script>

