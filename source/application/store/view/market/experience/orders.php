<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">体验装订单</div>
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
                                                <el-select v-model="order_status" placeholder="请选择">
                                                    <el-option
                                                            v-for="item in options"
                                                            :key="item.value"
                                                            :label="item.label"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
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
                                                        prop="order_data.order_no"
                                                        label="订单号"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_data.goods[0].goods_name"
                                                        label="商品名"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        label="商品图"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.order_data.goods[0].image.file_path"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_data.goods[0].goods_attr"
                                                        label="商品规格"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="user.nickName"
                                                        label="购买人"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="first_user.nickName"
                                                        label="直接推荐人"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="second_user.nickName"
                                                        label="间接推荐人"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_data.state_text"
                                                        label="订单状态"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_data.create_time"
                                                        label="下单时间"
                                                        width="220">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_data.delivery_type.text"
                                                        label="发货类型"
                                                        width="220">
                                                </el-table-column>
                                            </el-table>
                                        </template>

                                        <div class="am-u-lg-12 am-cf">
                                            <div class="am-fr" v-html="page"></div>
                                            <div class="am-fr pagination-total am-margin-right">
                                                <div class="am-vertical-align-middle">总记录：{{total}}</div>
                                            </div>
                                        </div>
                                    </el-main>
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
                date:'',
                options: [{
                    value: '0',
                    label: '全部'
                }, {
                    value: '1',
                    label: '待发货'
                }, {
                    value: '2',
                    label: '待提货'
                }, {
                    value: '3',
                    label: '已发货'
                }, {
                    value: '4',
                    label: '已完成'
                }],
                order_status: '0',
                keywords: ''
            },
            methods:{
                getRankList: function(page){
                    this.cur_page = page;
                    let that = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let [order_status, keywords] = [this.order_status, this.keywords];
                    $.post("<?= url('market.experience/getOrderList') ?>", {page, order_status, keywords, start_time, end_time}, function(res){
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

