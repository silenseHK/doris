<?php
use app\common\enum\DeliveryType as DeliveryTypeEnum;
?>

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
                                    <el-header style="height:auto;">
                                        <el-row :gutter="10">
                                            <el-col :span="1.5"><el-button type="primary" plain @click="orderExport">订单导出</el-button></el-col>
                                            <el-col :span="1.5"><el-button type="primary" plain @click="deliveryExport">发货订单导出</el-button></el-col>
                                        </el-row>
                                        <el-row :gutter="10" style="margin-top:10px;">
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
                                                <el-select v-model="order_status" placeholder="请选择订单状态">
                                                    <el-option
                                                            v-for="item in order_status_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-select v-model="delivery_type" placeholder="请选择配送方式">
                                                    <el-option label="全部配送方式" :value="0"></el-option>
                                                    <el-option
                                                            v-if="item.value <= 20"
                                                            v-for="item in delivery_type_list"
                                                            :key="item.value"
                                                            :label="item.name"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-select v-model="shop_id" placeholder="请选择自提门店">
                                                    <el-option label="全部门店" :value="0"></el-option>
                                                    <el-option
                                                            v-for="item in shop_list"
                                                            :key="item.shop_id"
                                                            :label="item.shop_name"
                                                            :value="item.shop_id">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input placeholder="请输入订单号/用户昵称" v-model="keywords"></el-input>
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
                                                <el-table-column label="买家">
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
                                                        label="支付方式">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" style="flex-direction: column;">
                                                            <el-tooltip v-if="scope.row.pay_type.value == 20" class="item" effect="dark" :content="scope.row.transaction_id" placement="right">
                                                                <el-link :underline="false" type="primary">{{scope.row.pay_type.text}}</el-link>
                                                            </el-tooltip>
                                                            <el-link v-else :underline="false" type="info">{{scope.row.pay_type.text}}</el-link>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        align="center"
                                                        label="配送方式">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" style="flex-direction: column;">
                                                            <el-link v-if="scope.row.delivery_type.value == 10" :underline="false" type="primary">{{scope.row.delivery_type.text}}</el-link>
                                                            <el-link v-else :underline="false" type="success">{{scope.row.delivery_type.text}}</el-link>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        align="center"
                                                        width="150"
                                                        label="交易状态">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" style="flex-direction: column;">
                                                            <el-col>
                                                                付款状态:
                                                                <el-link v-if="scope.row.pay_status.value == 20" :underline="false" type="primary">{{scope.row.pay_status.text}}</el-link>
                                                                <el-link v-else :underline="false" type="info">{{scope.row.pay_status.text}}</el-link>
                                                            </el-col>
                                                            <el-col>
                                                                发货状态:
                                                                <el-link v-if="scope.row.delivery_status.value == 20" :underline="false" type="primary">{{scope.row.delivery_status.text}}</el-link>
                                                                <el-link v-else :underline="false" type="info">{{scope.row.delivery_status.text}}</el-link>
                                                            </el-col>
                                                            <el-col>
                                                                收货状态:
                                                                <el-link v-if="scope.row.receipt_status.value == 20" :underline="false" type="primary">{{scope.row.receipt_status.text}}</el-link>
                                                                <el-link v-else :underline="false" type="info">{{scope.row.receipt_status.text}}</el-link>
                                                            </el-col>
                                                            <el-col>
                                                                订单状态:
                                                                <el-link v-if="scope.row.order_status.value == 10" :underline="false" type="primary">{{scope.row.order_status.text}}</el-link>
                                                                <el-link v-if="scope.row.order_status.value == 20 || scope.row.order_status.value == 21" :underline="false" type="info">{{scope.row.order_status.text}}</el-link>
                                                                <el-link v-if="scope.row.order_status.value == 30" :underline="false" type="success">{{scope.row.order_status.text}}</el-link>
                                                                <el-link v-if="scope.row.order_status.value == 40" :underline="false" type="danger">{{scope.row.order_status.text}}</el-link>
                                                            </el-col>
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
                                                <el-table-column
                                                        fixed="right"
                                                        width="190"
                                                        align="center"
                                                        label="操作">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" style="flex-direction: column;">
                                                            <el-col style="margin:2px 0; display:flex; justify-content: space-between;">
                                                                <el-button type="success" plain size="mini" @click="goDetail(scope)">订单详情</el-button>
                                                                <el-button v-if="scope.row.pay_status.value == 20 && scope.row.delivery_status.value == 10" type="success" plain size="mini" @click="goDetail(scope)">前往发货</el-button>
                                                            </el-col>
                                                            <el-col style="margin:2px 0; display:flex; justify-content: space-between;">
                                                                <el-button v-if="scope.row.pay_status.value == 20 && scope.row.order_status.value != 20 && scope.row.order_status.value != 21 && scope.row.order_status.value != 40" type="warning" plain size="mini" @click="cancelOrder(scope)">订单退款</el-button>
                                                            </el-col>
                                                        </el-row>
                                                    </template>
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
                size: 10,
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
                nickname: '',
                show_rebate: false,
                rebate_info: [],
                delivery_type_list: <?= json_encode(DeliveryTypeEnum::data()) ?>,
                delivery_type: 0,
                shop_list: <?= json_encode($shopList) ?>,
                shop_id: 0,
                keywords: '',
                order_status_list: [
                    {
                        text: '全部订单',
                        value: 'all',
                    },
                    {
                        text: '待发货',
                        value: 'delivery',
                    },
                    {
                        text: '待收货',
                        value: 'receipt',
                    },
                    {
                        text: '待付款',
                        value: 'pay',
                    },
                    {
                        text: '已完成',
                        value: 'complete',
                    },
                    {
                        text: '已取消',
                        value: 'cancel',
                    },
                ],
                order_status: 'all'
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getOrderList();
                },
                getOrderList: function(){
                    let that = this;
                    let {page, keywords, order_status, shop_id, delivery_type} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    $.post("<?= url('order/getOrderList') ?>", {page, search:keywords, data_type:order_status, delivery_type, extract_shop_id:shop_id, start_time, end_time}, function(res){
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
                cancelOrder(scope){
                    this.$prompt('退款理由', '', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                    }).then(({ value }) => {
                        let that = this;
                        if(!$.trim(value)){
                            this.$message({
                                type: 'warning',
                                message: '请填写退款理由'
                            })
                            return false;
                        }
                        $.post("<?= url("order/refund") ?>", {order_id: scope.row.order_id}, function(res){
                            let type = 'error';
                            if(res.code == 1){
                                type = 'success';
                                that.getOrderList();
                            }
                            that.$message({
                                type,
                                message: res.msg
                            })
                        }, 'json')
                    }).catch();
                },
                goDetail(scope){
                    window.location = `<?= url('order/orderDetail') ?>/order_id/${scope.row.order_id}`;
                },
                orderExport(){
                    let that = this;
                    let {page, keywords, order_status, shop_id, delivery_type} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let param = `search/${keywords}/dataType/${order_status}/extract_shop_id/${shop_id}/delivery_type/${delivery_type}/start_time/${start_time}/end_time/${end_time}`;
                    // param = $.urlEncode(param);
                    window.location = `<?= url('order.operate/export') ?>/${param}`;
                },
                deliveryExport(){
                    let that = this;
                    let {page, keywords, order_status, shop_id, delivery_type} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let param = `search/${keywords}/dataType/${order_status}/extract_shop_id/${shop_id}/delivery_type/${delivery_type}/start_time/${start_time}/end_time/${end_time}`;
                    // param = $.urlEncode(param);
                    window.location = `<?= url('order.operate/deliveryexport') ?>/${param}`;
                },
            },
            computed:{

            },
            created: function(){
                this.getOrderList();
            }
        });


    });
</script>

