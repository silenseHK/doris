<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>

</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">发货统计</div>
                </div>
                <div class="widget-body am-fr" id="wrap" v-cloak>

                    <el-container>
                        <el-header>
                            <el-date-picker
                                v-model="date"
                                type="datetimerange"
                                :picker-options="pickerOptions"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                align="right">
                            </el-date-picker>

                            <el-button type="primary" plain size="medium" @click="dateData()">搜索</el-button>
                        </el-header>
                        <el-main>
                            <el-row :gutter="20">
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">[总]待发货:</el-col>
                                        <el-col :span="2" :offset="2">{{total_data.wait_send}}</el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">[总]备货中:</el-col>
                                        <el-col :span="2" :offset="2">{{total_data.prepare}}</el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">[总]已发货:</el-col>
                                        <el-col :span="2" :offset="2">{{total_data.did_send}}</el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">[总]已取消:</el-col>
                                        <el-col :span="2" :offset="2">{{total_data.cancel}}</el-col>
                                    </el-row>
                                </el-col>
                            </el-row>
                            <el-row :gutter="20" style="margin-top:40px;">
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">待发货:</el-col>
                                        <el-col :span="2" :offset="2">{{total_date.wait_send}}</el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">备货中:</el-col>
                                        <el-col :span="2" :offset="2">{{total_date.prepare}}</el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">已发货:</el-col>
                                        <el-col :span="2" :offset="2">{{total_date.did_send}}</el-col>
                                    </el-row>
                                </el-col>
                                <el-col :span="6">
                                    <el-row type="flex" align="middle">
                                        <el-col :span="4" :offset="2">已取消:</el-col>
                                        <el-col :span="2" :offset="2">{{total_date.cancel}}</el-col>
                                    </el-row>
                                </el-col>
                            </el-row>
                        </el-main>
                    </el-container>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>
<script src="assets/store/js/jquery-migrate-1.2.1.min.js"></script>
<script src="assets/store/js/jquery.jqprint-0.3.js"></script>

<script>
    var App;
    $(function () {

        App = new Vue({
            el: '#wrap',
            data: {
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
                total_data: {},
                total_date: {}
            },
            methods:{
                initDate: function(date){
                    let year = date.getFullYear();
                    let month = date.getMonth() + 1;
                    let day = date.getDate();
                    let hour = date.getHours();
                    let minute = date.getMinutes();
                    let second = date.getSeconds();
                    return `${year}-${month}-${day} ${hour}:${minute}:${second}`;
                },

                totalData: function(){
                    let that = this;
                    $.post('<?= url("order.express/statisticsTotal") ?>', {}, function(res){
                        that.total_data = res.data;
                        that.total_date = res.data;
                    }, 'json')
                },

                dateData: function(){
                    let start_time = '';
                    let end_time = '';
                    if(this.date){
                        start_time = this.initDate(this.date[0])
                        end_time = this.initDate(this.date[1])
                    }
                    let that = this;
                    $.post('<?= url("order.express/statisticsTotal") ?>', {start_time, end_time}, function(res){
                        that.total_date = res.data;
                    }, 'json')
                }

            },
            computed:{

            },
            created: function(){
                this.totalData();
            }
        });

    });

</script>

