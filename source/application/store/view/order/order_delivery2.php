<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">自提发货订单</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2">
                                                <el-button type="success" icon="el-icon-download" @click="exportOrder" plain>订单导出</el-button>
                                            </el-col>
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
                                            <el-col :span="2">
                                                <el-select v-model="delivery_type" placeholder="配送方式">
                                                    <el-option :value="-1" label="全部配送方式"></el-option>
                                                    <el-option
                                                            v-for="item in delivery_type_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2">
                                                <el-select v-model="delivery_status" placeholder="订单状态">
                                                    <el-option
                                                            v-for="item in delivery_status_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input placeholder="请输入订单号" v-model="order_no"></el-input>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input placeholder="请输入用户电话/用户昵称" v-model="keywords"></el-input>
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
                                                                    :src="scope.row.spec.image.file_path"
                                                                    fit="fill"></el-image>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column width="160" align="center" label="商品名" prop="goods.goods_name"></el-table-column>
                                                    <el-table-column align="center" label="商品规格">
                                                        <template slot-scope="scope">
                                                            <el-row type="flex" style="flex-direction: column">
                                                                <span v-for="item in scope.row.spec.sku_list">{{item.spec_name}}:{{item.spec_value}}</span>
                                                            </el-row>
                                                        </template>
                                                    </el-table-column>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="数量">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">x{{scope.row.goods_num}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        width="120"
                                                        align="center"
                                                        label="实付金额">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">运费:{{scope.row.freight_money}}</el-link>
                                                    </template>
                                                </el-table-column>

                                                <el-table-column label="收货人">
                                                    <el-table-column label="姓名" align="center">
                                                        <template slot-scope="scope">
                                                            <el-link :underline="false" type="info">{{scope.row.receiver_user}}</el-link>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column label="电话" align="center">
                                                        <template slot-scope="scope">
                                                            <el-link :underline="false" type="info">{{scope.row.receiver_mobile}}</el-link>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column label="收货地址" align="center" width="150">
                                                        <template slot-scope="scope">
                                                            <el-link :underline="false" type="info">{{scope.row.address}}</el-link>
                                                        </template>
                                                    </el-table-column>
                                                </el-table-column>

                                                <el-table-column label="发货人">
                                                    <el-table-column align="center" label="昵称" prop="nickName"></el-table-column>
                                                    <el-table-column align="center" label="电话" prop="mobile"></el-table-column>
                                                    <el-table-column align="center" label="等级" prop="grade_name"></el-table-column>
                                                    <el-table-column align="center" label="用户id" prop="user_id"></el-table-column>
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
                                                        label="配送方式">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.deliver_type.text}}</el-link>
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
                                                                <el-link v-else :underline="false" type="warning">{{scope.row.pay_status.text}}</el-link>
                                                            </el-col>
                                                            <el-col>
                                                                订单状态:
                                                                <el-link v-if="scope.row.deliver_status.value == 10" :underline="false" type="warning">{{scope.row.deliver_status.text}}</el-link>
                                                                <el-link v-if="scope.row.deliver_status.value == 20" :underline="false" type="primary">{{scope.row.deliver_status.text}}</el-link>
                                                                <el-link v-if="scope.row.deliver_status.value == 30" :underline="false" type="danger">{{scope.row.deliver_status.text}}</el-link>
                                                                <el-link v-if="scope.row.deliver_status.value == 40" :underline="false" type="success">{{scope.row.deliver_status.text}}</el-link>
                                                            </el-col>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        width="155"
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
                                                                <el-button v-if="scope.row.pay_status.value == 20 && scope.row.deliver_type.value == 10 && scope.row.deliver_status.value == 10" type="success" plain size="mini" @click="goDetail(scope)">前往发货</el-button>
                                                            </el-col>
                                                            <el-col style="margin:2px 0; display:flex; justify-content: space-between;">
                                                                <el-button v-if="scope.row.pay_status.value == 20 && scope.row.deliver_type.value == 20 && scope.row.deliver_status.value == 20" type="success" plain size="mini" @click="submitSelfOrder(scope)">确认提货</el-button>
                                                                <el-button v-if="scope.row.pay_status.value == 20 && ((scope.row.deliver_status.value == 10 && scope.row.deliver_type.value == 10) || (scope.row.deliver_status.value == 20 && scope.row.deliver_type.value == 20))" type="warning" plain size="mini" @click="cancelOrder(scope)">取消{{scope.row.deliver_type.value == 10 ? "发货" : "提货"}}</el-button>
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
                delivery_status_list: [
                    {
                        text: '全部订单状态',
                        value: 0
                    },
                    {
                        text: '待发货',
                        value: 10
                    },
                    {
                        text: '已发货',
                        value: 20
                    },
                    {
                        text: '已取消',
                        value: 30
                    },
                    {
                        text: '已完成',
                        value: 40
                    }
                ],
                delivery_status: 0,
                delivery_type_list: <?= json_encode($delivery_type) ?>,
                delivery_type: -1,
                keywords: '',
                order_no: ''
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getOrderDeliveryList();
                },
                getOrderDeliveryList: function(){
                    let that = this;
                    let {page, keywords, delivery_status, order_no, delivery_type} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    $.post("<?= url('order/getOrderDeliveryList') ?>", {page, keywords, deliver_status:delivery_status, order_no, deliver_type:delivery_type, start_time, end_time}, function(res){
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
                goDetail(scope){
                    window.location.href = `<?= url('order/deliveryDetail') ?>/order_id/${scope.row.deliver_id}`
                },
                exportOrder(){
                    let {page, keywords, delivery_status, order_no, delivery_type} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let param = `keywords/${keywords}/deliver_status/${delivery_status}/deliver_type/${delivery_type}/order_no/${order_no}`;
                    // param = $.urlEncode(param);
                    window.location = `<?= url('order.delivery/export') ?>/${param}`;
                },
                cancelOrder(scope){
                    this.$confirm('确定取消发货/自提?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post('<?= url('order/cancelOrder') ?>', {deliver_id: scope.row.deliver_id}, (res)=>{
                            let type = "error";
                            if(res.code == 1){
                                type = 'success';
                                that.getOrderDeliveryList();
                            }
                            that.$message({
                                type,
                                message: res.msg
                            })
                        }, 'json')
                    }).catch();
                },
                submitSelfOrder(scope){
                    this.$confirm('确定客户已提货?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post('<?= url('order/submitSelfOrder') ?>', {deliver_id: scope.row.deliver_id}, (res)=>{
                            let type = "error";
                            if(res.code == 1){
                                type = 'success';
                                that.getOrderDeliveryList();
                            }
                            that.$message({
                                type,
                                message: res.msg
                            })
                        }, 'json')
                    }).catch();
                }
            },
            computed:{

            },
            created: function(){
                this.getOrderDeliveryList();
            }
        });


    });
</script>

