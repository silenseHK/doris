<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">补货订单</div>
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
                                                            v-for="item in order_status_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="6">
                                                <el-input placeholder="请输入内容" v-model="keywords" class="input-with-select">
                                                    <el-select style="width:100px" v-model="search_type" slot="prepend" placeholder="请选择">
                                                        <el-option v-for="item in search_type_list" :label="item.text" :value="item.value"></el-option>
                                                    </el-select>
                                                </el-input>
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
                                                        fixed="left"
                                                        prop="order_no"
                                                        align="center"
                                                        label="订单号"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column label="商品信息">
                                                    <el-table-column
                                                            align="center"
                                                            label="商品图片"
                                                            width="100">
                                                        <template slot-scope="scope">
                                                            <el-image
                                                                    style="width: 70px; height: 70px"
                                                                    :src="scope.row.goods[0].image.file_path"
                                                                    fit="fill"></el-image>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column width="160" align="center" label="商品名" prop="goods[0].goods_name"></el-table-column>
                                                    <el-table-column align="center" label="商品规格" prop="goods[0].goods_attr"></el-table-column>
                                                    <el-table-column align="center" label="单价" prop="goods[0].goods_price"></el-table-column>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="数量">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">x{{scope.row.goods[0].total_num}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="实付金额">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.total_price}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        width="100"
                                                        align="center"
                                                        label="返利">
                                                    <template slot-scope="scope" >
                                                        <el-row v-if="scope.row.rebate_money > 0" type="flex" style="flex-direction: column;">
                                                            <el-col>{{scope.row.rebate_money}}</el-col>
                                                            <el-col>
                                                                <el-button type="primary" size="mini" @click="showRebate(scope)" plain>返利详情</el-button>
                                                            </el-col>
                                                        </el-row>
                                                        <el-link v-else :underline="false" type="info">无返利</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column label="进货方">
                                                    <el-table-column align="center" label="昵称" prop="user.nickName"></el-table-column>
                                                    <el-table-column align="center" label="等级" prop="user_grade.name"></el-table-column>
                                                    <el-table-column align="center" label="用户id" prop="user.user_id"></el-table-column>
                                                </el-table-column>
                                                <el-table-column label="出货方">
                                                    <el-table-column label="昵称" align="center">
                                                        <template slot-scope="scope">
                                                            <el-link v-if="scope.row.supply_user" :underline="false" type="info">{{scope.row.supply_user.nickName}}</el-link>
                                                            <el-link v-else :underline="false" type="info">平台</el-link>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column label="等级" align="center">
                                                        <template slot-scope="scope">
                                                            <el-link v-if="scope.row.supply_grade" :underline="false" type="info">{{scope.row.supply_grade.name}}</el-link>
                                                            <el-link v-else :underline="false" type="info"></el-link>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column label="用户id" align="center">
                                                        <template slot-scope="scope">
                                                            <el-link v-if="scope.row.supply_user" :underline="false" type="info">{{scope.row.supply_user.user_id}}</el-link>
                                                            <el-link v-else :underline="false" type="info"></el-link>
                                                        </template>
                                                    </el-table-column>
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        align="center"
                                                        label="付款状态">
                                                    <template slot-scope="scope">
                                                        <el-link v-if="scope.row.pay_status.value==20" :underline="false" type="primary">{{scope.row.pay_status.text}}</el-link>
                                                        <el-link v-else :underline="false" type="warning">{{scope.row.pay_status.text}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        align="center"
                                                        label="支付方式">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" style="flex-direction: column;">
                                                            <el-link :underline="false" type="info">{{scope.row.pay_type.text}}</el-link>
                                                            <el-link v-if="scope.row.transaction_id" :underline="false" type="info">{{scope.row.transaction_id}}</el-link>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        width="180"
                                                        align="center"
                                                        prop="create_time"
                                                        label="创建时间">
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

                        <el-dialog
                                title="返利详情"
                                :visible.sync="show_rebate"
                                width="40%"
                                right>
                            <template>
                                <el-table
                                        border
                                        :data="rebate_info"
                                        style="width: 100%">
                                    <el-table-column
                                            width="80"
                                            prop="user_id"
                                            label="用户id">
                                    </el-table-column>
                                    <el-table-column
                                            prop="user.nickName"
                                            label="用户名">
                                    </el-table-column>
                                    <el-table-column
                                            prop="money"
                                            label="返利金额">
                                    </el-table-column>
                                    <el-table-column
                                            width="200"
                                            prop="remark"
                                            label="备注">
                                    </el-table-column>
                                    <el-table-column
                                            prop="grade"
                                            label="等级">
                                    </el-table-column>
                                </el-table>
                            </template>
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
                size: 15,
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
                scene_list: [],
                scene: 0,
                nickname: '',
                show_rebate: false,
                rebate_info: [],
                order_status_list: [
                    {
                        text: '全部',
                        value: -1,
                    },
                    {
                        text: '未付款',
                        value: 10,
                    },
                    {
                        text: '已付款',
                        value: 20,
                    }
                ],
                search_type_list: [
                    {
                        text: '订单号',
                        value: 10
                    },
                    {
                        text: '出货人',
                        value: 20
                    },
                    {
                        text: '进货人',
                        value: 30
                    }
                ],
                keywords: '',
                search_type: 10,
                order_status: -1
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getOrderStockList();
                },
                getOrderStockList: function(){
                    let that = this;
                    let {page, keywords, order_status, search_type} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    $.post("<?= url('order/getOrderStockList') ?>", {page, search:keywords, order_status, search_type, start_time, end_time}, function(res){
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
                showRebate(scope){
                    this.show_rebate = true;
                    this.rebate_info = scope.row.rebate_info
                },
            },
            computed:{

            },
            created: function(){
                this.getOrderStockList();
            }
        });


    });
</script>

