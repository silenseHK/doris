<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf" id="my-table">
                <el-container>
                    <el-header>
                        <el-steps :active="active" align-center style="margin-top:20px;" finish-status="success">
                            <el-step title="下单">
                                <el-link type="info" :underline="false" slot="description">下单于{{detail.create_time}}</el-link>
                            </el-step>
                            <el-step title="付款">
                                <el-link v-if="detail.pay_status.value==20" type="info" :underline="false" slot="description">支付于{{timeFormat(detail.pay_time)}}</el-link>
                            </el-step>
                            <el-step title="发货">
                                <el-link v-if="detail.deliver_status.value==20 || detail.deliver_status.value==40" type="info" :underline="false" slot="description">发货于{{timeFormat(detail.deliver_time)}}</el-link>
                            </el-step>
                            <el-step title="已完成">
                                <el-link v-if="detail.deliver_status.value==40" type="info" :underline="false" slot="description">完成于{{timeFormat(detail.complete_time)}}</el-link>
                            </el-step>
                            <el-step title="已取消"></el-step>
                        </el-steps>
                    </el-header>
                    <el-main style="margin-top:50px;">
                        <el-row>
                            <el-divider content-position="left">基本信息</el-divider>
                            <el-table
                                    :data="baseData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="order_no"
                                        label="订单号"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="receiver_user"
                                        label="提货人"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="freight_money"
                                        align="center"
                                        label="运费">
                                </el-table-column>
                                <el-table-column
                                        prop="deliver_type.text"
                                        align="center"
                                        label="配送方式">
                                </el-table-column>
                                <el-table-column
                                        align="center"
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
                            </el-table>
                        </el-row>
                        <el-row>
                            <el-divider content-position="left">商品信息</el-divider>
                            <el-table
                                    :data="goodsData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="goods_name"
                                        label="商品名称"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        label="商品图"
                                        align="center">
                                    <template slot-scope="scope">
                                        <el-image
                                                style="width: 80px; height: 80px"
                                                :src="scope.row.image"
                                                fit="fill"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="规格">
                                    <template slot-scope="scope">
                                        <el-row type="flex" style="flex-direction: column">
                                            <span v-for="item in scope.row.attr">{{item.spec_name}}:{{item.spec_value}}</span>
                                        </el-row>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="sku"
                                        align="center"
                                        label="商品编码">
                                </el-table-column>
                                <el-table-column
                                        prop="weight"
                                        align="center"
                                        label="重量(Kg)">
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="购买数量">
                                    <template slot-scope="scope">
                                        <el-link :underline="false" type="primary">x{{scope.row.goods_num}}</el-link>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-row>
                        <el-row v-if="detail.deliver_type.value == 10">
                            <el-divider content-position="left">收货信息</el-divider>
                            <el-table
                                    :data="receiverData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="receiver_user"
                                        label="收货人"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="receiver_mobile"
                                        label="收货电话"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="address"
                                        align="center"
                                        label="收货地址">
                                </el-table-column>

                            </el-table>
                        </el-row>
                        <el-row v-if="detail.deliver_type.value == 10">
                            <el-divider content-position="left">发货信息</el-divider>
                            <el-table
                                    v-if="(detail.deliver_status.value == 20 || detail.deliver_status.value == 40) && detail.pay_status.value == 20"
                                    :data="expressData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="express_name"
                                        label="物流公司"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="express_no"
                                        label="物流单号"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="remark"
                                        align="center"
                                        label="备注">
                                </el-table-column>
                                <el-table-column
                                        prop="deliver_time"
                                        align="center"
                                        label="发货时间">
                                </el-table-column>
                                <el-table-column
                                        v-if="detail.deliver_status.value == 20"
                                        align="center"
                                        label="操作">
                                    <template slot-scope="scope">
                                        <el-button type="warning" plain size="mini" @click="editExpress">修改物流</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-row v-if="detail.deliver_status.value == 10 && detail.deliver_type.value == 10 && detail.pay_status.value == 20">
                                <el-col :lg="9" :md="12">
                                    <el-form :label-position="labelPosition" label-width="80px" :model="expressForm">
                                        <el-form-item label="物流公司">
                                            <el-select v-model="expressForm.express_id" placeholder="请选择">
                                                <el-option
                                                        v-for="item in expressList"
                                                        :key="item.express_id"
                                                        :label="item.express_name"
                                                        :value="item.express_id">
                                                </el-option>
                                            </el-select>
                                            <span style="margin-left:20px;">可在</span><el-link :underline="false" type="primary" href="<?= url('setting.express/index') ?>">物流公司列表</el-link><span>中设置</span>
                                        </el-form-item>
                                        <el-form-item label="物流单号">
                                            <el-input v-model="expressForm.express_no"></el-input>
                                        </el-form-item>
                                        <el-form-item label="备注">
                                            <el-input
                                                    type="textarea"
                                                    placeholder="请输入内容"
                                                    v-model="expressForm.express_remark"
                                                    maxlength="255"
                                                    show-word-limit
                                            >
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="primary" plain @click="confirmExpress">确认发货</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </el-row>
                        <el-row v-if="detail.deliver_type.value == 20">
                            <el-divider content-position="left">自提核销</el-divider>
                            <el-table
                                    v-if="detail.pay_status.value == 20"
                                    :data="selfExpressData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="receiver_user"
                                        label="提货人"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="receiver_mobile"
                                        label="提货人电话"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="examine_time"
                                        label="核销时间"
                                        align="center">
                                </el-table-column>
                            </el-table>
                            <el-row v-if="detail.deliver_status.value == 20 && detail.pay_status.value == 20">
                                <el-col :lg="9" :md="12">
                                    <el-form :label-position="labelPosition" label-width="80px" :model="expressForm">
                                        <el-form-item label="买家取货状态">
                                            <el-radio-group v-model="examineForm.extract_status">
                                                <el-radio :label="1">已取货</el-radio>
                                            </el-radio-group>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="primary" plain @click="confirmExamine">确认核销</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </el-row>
                    </el-main>
                </el-container>

                <el-dialog
                        title="修改物流"
                        :visible.sync="show_edit_express"
                        width="40%"
                        right>
                    <el-form :label-position="labelPosition" label-width="80px" :model="expressEditForm">
                        <el-form-item label="物流公司">
                            <el-select v-model="expressEditForm.express_id" placeholder="请选择">
                                <el-option
                                        v-for="item in expressList"
                                        :key="item.express_id"
                                        :label="item.express_name"
                                        :value="item.express_id">
                                </el-option>
                            </el-select>
                            <span style="margin-left:20px;">可在</span><el-link :underline="false" type="primary" href="<?= url('setting.express/index') ?>">物流公司列表</el-link><span>中设置</span>
                        </el-form-item>
                        <el-form-item label="物流单号">
                            <el-input v-model="expressEditForm.express_no"></el-input>
                        </el-form-item>
                    </el-form>
                    <span slot="footer" class="dialog-footer">
                        <el-button @click="show_edit_express = false">取 消</el-button>
                        <el-button type="primary" @click="confirmEditExpress">确 定</el-button>
                    </span>
                </el-dialog>
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
                detail: <?= json_encode($detail)?>,
                expressList: <?= json_encode($expressList) ?>,
                baseData: [],
                goodsData: [],
                receiverData: [],
                expressData: [],
                labelPosition: 'top',
                expressForm: {
                    express_id: '',
                    express_no: '',
                    express_remark: ''
                },
                show_edit_express: false,
                expressEditForm: {
                    express_id: '',
                    express_no: ''
                },
                examineForm: {
                    extract_status: 1
                },
                selfExpressData: []
            },
            methods:{
                add0(m){ return m < 10 ? '0' + m : m },
                timeFormat(timestamp) {
                    let time = new Date(timestamp*1000);
                    let year = time.getFullYear();
                    let month = time.getMonth() + 1;
                    let date = time.getDate();
                    let hours = time.getHours();
                    let minutes = time.getMinutes();
                    let seconds = time.getSeconds();
                    return year + '-' + this.add0(month) + '-' + this.add0(date) + ' ' + this.add0(hours) + ':' + this.add0(minutes) + ':' + this.add0(seconds);
                },
                initData: function(){
                    let detail = this.detail;
                    let baseData = {order_no, receiver_user, freight_money, deliver_type, pay_status, deliver_status} = detail;
                    this.baseData = [baseData];
                    let goodsData = {
                        goods_name: detail.goods.goods_name,
                        image: detail.spec.image.file_path,
                        attr: detail.spec.sku_list,
                        sku: detail.spec.goods_no,
                        weight: detail.spec.goods_weight,
                        goods_num: detail.goods_num
                    };
                    this.goodsData = [goodsData];
                    let receiverData = {receiver_user, receiver_mobile, address} = detail;
                    this.receiverData = [receiverData];
                    let expressData = {
                        express_name: detail.express? detail.express.express_name : '',
                        express_no: detail.express_no,
                        remark: detail.express_remark,
                        deliver_time: this.timeFormat(detail.deliver_time),
                        deliver_id: detail.deliver_id
                    }
                    this.expressData = [expressData];
                    let selfExpressData = {
                        receiver_user: detail.receiver_user,
                        receiver_mobile: detail.receiver_mobile,
                        examine_time: this.timeFormat(detail.complete_time)
                    };
                    this.selfExpressData = [selfExpressData];
                },
                editExpress(){
                    let detail = this.detail;
                    this.expressEditForm = {
                        express_id: detail.express_id,
                        express_no: detail.express_no
                    }
                    this.show_edit_express = true;
                },
                confirmExpress(){
                    let {detail, expressForm} = this;
                    let order = {express_id, express_no} = expressForm;
                    if(!express_id){
                        this.$message({
                            type: 'warning',
                            message: '请选择物流公司'
                        })
                        return false;
                    }
                    if(!express_no){
                        this.$message({
                            type: 'warning',
                            message: '请填写物流单号'
                        })
                        return false;
                    }
                    let that = this;
                    $.post('<?= url('order/deliverOrderDeliver') ?>', {order, deliver_id: detail.deliver_id}, (res)=>{
                        if(res.code == 1){
                            location.reload();
                        }else{
                            that.$message({
                                type: 'error',
                                message: res.msg
                            })
                        }
                    }, 'json')
                },
                confirmEditExpress(){
                    let {detail, expressEditForm} = this;
                    let express = {express_id, express_no, express_remark} = expressEditForm;
                    if(!express_id){
                        this.$message({
                            type: 'warning',
                            message: '请选择物流公司'
                        })
                        return false;
                    }
                    if(!express_no){
                        this.$message({
                            type: 'warning',
                            message: '请填写物流单号'
                        })
                        return false;
                    }
                    let that = this;
                    $.post('<?= url('order/updateDeliveryExpress') ?>', {express, order_id: detail.deliver_id}, (res)=>{
                        if(res.code == 1){
                            location.reload();
                        }else{
                            that.$message({
                                type: 'error',
                                message: res.msg
                            })
                        }
                    }, 'json')
                },
                confirmExamine(){
                    let order = this.examineForm;
                    let detail = this.detail;
                    $.post('<?= url('order/submitSelfOrder') ?>', {order, deliver_id: detail.deliver_id}, (res)=>{
                        if(res.code == 1){
                            location.reload();
                        }else{
                            that.$message({
                                type: 'error',
                                message: res.msg
                            })
                        }
                    }, 'json')
                },
            },
            computed:{
                active: function(){
                    let [order_status, pay_status] = [this.detail.deliver_status.value, this.detail.pay_status.value];
                    let step = 1;
                    if(order_status == 10 && pay_status == 20){
                        step = 2;
                    }
                    if(order_status == 20)
                        step = 3
                    if(order_status == 40)
                        step = 4
                    if(order_status == 30)
                        step = 5
                    return step;
                },

            },
            created: function(){
                this.initData();
            }
        });


    });
</script>

