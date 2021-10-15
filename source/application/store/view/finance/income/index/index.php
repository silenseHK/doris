<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">平台资金进出</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
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

                                        <el-select v-model="type" placeholder="请选择">
                                            <el-option label="全部" value="0"></el-option>
                                            <el-option
                                                    v-for="item in typeList"
                                                    :key="item.value"
                                                    :label="item.text"
                                                    :value="item.value">
                                            </el-option>
                                        </el-select>

                                        <el-button type="primary" plain size="medium" @click="search">搜索</el-button>
                                    </el-header>
                                    <el-header>
                                        <el-row>
                                            <el-col :span="8">
                                                <el-row>
                                                    <el-col :span="6">总收入：{{total_in}}</el-col>
                                                    <el-col :span="6">总支出：{{total_out}}</el-col>
                                                </el-row>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-row>
                                                    <el-col :span="6">收入：{{in_}}</el-col>
                                                    <el-col :span="6">支出：{{out}}</el-col>
                                                </el-row>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list"
                                                    style="width: 100%">
                                                <el-table-column
                                                        prop="income_id"
                                                        label="ID"
                                                        width="100">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_no"
                                                        label="订单号"
                                                        width="220">
                                                </el-table-column>
                                                <el-table-column
                                                        label="金额"
                                                        width="200">
                                                    <template slot-scope="scope">
                                                        <el-row v-bind:style="{color:scope.row.direction == 10? plusColor : minusColor}">
                                                            <el-col :span="3"><i :class="scope.row.direction == 10 ? 'el-icon-plus' : 'el-icon-minus' "></i></el-col>
                                                            <el-col :span="6">{{scope.row.money}}</el-col>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="type.text"
                                                        label="场景"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_type.text"
                                                        label="订单类型"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="create_time"
                                                        label="操作时间"
                                                        width="220">
                                                </el-table-column>
<!--                                                <el-table-column-->
<!--                                                        label="操作"-->
<!--                                                        width="180">-->
<!--                                                    <template slot-scope="scope">-->
<!--                                                        <el-button type="text" size="small">-->
<!--                                                            <el-link type="primary" :underline="false" :underline="false" @click="orderInfo(scope)" target="_self">订单信息</el-link>-->
<!--                                                        </el-button>-->
<!--                                                    </template>-->
<!--                                                </el-table-column>-->
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

                        <el-drawer
                                :visible.sync="drawer"
                                :with-header="false">
                            <el-container>
                                <el-main>

                                </el-main>
                            </el-container>
                        </el-drawer>

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
                page: 1,
                size: 15,
                list:[],
                total: 0,
                drawer: false,
                plusColor: '#67C23A',
                minusColor: '#F56C6C',
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
                typeList: <?= json_encode($typeList) ?>,
                type: '0',
                total_in: 0,
                total_out: 0,
                in_: 0,
                out: 0
            },
            methods:{
                search(page){
                    this.page = page;
                    this.getIncomeList();
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

                getIncomeList: function(){
                    let start_time = '';
                    let end_time = '';
                    if(this.date){
                        start_time = this.initDate(this.date[0])
                        end_time = this.initDate(this.date[1])
                    }
                    let that = this;
                    let page = this.page;
                    let type = parseInt(this.type)
                    $.post("<?= url('finance.income.index/incomeList') ?>", {page, start_time, end_time, type}, function(res){
                        that.list = res.data.list;
                        that.total = res.data.total;
                        if(!start_time && !type){
                            that.total_in = res.data.count.in;
                            that.total_out = res.data.count.out;
                        }
                        that.in_ = res.data.count.in;
                        that.out = res.data.count.out;
                    }, 'json')
                },

                orderInfo: function(scope){
                    this.drawer = true;
                    let detail = this.list[scope.$index];
                    let [order_no, order_type] = [detail.order_no, detail.order_type.value]
                    $.post("<?= url('finance.income.index/orderInfo') ?>", {order_no, order_type}, function(res){

                    }, 'json')
                }

            },
            computed:{

            },
            created: function(){
                this.getIncomeList();
            }
        });


    });

    function getIncomeList(page=1){
        App.cur_page = page;
        App.getIncomeList();
    }
</script>

