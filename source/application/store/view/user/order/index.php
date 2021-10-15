<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">明细</div>
                </div>
                <div class="widget-body am-fr" id="wrap">


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
                            <el-button type="primary" plain size="medium" @click="ajax_balance_go(1)">搜索</el-button>
                        </el-header>
                        <el-main>
                            <el-table
                                    :data="tableData"
                                    border
                                    size="medium"
                                    style="width: 100%">
                                <el-table-column
                                        prop="orders.order_no"
                                        label="订单号"
                                        width="160">
                                </el-table-column>
                                <el-table-column
                                        label="商品图片"
                                        width="120">
                                    <template slot-scope="scope">
                                        <el-image v-if="scope.row.order_id" style="width: 100px; height: 100px" :src="scope.row.orders.goods[0].spec.image.file_path"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="商品名"
                                        width="200">
                                    <template slot-scope="scope">
                                        <div class="demo-image__preview">
                                            <el-link v-if="scope.row.order_id > 0" :underline="false" type="info">{{scope.row.orders.goods[0].goods_name}}</el-link>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="status_text"
                                        label="状态"
                                        width="120">
                                </el-table-column>
                                <el-table-column
                                        prop="supply_user_name"
                                        label="出货人"
                                        width="150">
                                </el-table-column>
                                <el-table-column
                                        prop="supply_user_grade"
                                        label="出货人等级"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        prop="get_user_name"
                                        label="进货人"
                                        width="150">
                                </el-table-column>
                                <el-table-column
                                        prop="get_user_grade"
                                        label="进货人等级"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        label="变动数量"
                                        width="180">
                                    <template slot-scope="scope">
                                        <div class="demo-image__preview">
                                            <el-link v-if="scope.row.stock > 0" :underline="false" type="success">{{scope.row.stock}}{{scope.row.stock_mark}}</el-link>
                                            <el-link v-else="scope.row.stock" :underline="false" type="danger">{{scope.row.stock}}{{scope.row.stock_mark}}</el-link>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="prev_stock"
                                        label="库存"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        label="变动金额"
                                        width="150">
                                    <template slot-scope="scope">
                                        <div class="demo-image__preview">
                                            <el-link v-if="scope.row.money > 0" :underline="false" type="success">{{scope.row.money}}</el-link>
                                            <el-link v-else="scope.row.money" :underline="false" type="danger">{{scope.row.money}}</el-link>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="balance_money"
                                        label="之后余额"
                                        width="150">
                                </el-table-column>
                                <el-table-column
                                    prop="create_time"
                                    label="时间"
                                    width="200">
                                </el-table-column>
                                <el-table-column
                                    prop="scene.text"
                                    label="场景"
                                    width="170"
                                    fixed="right">
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
                            <el-link :underline="false" type="primary">提货发货记录</el-link>
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
                                        prop="goods.goods_name"
                                        label="商品名"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        label="商品图片"
                                        width="120">
                                    <template slot-scope="scope">
                                        <el-image style="width: 100px; height: 100px" :src="scope.row.spec.image.file_path"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="deliver_status.text"
                                        label="状态"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="user.nickName"
                                        label="出货人"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="user.grade.name"
                                        label="出货人等级"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        label="变动数量"
                                        width="180">
                                    <template slot-scope="scope">
                                        <div class="demo-image__preview">
                                            <el-link v-if="scope.row.stock_log.change_direction == 10" :underline="false" type="success">{{scope.row.stock_log.change_num}}</el-link>
                                            <el-link v-else="scope.row.stock" :underline="false" type="danger">-{{scope.row.stock_log.change_num}}</el-link>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="stock_log.balance_stock"
                                        label="库存"
                                        width="140">
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

                    <el-container>
                        <el-header>
                            <el-link :underline="false" type="primary">微信支付进货及后台充值库存记录</el-link>
                        </el-header>
                        <el-header>
                            <el-date-picker
                                v-model="date3"
                                type="datetimerange"
                                :picker-options="pickerOptions"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                align="right">
                            </el-date-picker>
                            <el-button type="primary" plain size="medium" @click="ajax_wxpay_go(1)">搜索</el-button>
                        </el-header>
                        <el-main>
                            <el-table
                                :data="tableData3"
                                size="medium"
                                style="width: auto">
                                <el-table-column
                                    prop="order_no"
                                    label="订单号"
                                    width="200">
                                </el-table-column>
                                <el-table-column
                                    prop="goods[0].goods_name"
                                    label="商品名"
                                    width="200">
                                </el-table-column>
                                <el-table-column
                                    label="商品图片"
                                    width="120">
                                    <template slot-scope="scope">
                                        <el-image style="width: 100px; height: 100px" :src="scope.row.goods[0].spec.image.file_path"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="status_text"
                                    label="状态"
                                    width="180">
                                </el-table-column>
                                <el-table-column
                                    prop="get_user_name"
                                    label="进货人"
                                    width="150">
                                </el-table-column>
                                <el-table-column
                                    prop="get_user_grade"
                                    label="进货人等级"
                                    width="140">
                                </el-table-column>
                                <el-table-column
                                        prop="supply_user_name"
                                        label="出货人"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="supply_user_grade"
                                        label="出货人等级"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                    label="变动数量"
                                    width="180">
                                    <template slot-scope="scope">
                                        <div class="demo-image__preview">
                                            <el-link :underline="false" type="success">{{scope.row.stock}}{{scope.row.stock_mark}}</el-link>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="prev_stock"
                                    label="库存"
                                    width="140">
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
                tableData3: [],
                user_id: <?= $user_id ?>,
                page: "",
                page2: "",
                page3:'',
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
                date3: '',
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
                initEditUrl:function(class_id){
                    return this.edit_url + "/class_id/" + class_id;
                },
                ajax_balance_go:function(page){
                    let start_time = '';
                    let end_time = '';
                    if(this.date){
                        start_time = this.initDate(this.date[0])
                        end_time = this.initDate(this.date[1])
                    }
                    let that = this;
                    $.post("<?= url('user.order/orderlist') ?>", {user_id:this.user_id,start_time,end_time,page}, function(res){
                        if(res.code == 1){
                            let data = res.data.list.data;
                            data.forEach(function(v,k){
                                data[k]['stock_mark'] = '';
                                if(v.order_id){
                                    data[k]['supply_user_name'] = v.orders.supply_user?v.orders.supply_user.nickName:'平台';
                                    data[k]['supply_user_grade'] = v.orders.supply_grade?v.orders.supply_grade.name:'';
                                    if(v.orders.user_id == that.user_id){ //进货人
                                        if(v.orders.delivery_type.value == 30){ //补货订单
                                            v.orders.stock_log.forEach(function(vv,kk){
                                                if(v.scene.value == 40){ //退款
                                                    if(vv.user_id == that.user_id && vv.change_direction == 20){
                                                        data[k]['stock'] = "+" + vv.change_num;
                                                        data[k]['prev_stock'] = vv.balance_stock;
                                                    }
                                                }else{ //补货
                                                    if(vv.user_id == that.user_id && vv.change_direction == 10){
                                                        data[k]['stock'] = "+" + vv.change_num;
                                                        data[k]['prev_stock'] = vv.balance_stock;
                                                    }
                                                }
                                            })
                                        }else{
                                            if(v.scene.value == 40){
                                                data[k]['stock'] = "-" + v.orders.goods[0].total_num;
                                            }else{
                                                data[k]['stock'] = "+" + v.orders.goods[0].total_num;
                                            }
                                            data[k]['stock_mark'] = "(不计入库存)";
                                            data[k]['prev_stock'] = 0;
                                        }
                                    }else if(v.orders.supply_user_id == that.user_id){//发货人
                                        v.orders.stock_log.forEach(function(vv,kk){
                                            if(v.scene.value == 40){
                                                if(vv.user_id == that.user_id && vv.change_direction == 10){
                                                    data[k]['stock'] =  "+" + vv.change_num;
                                                    data[k]['prev_stock'] = vv.balance_stock;
                                                }
                                            }else{
                                                if(vv.user_id == that.user_id && vv.change_direction == 20){
                                                    data[k]['stock'] =  "-" + vv.change_num;
                                                    data[k]['prev_stock'] = vv.balance_stock;
                                                }
                                            }
                                        })
                                    }else{//收到返利的人
                                        data[k]['stock'] =  "" ;
                                        data[k]['prev_stock'] = "";
                                    }
                                    data[k]['status_text'] = v.orders.order_status.text;
                                    data[k]['get_user_name'] = v.orders.user.nickName;
                                    data[k]['get_user_grade'] = v.orders.user_grade?v.orders.user_grade.name: '';
                                }else{
                                    data[k]['stock'] =  "" ;
                                    data[k]['prev_stock'] = "";
                                    data[k]['status_text'] = '';
                                    data[k]['get_user_name'] = '';
                                    data[k]['get_user_grade'] = '';
                                }
                            });
                            that.tableData = res.data.list.data
                            that.page = res.data.page;
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
                    $.post("<?= url('user.order/deliveryorderlist') ?>", {user_id:this.user_id,start_time,end_time,page}, function(res){
                        if(res.code == 1){
                            that.tableData2 = res.data.list.data;
                            that.page2 = res.data.page;
                        }
                    }, 'json')
                },

                ajax_wxpay_go:function(page){
                    let start_time = '';
                    let end_time = '';
                    if(this.date3){
                        start_time = this.initDate(this.date3[0])
                        end_time = this.initDate(this.date3[1])
                    }
                    let that = this;
                    $.post("<?= url('user.order/wxorderlist') ?>", {user_id:this.user_id,start_time,end_time,page}, function(res){
                        if(res.code == 1){
                            let data = res.data.list.data;
                            data.forEach(function(v,k){
                                data[k]['stock_mark'] = '';
                                data[k]['supply_user_name'] = v.supply_user?v.supply_user.nickName:'平台';
                                data[k]['supply_user_grade'] = v.supply_grade?v.supply_grade.name:'';
                                if(v.delivery_type.value == 30){ //补货订单
                                    v.stock_log.forEach(function(vv,kk){
                                        if(vv.user_id == that.user_id && vv.change_direction == 10){
                                            data[k]['stock'] = "+" + vv.change_num;
                                            data[k]['prev_stock'] = vv.balance_stock;
                                        }
                                    })
                                }else{
                                    data[k]['stock'] = "+" + v.goods[0].total_num;
                                    data[k]['stock_mark'] = "(不计入库存)";
                                    data[k]['prev_stock'] = 0;
                                }
                                data[k]['status_text'] = v.order_status.text;
                                data[k]['get_user_name'] = v.user.nickName;
                                data[k]['get_user_grade'] = v.user_grade ? v.user_grade.name : '';

                            });
                            that.tableData3 = res.data.list.data
                            that.page3 = res.data.page;
                        }
                    }, 'json')
                },

            },
            computed:{

            },
            created: function(){
                this.ajax_balance_go();
                this.ajax_delivery_go();
                this.ajax_wxpay_go();
            }
        });




    });

    function ajax_balance_go(page){
        App.ajax_balance_go(page);
    }

    function ajax_delivery_go(page){
        App.ajax_delivery_go(page);
    }

    function ajax_wxpay_go(page){
        App.ajax_wxpay_go(page);
    }

</script>

