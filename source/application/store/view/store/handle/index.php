<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">管理员操作日志</div>
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
                                                <el-input v-model="keywords" placeholder="购买人、推荐人"></el-input>
                                            </el-col>
                                            <el-col :span="1">
                                                <el-button icon="el-icon-search" circle @click="getRankList(1)"></el-button>
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
                                                        label="日志ID"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="name"
                                                        label="操作"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="user_name"
                                                        label="操作人"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="request_type"
                                                        label="请求类型"
                                                        width="220">
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
                                            layout="prev, pager, next"
                                            :total="total"
                                            :page-size="size"
                                            hide-on-single-page
                                            @current-change="changePage"
                                        >
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
                cur_page: 1,
                list:[],
                total: 0,
                size: 15,
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
                changePage(e){
                    this.getRankList(e);
                },
                getRankList: function(page){
                    this.cur_page = page;
                    let that = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let [keywords, size] = [this.keywords, this.size];
                    $.post("<?= url('store.handle/lists') ?>", {page, keywords, start_time, end_time, size}, function(res){
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
                this.getRankList(1);
            }
        });


    });
    function getRankList(page){
        App.getRankList(page);
    }
</script>

