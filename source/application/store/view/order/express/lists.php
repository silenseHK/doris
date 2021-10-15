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
                    <div class="widget-title am-cf">发货列表</div>
                </div>
                <div class="widget-body am-fr" id="wrap" v-cloak>

                    <el-container>
                        <el-header>
                            <el-button type="primary" plain size="medium" @click="request_express">批量生成面单</el-button>
                            <el-button type="primary" plain size="medium" @click="print_express">批量打印面单</el-button>
                            <el-button type="primary" plain size="medium" @click="confirm_delivery">批量确认发货</el-button>
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

                            <el-select v-model="status" placeholder="请选择">
                                <el-option value="0" label="全部"></el-option>
                                <el-option
                                        v-for="item in status_list"
                                        :key="item.value"
                                        :label="item.name"
                                        :value="item.value">
                                </el-option>
                            </el-select>

                            <el-input style="width:240px;" v-model="order_no" placeholder="订单号|物流单号"></el-input>

                            <el-button type="primary" plain size="medium" @click="ajax_go(1)">搜索</el-button>
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
                                        prop="delivery_status.name"
                                        label="发货状态"
                                        width="160">
                                </el-table-column>
                                <el-table-column
                                        prop="goods_name"
                                        label="商品名"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="goods_attr"
                                        label="商品规格"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        label="商品图片"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-image style="width: 80px; height: 80px" :src="scope.row.image.file_path"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="receive_user"
                                        label="收货人"
                                        width="100">
                                </el-table-column>
                                <el-table-column
                                        prop="receive_mobile"
                                        label="收货人电话"
                                        width="160">
                                </el-table-column>
                                <el-table-column
                                        prop="receive_address"
                                        label="收货地址"
                                        width="240">
                                </el-table-column>
                                <el-table-column
                                        prop="express_no"
                                        label="快递单号"
                                        width="180">
                                </el-table-column>
                                <el-table-column
                                        prop="express.express_name"
                                        label="快递公司"
                                        width="140">
                                </el-table-column>
                                <el-table-column
                                        prop="delivery_time.date"
                                        label="发货时间"
                                        width="200">
                                </el-table-column>
                                <el-table-column
                                        fixed="right"
                                        label="操作"
                                        width="240">
                                    <template slot-scope="scope">
                                        <el-row :gutter="10" type="flex">
                                            <el-col :span="10"><el-button @click="showDetail(scope)" type="success" size="mini" plain>进度详情</el-button></el-col>
                                            <el-col v-if="scope.row.delivery_status.value == 10 || scope.row.delivery_status.value == 20" :span="10"><el-button @click="printTask(scope)" type="warning" size="mini" plain>打印面单</el-button></el-col>
                                        </el-row>
                                        <el-row :gutter="10" type="flex" style="margin-top:5px;">
                                            <el-col v-if="scope.row.delivery_status.value == 20" :span="10"><el-button @click="delivery(scope)" type="primary" size="mini" plain>确认发货</el-button></el-col>
                                            <el-col v-if="scope.row.delivery_status.value == 10 || scope.row.delivery_status.value == 20" :span="10"><el-button type="danger" size="mini" @click="cancelDelivery(scope)" plain>取消发货</el-button></el-col>
                                        </el-row>
                                    </template>
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

                    <el-drawer
                            size="30%"
                            title="我是标题"
                            :visible.sync="show_detail"
                            :with-header="false">
                        <el-container>
                            <el-header style="height:800px;padding:20px;">
                                    <el-steps direction="vertical" :active="step">
                                        <el-step title="待发货">
                                            <el-container slot="description">
                                                <el-main>
                                                    <el-row :gutter="20" style="color:#555;">
                                                        <el-col :span="4">操作时间：</el-col>
                                                        <el-col :span="12">{{detail.create_time}}</el-col>
                                                    </el-row>
                                                    <el-row :gutter="20" style="color:#555;margin-top:20px;">
                                                        <el-col :span="4">备注：</el-col>
                                                        <el-col :span="12">{{detail.delivery_remark?detail.delivery_remark:"--"}}</el-col>
                                                    </el-row>
                                                </el-main>
                                            </el-container>
                                        </el-step>

                                        <el-step title="发货中">
                                            <el-container slot="description">
                                                <el-main>
                                                    <el-row :gutter="20" style="color:#555;">
                                                        <el-col :span="4">操作时间：</el-col>
                                                        <el-col :span="12">{{detail.wait_delivery_time?detail.wait_delivery_time.date:"--"}}</el-col>
                                                    </el-row>
                                                    <el-row :gutter="20" style="color:#555;margin-top:20px;">
                                                        <el-col :span="4">备注：</el-col>
                                                        <el-col :span="12">--</el-col>
                                                    </el-row>
                                                </el-main>
                                            </el-container>
                                        </el-step>
                                        <el-step title="已发货">
                                            <el-container slot="description">
                                                <el-main>
                                                    <el-row :gutter="20" style="color:#555;">
                                                        <el-col :span="4">操作时间：</el-col>
                                                        <el-col :span="12">{{detail.delivery_time?detail.delivery_time.date:"--"}}</el-col>
                                                    </el-row>
                                                    <el-row :gutter="20" style="color:#555;margin-top:20px;">
                                                        <el-col :span="4">备注：</el-col>
                                                        <el-col :span="12">{{detail.remark?detail.remark:"--"}}</el-col>
                                                    </el-row>
                                                </el-main>
                                            </el-container>
                                        </el-step>
                                        <el-step title="已取消">
                                            <el-container slot="description">
                                                <el-main>
                                                    <el-row :gutter="20" style="color:#555;">
                                                        <el-col :span="4">操作时间：</el-col>
                                                        <el-col :span="12">{{detail.cancel_time?detail.cancel_time.date:"--"}}</el-col>
                                                    </el-row>
                                                    <el-row :gutter="20" style="color:#555;margin-top:20px;">
                                                        <el-col :span="4">备注：</el-col>
                                                        <el-col :span="12">{{detail.cancel_remark?detail.cancel_remark:"--"}}</el-col>
                                                    </el-row>
                                                </el-main>
                                            </el-container>
                                        </el-step>
                                    </el-steps>
                            </el-header>
                        </el-container>
                    </el-drawer>

                    <el-dialog title="物流信息" :visible.sync="choose_express" width="20%">
                        <el-form :model="express">
                            <el-form-item label="物流公司" label-width="80px">
                                <el-select style="width:90%;" v-model="express.express_id" placeholder="请选择物流公司">
                                    <el-option v-for="(v, k) in express.express_list" :key="v.express_id" :label="v.express_name" :value="v.express_id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="备注" label-width="80px">
                                <el-input
                                        style="width:90%"
                                        type="textarea"
                                        placeholder="请输入内容"
                                        v-model="express.remark"
                                        maxlength="200"
                                        show-word-limit
                                        :autosize="{ minRows: 2, maxRows: 4}"
                                >
                                </el-input>
                            </el-form-item>
                            <el-form-item label-width="80px">
                                <el-button type="info" plain @click="printExpressImage">打印面单</el-button>
                            </el-form-item>
                        </el-form>
                    </el-dialog>

                    <el-dialog title="确认发货" :visible.sync="delivery_wrap" width="20%">
                        <el-form :model="deliverys">
                            <el-form-item label="备注" label-width="80px">
                                <el-input
                                        style="width:90%"
                                        type="textarea"
                                        placeholder="请输入内容"
                                        v-model="deliverys.remark"
                                        maxlength="200"
                                        show-word-limit
                                        :autosize="{ minRows: 2, maxRows: 4}"
                                >
                                </el-input>
                            </el-form-item>
                            <el-form-item label-width="80px">
                                <el-button type="info" plain @click="confirmDelivery">确认发货</el-button>
                            </el-form-item>
                        </el-form>
                    </el-dialog>

                    <el-dialog title="取消发货" :visible.sync="cancel_delivery" width="20%">
                        <el-form>
                            <el-form-item label="备注" label-width="80px">
                                <el-input
                                        style="width:90%"
                                        type="textarea"
                                        placeholder="请输入内容"
                                        v-model="cancel_remark"
                                        maxlength="200"
                                        show-word-limit
                                        :autosize="{ minRows: 2, maxRows: 4}"
                                >
                                </el-input>
                            </el-form-item>
                            <el-form-item label-width="80px">
                                <el-button type="info" plain @click="handleCancelDelivery">取消发货</el-button>
                            </el-form-item>
                        </el-form>
                    </el-dialog>

                    <el-dialog title="批量生成面单" :visible.sync="batch_request_express" width="80%" v-loading="request_express_loading">
                        <el-table
                                ref="multipleTable"
                                :data="wait_request_list"
                                tooltip-effect="dark"
                                style="width: 100%"
                                border
                                @selection-change="handleSelectionChange">
                            <el-table-column
                                    type="selection"
                                    width="55">
                            </el-table-column>
                            <el-table-column
                                    label="订单号"
                                    prop="order_no">
                            </el-table-column>
                            <el-table-column
                                    label="商品图片"
                                    width="150">
                                <template slot-scope="scope">
                                    <el-image style="width: 80px; height: 80px" :src="scope.row.image.file_path"></el-image>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    prop="goods_name"
                                    label="商品名">
                            </el-table-column>
                            <el-table-column
                                    prop="goods_attr"
                                    label="商品规格">
                            </el-table-column>
                            <el-table-column
                                    prop="goods_num"
                                    label="商品数量">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_user"
                                    label="收货人"
                                    width="100">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_mobile"
                                    label="收货人电话"
                                    width="160">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_address"
                                    label="收货地址"
                                    width="240">
                            </el-table-column>
                        </el-table>

                        <el-form :model="express" style="margin-top:20px;">
                            <el-form-item label="物流公司" label-width="80px">
                                <el-select style="width:200px;" v-model="express.express_id" placeholder="请选择物流公司">
                                    <el-option v-for="(v, k) in express.express_list" :key="v.express_id" :label="v.express_name" :value="v.express_id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="备注" label-width="80px">
                                <el-input
                                        style="width:200px"
                                        type="textarea"
                                        placeholder="请输入内容"
                                        v-model="express.remark"
                                        maxlength="200"
                                        show-word-limit
                                        :autosize="{ minRows: 2, maxRows: 4}"
                                >
                                </el-input>
                            </el-form-item>

                            <el-form-item label="" label-width="80px">
                                <el-button type="warning" @click="requestExpress">生成面单</el-button>
                            </el-form-item>
                        </el-form>
                    </el-dialog>

                    <el-dialog title="批量打印面单" :visible.sync="batch_print_express" width="80%" v-loading="print_express_loading">
                        <el-table
                                ref="multipleTable"
                                :data="print_list"
                                tooltip-effect="dark"
                                style="width: 100%"
                                border
                                @selection-change="handleSelectionChange2">
                            <el-table-column
                                    type="selection"
                                    width="55">
                            </el-table-column>
                            <el-table-column
                                    label="订单号"
                                    prop="order_no">
                            </el-table-column>
                            <el-table-column
                                    label="商品图片"
                                    width="150">
                                <template slot-scope="scope">
                                    <el-image style="width: 80px; height: 80px" :src="scope.row.image.file_path"></el-image>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    prop="goods_name"
                                    label="商品名">
                            </el-table-column>
                            <el-table-column
                                    prop="goods_attr"
                                    label="商品规格">
                            </el-table-column>
                            <el-table-column
                                    prop="goods_num"
                                    label="商品数量">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_user"
                                    label="收货人"
                                    width="100">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_mobile"
                                    label="收货人电话"
                                    width="160">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_address"
                                    label="收货地址"
                                    width="240">
                            </el-table-column>
                        </el-table>

                        <el-button style="margin-top:20px;" type="warning" @click="printExpress">生成面单</el-button>
                    </el-dialog>

                    <el-dialog title="批量确认发货" :visible.sync="batch_confirm_express" width="80%" v-loading="confirm_express_loading">
                        <el-table
                                ref="multipleTable"
                                :data="confirm_list"
                                tooltip-effect="dark"
                                style="width: 100%"
                                border
                                @selection-change="handleSelectionChange3">
                            <el-table-column
                                    type="selection"
                                    width="55">
                            </el-table-column>
                            <el-table-column
                                    label="订单号"
                                    prop="order_no">
                            </el-table-column>
                            <el-table-column
                                    label="商品图片"
                                    width="150">
                                <template slot-scope="scope">
                                    <el-image style="width: 80px; height: 80px" :src="scope.row.image.file_path"></el-image>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    prop="goods_name"
                                    label="商品名">
                            </el-table-column>
                            <el-table-column
                                    prop="goods_attr"
                                    label="商品规格">
                            </el-table-column>
                            <el-table-column
                                    prop="goods_num"
                                    label="商品数量">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_user"
                                    label="收货人"
                                    width="100">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_mobile"
                                    label="收货人电话"
                                    width="160">
                            </el-table-column>
                            <el-table-column
                                    prop="receive_address"
                                    label="收货地址"
                                    width="240">
                            </el-table-column>
                        </el-table>

                        <el-form style="margin-top:20px;">
                            <el-form-item label="备注" label-width="80px">
                                <el-input
                                        style="width:200px"
                                        type="textarea"
                                        placeholder="请输入内容"
                                        v-model="delivery_remark"
                                        maxlength="200"
                                        show-word-limit
                                        :autosize="{ minRows: 2, maxRows: 4}"
                                >
                                </el-input>
                            </el-form-item>

                            <el-form-item label="" label-width="80px">
                                <el-button type="warning" @click="batchConfirmDelivery">确认发货</el-button>
                            </el-form-item>
                        </el-form>

                    </el-dialog>

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
                tableData: [],
                page: "",
                cur_page: 1,
                status_list: <?= json_encode($statusList) ?>,
                status: '0',
                order_no: '',
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
                express:{
                    express_list: [
                        {
                            express_id: 10001,
                            express_name: '顺丰速运'
                        }
                    ],
                    express_id: '',
                    remark: ''
                },
                deliverys:{
                    remark: '',
                    id: 0,
                },

                date:'',
                total: '',
                show_detail: false,
                step: 1,
                detail:{},
                choose_express: false,
                delivery_wrap: false,
                wait_request_list: [],
                batch_request_express: false,
                request_express_loading: false,
                question_express_select: [],

                batch_print_express: false,
                print_express_loading: false,
                print_list: [],
                question_express_select2: [],

                batch_confirm_express: false,
                confirm_express_loading: false,
                confirm_list: [],
                question_express_select3: [],
                delivery_remark: '',

                cancel_delivery: false,
                cancel_remark: '',
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

                ajax_go:function(page){
                    this.cur_page = page;
                    let start_time = '';
                    let end_time = '';
                    if(this.date){
                        start_time = this.initDate(this.date[0])
                        end_time = this.initDate(this.date[1])
                    }
                    let [order_no, status] = [this.order_no, this.status];
                    let that = this;
                    $.post("<?= url('order.express/expressList') ?>", {start_time,end_time,page,order_no,status}, function(res){
                        if(res.code == 1){
                            let data = res.data.list;
                            // data.forEach(function(v,k){
                            //     let address = '';
                            //     Object.values(v.address.region).forEach((vv,kk)=>{
                            //         address = address += vv;
                            //     })
                            //     address += v.address.detail;
                            //     data[k].address['address'] = address;
                            // });
                            that.tableData = data;
                            that.page = res.data.page;
                            that.total = res.data.total;
                        }
                    }, 'json')
                },

                showDetail: function(scope){
                    let detail = this.tableData[scope.$index]
                    let step = 1;
                    switch(detail.delivery_status.value){
                        case 10:
                            step = 1;
                            break;
                        case 20:
                            step = 2;
                            break;
                        case 30:
                            step = 3;
                            break;
                        case 40:
                            step = 4;
                            break;
                    }
                    this.step = step;
                    this.detail = detail;
                    this.show_detail = true;
                },

                printTask: function(scope){
                    let detail = this.tableData[scope.$index];
                    if(detail.express_html){
                        let obj = detail.express_html
                        // window.print();return false;
                        $(obj).jqprint(
                            {
                                debug: false, //如果是true则可以显示iframe查看效果（iframe默认高和宽都很小，可以再源码中调大），默认是false
                                importCSS: true, //true表示引进原来的页面的css，默认是true。（如果是true，先会找$("link[media=print]")，若没有会去找$("link")中的css文件）
                                printContainer: true, //表示如果原来选择的对象必须被纳入打印（注意：设置为false可能会打破你的CSS规则）。
                                operaSupport: false//表示如果插件也必须支持歌opera浏览器，在这种情况下，它提供了建立一个临时的打印选项卡。
                            }
                        );
                    }else{
                        this.print_id = detail.id;
                        this.choose_express = true;
                    }
                },

                printOrder: function(scope){
                    let detail = this.tableData[scope.$index];
                    if(detail.print_num > 0){
                        const loading = this.$loading({
                            lock: true,
                            text: '请求中...',
                            spinner: 'el-icon-loading',
                            background: 'rgba(0, 0, 0, 0.7)'
                        });
                        this.ajaxPrint({id:this.print_id}, loading);
                    }else{
                        this.print_id = detail.id;
                        this.choose_express = true;
                    }
                },

                testPrint(){
                    let html_ = this.tableData[1].express_html;
                    console.log(html_);
                    let obj = html_;
                            $(obj).jqprint(
                                {
                                    debug: false, //如果是true则可以显示iframe查看效果（iframe默认高和宽都很小，可以再源码中调大），默认是false
                                    importCSS: true, //true表示引进原来的页面的css，默认是true。（如果是true，先会找$("link[media=print]")，若没有会去找$("link")中的css文件）
                                    printContainer: true, //表示如果原来选择的对象必须被纳入打印（注意：设置为false可能会打破你的CSS规则）。
                                    operaSupport: true//表示如果插件也必须支持歌opera浏览器，在这种情况下，它提供了建立一个临时的打印选项卡。
                                }
                            );
                },

                printExpressImage(){
                    let [express_id, remark, id] = [this.express.express_id, this.express.remark, this.print_id];
                    if(!express_id){
                        this.$message({
                            message: '请选择物流',
                            type: 'warning'
                        });
                        return false;
                    }
                    this.choose_express = false;
                    const loading = this.$loading({
                        lock: true,
                        text: '请求中...',
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.7)'
                    });
                    let that = this;
                    $.post("<?= url('order.express/expressImage') ?>", {express_id, remark, id}, function(res){
                        loading.close();
                        if(res.code == 1){
                            that.ajax_go(that.cur_page);
                            let obj = res.data;
                            $(obj).jqprint(
                                {
                                    debug: false, //如果是true则可以显示iframe查看效果（iframe默认高和宽都很小，可以再源码中调大），默认是false
                                    importCSS: true, //true表示引进原来的页面的css，默认是true。（如果是true，先会找$("link[media=print]")，若没有会去找$("link")中的css文件）
                                    printContainer: true, //表示如果原来选择的对象必须被纳入打印（注意：设置为false可能会打破你的CSS规则）。
                                    operaSupport: true//表示如果插件也必须支持歌opera浏览器，在这种情况下，它提供了建立一个临时的打印选项卡。
                                }
                            );
                        }else{
                            that.$message({
                                message: res.msg,
                                type: 'warning'
                            });
                        }
                    }, 'json')
                },

                printOrderTask(){
                    let [express_id, remark, id] = [this.express.express_id, this.express.remark, this.print_id];
                    if(!express_id){
                        this.$message({
                            message: '请选择物流',
                            type: 'warning'
                        });
                        return false;
                    }
                    this.choose_express = false;
                    const loading = this.$loading({
                        lock: true,
                        text: '请求中...',
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.7)'
                    });
                    this.ajaxPrint({express_id, remark, id}, loading);
                },

                ajaxPrint: function(data, loading){
                    let that = this;
                    $.post("<?= url('order.express/printOrder') ?>", data, function(res){
                        loading.close();
                        if(res.code == 1){
                            that.ajax_go(that.cur_page);
                            that.$message(res.msg);
                        }else{
                            that.$message({
                                message: res.msg,
                                type: 'warning'
                            });
                        }
                    }, 'json')
                },

                expressCompanyList: function(){
                    let that = this;
                    $.post("<?= url('order.express/expressCompanyList') ?>", {}, function(res){
                        that.express.express_list = res.data;
                    }, 'json');
                },

                delivery: function(scope){
                    this.deliverys.id = this.tableData[scope.$index].id;
                    this.delivery_wrap = true;
                },

                confirmDelivery: function(){
                    let [id, remark] = [this.deliverys.id, this.deliverys.remark];
                    this.delivery_wrap = false;
                    let that = this;
                    $.post("<?= url('order.express/confirmDelivery') ?>", {id, remark}, function(res){
                        if(res.code == 1){
                            that.$message(res.msg);
                            that.ajax_go(that.cur_page);
                        }else{
                            that.$message({
                                message: res.msg,
                                type: 'warning'
                            });
                        }
                    }, 'json');
                },

                request_express: function(){
                    this.request_express_loading = true;
                    this.batch_request_express = true;
                    this.ids = [];
                    this.question_express_select = [];
                    let that = this;
                    $.post("<?= url('order.express/waitExpressImageList') ?>", {}, function(res){
                        that.request_express_loading = false;
                        if(res.code == 1){
                            that.wait_request_list = res.data.list;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },

                handleSelectionChange: function(e){
                    this.question_express_select = e;
                },

                handleSelectionChange2: function(e){
                    this.question_express_select2 = e;
                },

                handleSelectionChange3: function(e){
                    this.question_express_select3 = e;
                },

                requestExpress(){
                    let question_express_select = this.question_express_select
                    if(question_express_select.length <= 0){
                        this.$message.error('请选择订单');
                        return false;
                    }
                    if(question_express_select.length > 10){
                        this.$message.error('一次最多生成10张面单');
                        return false;
                    }
                    let [express_id, remark] = [this.express.express_id, this.express.remark];
                    if(!express_id){
                        this.$message({
                            message: '请选择物流',
                            type: 'warning'
                        });
                        return false;
                    }
                    this.choose_express = false;
                    let ids = [];
                    question_express_select.forEach((v, k)=>{
                        ids.push(v.id)
                    })
                    let that = this;
                    this.batch_request_express = false;
                    $.post("<?= url('order.express/batchExpressImage') ?>", {ids, express_id, remark}, function(res){
                        if(res.code == 1){
                            that.express.express_id = 0;
                            that.express.remark = '';
                            that.$message({
                                message: res.msg,
                                type: 'success'
                            });
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },

                print_express(){
                    this.batch_print_express = true;
                    this.print_express_loading = true;
                    this.question_express_select2 = [];
                    let that = this;
                    $.post("<?= url('order.express/waitPrintList') ?>", {}, function(res){
                        that.print_express_loading = false;
                        if(res.code == 1){
                            that.print_list = res.data.list;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },

                printExpress(){
                    let lists = this.question_express_select2;
                    if(lists.length <= 0){
                        this.$message.error('请选择订单');
                        return false;
                    }
                    if(lists.length > 20){
                        this.$message.error('一次最多打印20张面单');
                        return false;
                    }
                    let html_ = '';
                    lists.forEach((v, k)=>{
                        if(k == 0){
                            html_ += `<br />${v.express_html}`
                        }else{
                            html_ += `<br /><br />${v.express_html}`
                            html_ += `<br /><br />${v.express_html}`
                            html_ += `<br /><br />${v.express_html}`
                        }

                    })
                    this.batch_print_express = false;
                    $(html_).jqprint(
                        {
                            debug: false, //如果是true则可以显示iframe查看效果（iframe默认高和宽都很小，可以再源码中调大），默认是false
                            importCSS: true, //true表示引进原来的页面的css，默认是true。（如果是true，先会找$("link[media=print]")，若没有会去找$("link")中的css文件）
                            printContainer: true, //表示如果原来选择的对象必须被纳入打印（注意：设置为false可能会打破你的CSS规则）。
                            operaSupport: true//表示如果插件也必须支持歌opera浏览器，在这种情况下，它提供了建立一个临时的打印选项卡。
                        }
                    );
                },

                confirm_delivery(){
                    this.batch_confirm_express = true;
                    this.confirm_express_loading = true;
                    this.question_express_select3 = [];
                    let that = this;
                    $.post("<?= url('order.express/waitConfirmDeliveryList') ?>", {}, function(res){
                        that.confirm_express_loading = false;
                        if(res.code == 1){
                            that.confirm_list = res.data.list;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },

                batchConfirmDelivery(){
                    let lists = this.question_express_select3;
                    let remark = this.delivery_remark;
                    if(lists.length <= 0){
                        this.$message.error('请选择订单');
                        return false;
                    }
                    let ids = [];
                    lists.forEach((v, k)=>{
                        ids.push(v.id)
                    })
                    let that = this;
                    this.batch_confirm_express = true;
                    $.post("<?= url('order.express/batchConfirmDelivery') ?>", {ids, remark}, function(res){
                        if(res.code == 1){
                            that.$message({
                                message: res.msg,
                                type: 'success'
                            });
                            that.delivery_remark = '';
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },

                cancelDelivery(scope){
                    this.cancel_id = scope.row.id;
                    this.cancel_delivery = true;
                },

                handleCancelDelivery(){
                    let [id, remark] = [this.cancel_id, this.cancel_remark];
                    let that = this;
                    this.cancel_delivery = false;
                    $.post("<?= url('order.express/cancelDelivery') ?>", {id, remark}, function(res){
                        if(res.code == 1){
                            that.ajax_go(that.cur_page);
                            that.$message({
                                message: res.msg,
                                type: 'success'
                            });
                            that.cancel_remark = '';
                            that.cancel_id = 0;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },

            },
            computed:{

            },
            created: function(){
                this.ajax_go(1);
                this.expressCompanyList();
            }
        });

    });

    function ajax_go(page){
        App.ajax_go(page);
    }

</script>

