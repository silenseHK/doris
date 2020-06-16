<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>
    .item {
        margin-top: 10px;
        margin-right: 40px;
    }
</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">运费明细</div>
                </div>
                <div class="widget-body am-fr" id="wrap">

                    <el-container>
                        <el-header>
                            <el-badge :value="total_order_freight" class="item">
                                <el-link :underline="false" type="primary">消费订单运费</el-link>
                            </el-badge>
                        </el-header>
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
                            <el-button type="primary" plain size="medium" @click="ajax_order_freight_go(1)">搜索</el-button>
                        </el-header>
                        <el-main>
                            <el-table
                                    :data="tableData"
                                    size="medium"
                                    style="width: auto">
                                <el-table-column
                                        prop="order_no"
                                        label="订单号"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        prop="state_text"
                                        label="订单状态"
                                        width="160">
                                </el-table-column>
                                <el-table-column
                                        prop="goods[0].goods_name"
                                        label="商品名"
                                        width="240">
                                </el-table-column>
                                <el-table-column
                                        prop="goods[0].goods_attr"
                                        label="商品规格"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        label="商品图片"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-image style="width: 80px; height: 80px" :src="scope.row.goods[0].spec.image.file_path"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="user.nickName"
                                        label="下单客户"
                                        width="150">
                                </el-table-column>
                                <el-table-column
                                        prop="goods[0].total_num"
                                        label="购买数量"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="express_price"
                                        label="运费金额"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="pay_type"
                                        label="付款方式"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        prop="address.address"
                                        label="收货地址"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        prop="create_time"
                                        label="时间"
                                        width="240">
                                </el-table-column>
                            </el-table>
                        </el-main>
                        <el-footer>
                            <div class="am-u-lg-12 am-cf">
                                <div class="am-fr pagination-total am-margin-right">
                                    <div class="am-vertical-align-middle" v-html="page"></div>
                                </div>
                            </div>
                        </el-footer>
                    </el-container>

                    <el-container>
                        <el-header>
                            <el-badge :value="total_delivery_freight" class="item">
                                <el-link :underline="false" type="primary">提货发过订单运费</el-link>
                            </el-badge>
                        </el-header>
                        <el-header>
                            <el-date-picker
                                    v-model="date2"
                                    type="datetimerange"
                                    :picker-options="pickerOptions"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期"
                                    align="right">
                            </el-date-picker>
                            <el-button type="primary" plain size="medium" @click="ajax_delivery_go(1)">搜索</el-button>
                        </el-header>
                        <el-main>
                            <el-table
                                    :data="tableData2"
                                    size="medium"
                                    style="width: auto">
                                <el-table-column
                                        prop="order_no"
                                        label="订单号"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        prop="deliver_status.text"
                                        label="订单状态"
                                        width="160">
                                </el-table-column>
                                <el-table-column
                                        prop="goods.goods_name"
                                        label="商品名"
                                        width="240">
                                </el-table-column>
                                <el-table-column
                                        prop="spec_attr"
                                        label="商品规格"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        label="商品图片"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-image style="width: 80px; height: 80px" :src="scope.row.spec.image.file_path"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="user.nickName"
                                        label="下单客户"
                                        width="150">
                                </el-table-column>
                                <el-table-column
                                        prop="goods_num"
                                        label="购买数量"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="freight_money"
                                        label="运费金额"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="pay_type"
                                        label="付款方式"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        prop="address"
                                        label="收货地址"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        prop="create_time"
                                        label="时间"
                                        width="240">
                                </el-table-column>
                            </el-table>
                        </el-main>
                        <el-footer>
                            <div class="am-u-lg-12 am-cf">
                                <div class="am-fr pagination-total am-margin-right">
                                    <div class="am-vertical-align-middle" v-html="page2"></div>
                                </div>
                            </div>
                        </el-footer>
                    </el-container>

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
            el: '#wrap',
            data: {
                tableData: [],
                tableData2: [],
                page: "",
                page2: "",
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
                date2: '',
                total_order_freight: '',
                total_delivery_freight: '',
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

                ajax_order_freight_go:function(page){
                    let start_time = '';
                    let end_time = '';
                    if(this.date){
                        start_time = this.initDate(this.date[0])
                        end_time = this.initDate(this.date[1])
                    }
                    let that = this;
                    $.post("<?= url('user.order/orderfreight') ?>", {start_time,end_time,page}, function(res){
                        if(res.code == 1){
                            let data = res.data.list.data;
                            data.forEach(function(v,k){
                                let address = '';
                                Object.values(v.address.region).forEach((vv,kk)=>{
                                    address = address += vv;
                                })
                                address += v.address.detail;
                                data[k].address['address'] = address;
                            });
                            that.tableData = data;
                            that.page = res.data.page;
                            that.total_order_freight = res.data.total_freight;
                        }
                    }, 'json')
                },

                ajax_delivery_go:function(page){
                    let start_time = '';
                    let end_time = '';
                    if(this.date2){
                        start_time = this.initDate(this.date2[0])
                        end_time = this.initDate(this.date2[1])
                    }
                    let that = this;
                    $.post("<?= url('user.order/deliveryfreight') ?>", {start_time,end_time,page}, function(res){
                        if(res.code == 1){
                            let data = res.data.list.data;
                            data.forEach(function(v,k){
                                let spec_attr = '';
                                if(data[k].spec.sku_list){
                                    data[k].spec.sku_list.forEach((vv, kk)=>{
                                        spec_attr += `${vv.spec_name}:${vv.spec_value},`
                                    })
                                }
                                spec_attr = spec_attr.replace(/^(\s|,)+|(\s|,)+$/g, '')
                                data[k]['spec_attr'] = spec_attr;
                                data[k]['pay_type'] = '微信支付';
                            });
                            that.tableData2 = data;
                            that.page2 = res.data.page;
                            that.total_delivery_freight = res.data.total_freight;
                        }
                    }, 'json')
                },

            },
            computed:{

            },
            created: function(){
                this.ajax_order_freight_go();
                this.ajax_delivery_go();
            }
        });

    });

    function ajax_order_freight_go(page){
        App.ajax_order_freight_go(page);
    }

    function ajax_delivery_go(page){
        App.ajax_delivery_go(page);
    }

</script>

