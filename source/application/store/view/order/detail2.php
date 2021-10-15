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
                                <el-link v-if="detail.delivery_status.value==20" type="info" :underline="false" slot="description">发货于{{timeFormat(detail.delivery_time)}}</el-link>
                            </el-step>
                            <el-step title="收货">
                                <el-link v-if="detail.order_status.value==30" type="info" :underline="false" slot="description">收货于{{timeFormat(detail.receipt_time)}}</el-link>
                            </el-step>
                            <el-step title="已取消"></el-step>
                            <el-step title="已退款"></el-step>
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
                                <el-table-column label="买家">
                                    <el-table-column align="center" label="微信名" prop="user.nickName"></el-table-column>
                                    <el-table-column align="center" label="用户id" prop="user.user_id"></el-table-column>
                                </el-table-column>
                                <el-table-column
                                        label="订单金额">
                                    <el-table-column align="center" label="订单总额" prop="total_price"></el-table-column>
                                    <el-table-column align="center" label="运费金额" prop="express_price"></el-table-column>
                                    <el-table-column align="center" label="实付款金额">
                                        <template slot-scope="scope">
                                            <el-link :underline="false" type="primary">{{scope.row.pay_price}}</el-link>
                                        </template>
                                    </el-table-column>
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="支付方式">
                                    <template slot-scope="scope">
                                        <el-link :underline="false" type="primary">{{scope.row.pay_type.text}}</el-link>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="配送方式">
                                    <template slot-scope="scope">
                                        <el-link :underline="false" type="primary">{{scope.row.delivery_type.text}}</el-link>
                                    </template>
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
                                                发货状态:
                                                <el-link v-if="scope.row.delivery_status.value == 10" :underline="false" type="warning">{{scope.row.delivery_status.text}}</el-link>
                                                <el-link v-if="scope.row.delivery_status.value == 20" :underline="false" type="primary">{{scope.row.delivery_status.text}}</el-link>
                                            </el-col>
                                            <el-col>
                                                收货状态:
                                                <el-link v-if="scope.row.receipt_status.value == 10" :underline="false" type="warning">{{scope.row.receipt_status.text}}</el-link>
                                                <el-link v-if="scope.row.receipt_status.value == 20" :underline="false" type="primary">{{scope.row.receipt_status.text}}</el-link>
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
                                        prop="attr"
                                        align="center"
                                        label="规格">
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
                                        prop="goods_price"
                                        align="center"
                                        label="单价">
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="购买数量">
                                    <template slot-scope="scope">
                                        <el-link :underline="false" type="primary">x{{scope.row.goods_num}}</el-link>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="total_price"
                                        align="center"
                                        label="商品总价">
                                </el-table-column>
                            </el-table>
                        </el-row>
                        <el-row v-if="detail.delivery_type.value == 10">
                            <el-divider content-position="left">收货信息</el-divider>
                            <el-table
                                    :data="receiverData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="receiver_user"
                                        label="收货人"
                                        width="300"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="receiver_mobile"
                                        label="收货电话"
                                        width="300"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="address"
                                        align="center"
                                        label="收货地址">
                                </el-table-column>
                                <el-table-column
                                        prop="buyer_remark"
                                        align="center"
                                        label="用户备注">
                                </el-table-column>

                            </el-table>
                        </el-row>

                        <el-row v-if="detail.delivery_type.value == 20">
                            <el-divider content-position="left">自提信息</el-divider>
                            <el-row type="flex" style="flex-direction: column">
                                <el-col>联系人: <el-link :underline="false" type="primary">{{detail.extract.linkman}}</el-link></el-col>
                                <el-col>联系电话: <el-link :underline="false" type="primary">{{detail.extract.phone}}</el-link></el-col>
                            </el-row>
                        </el-row>

                        <el-row v-if="detail.delivery_type.value == 20">
                            <el-divider content-position="left">自提门店信息</el-divider>
                            <el-table
                                    :data="extractShopData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="shop_id"
                                        width="100"
                                        label="门店ID"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        label="门店logo"
                                        align="center">
                                    <template slot-scope="scope">
                                        <el-image
                                                style="width: 90px; height: 90px"
                                                :src="scope.row.logo"
                                                fit="fill"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="shop_name"
                                        align="center"
                                        label="门店名称">
                                </el-table-column>
                                <el-table-column
                                        prop="linkman"
                                        align="center"
                                        label="联系人">
                                </el-table-column>
                                <el-table-column
                                        prop="phone"
                                        align="center"
                                        label="联系电话">
                                </el-table-column>
                                <el-table-column
                                        width="400"
                                        prop="address"
                                        align="center"
                                        label="门店地址">
                                </el-table-column>

                            </el-table>
                        </el-row>

                        <el-row v-if="detail.pay_status.value == 20">
                            <el-divider content-position="left">付款信息</el-divider>
                            <el-table
                                    :data="payData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="pay_price"
                                        label="应付款金额"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="pay_type.text"
                                        label="支付方式"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="支付流水号">
                                    <template slot-scope="scope">
                                        <el-link :underline="false" type="primary">{{scope.row.transaction_id ? scope.row.transaction_id : '--'}}</el-link>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        align="center"
                                        label="付款状态">
                                    <template slot-scope="scope">
                                        <el-link :underline="false" type="primary">{{scope.row.pay_status.text}}</el-link>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="pay_time"
                                        align="center"
                                        label="付款时间">
                                </el-table-column>

                            </el-table>
                        </el-row>
                        <el-row v-if="detail.delivery_type.value == 10 && detail.pay_status.value == 20">
                            <el-divider content-position="left">发货信息</el-divider>
                            <el-table
                                    v-if="(detail.order_status.value == 10 || detail.order_status.value == 30) && detail.delivery_status.value == 20"
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
                                        v-if="detail.order_status.value == 10"
                                        align="center"
                                        label="操作">
                                    <template slot-scope="scope">
                                        <?php if (checkPrivilege('order/delivery')): ?>
                                        <el-button type="warning" plain size="mini" @click="editExpress">修改物流</el-button>
                                        <?php endif;?>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <?php if (checkPrivilege('order/delivery')): ?>
                            <el-row v-if="detail.order_status.value == 10 && detail.delivery_status.value == 10">
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
                                                    v-model="expressForm.delivery_remark"
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
                            <?php endif;?>
                        </el-row>
                        <el-row v-if="detail.delivery_type.value == 20 && detail.pay_status.value == 20">
                            <el-divider content-position="left">门店自提核销</el-divider>
                            <el-table
                                    v-if="detail.delivery_status.value == 20"
                                    :data="selfExpressData"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        prop="shop_name"
                                        label="自提门店名称"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="extract_clerk"
                                        label="核销员"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="extract_status"
                                        label="核销状态"
                                        align="center">
                                </el-table-column>
                                <el-table-column
                                        prop="examine_time"
                                        label="核销时间"
                                        align="center">
                                </el-table-column>
                            </el-table>
                            <?php if (checkPrivilege('order.operate/extract')): ?>
                            <el-row v-if="detail.delivery_status.value == 10">
                                <el-col :lg="9" :md="12">
                                    <el-form :label-position="labelPosition" label-width="80px" :model="expressForm">
                                        <el-form-item label="门店核销员">
                                            <el-select v-model="examineForm.extract_clerk_id" placeholder="请选择">
                                                <el-option
                                                        v-for="item in shopClerkList"
                                                        :key="item.clerk_id"
                                                        :label="`${item.real_name} [${item.shop.shop_name}]`"
                                                        :value="item.clerk_id">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
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
                            <?php endif;?>
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
                shopClerkList: <?= json_encode($shopClerkList) ?>,
                baseData: [],
                goodsData: [],
                receiverData: [],
                expressData: [],
                extractShopData: [],
                labelPosition: 'top',
                expressForm: {
                    express_id: '',
                    express_no: '',
                    delivery_remark: ''
                },
                show_edit_express: false,
                expressEditForm: {
                    express_id: '',
                    express_no: ''
                },
                examineForm: {
                    extract_status: 1,
                    extract_clerk_id: ''
                },
                selfExpressData: [],
                payData: [],
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
                    let baseData = {order_no: detail.order_no, express_price: detail.express_price, pay_price:detail.pay_price, total_price:detail.total_price, user:detail.user, pay_type:detail.pay_type, delivery_type:detail.delivery_type, pay_status:detail.pay_status, delivery_status:detail.delivery_status, receipt_status:detail.receipt_status};
                    this.baseData = [baseData];
                    let goodsData = {
                        goods_name: detail.goods[0].goods_name,
                        image: detail.goods[0].image.file_path,
                        attr: detail.goods[0].goods_attr,
                        sku: detail.goods[0].goods_no,
                        weight: detail.goods[0].goods_weight,
                        goods_num: detail.goods[0].total_num,
                        goods_price: detail.goods[0].goods_price,
                        total_price: detail.goods[0].total_price
                    };
                    this.goodsData = [goodsData];
                    let [address , receiver_user , receiver_mobile] = ['', '', ''];
                    if(detail.address){
                        let region = detail.address.region;
                        address = `${region.province} ${region.city} ${region.region} ${detail.address.detail}`;
                        receiver_user = detail.address.name;
                        receiver_mobile = detail.address.phone;
                    }
                    let receiverData = {receiver_user, receiver_mobile, address, buyer_remark: detail.buyer_remark};
                    this.receiverData = [receiverData];

                    let payData = {
                        pay_price: detail.pay_price,
                        pay_type: detail.pay_type,
                        pay_status: detail.pay_status,
                        pay_time: this.timeFormat(detail.pay_time),
                        transaction_id: detail.transaction_id,
                    }
                    this.payData = [payData];

                    let expressData = {
                        express_name: detail.express? detail.express.express_name : '',
                        express_no: detail.express_no,
                        remark: detail.delivery_remark,
                        deliver_time: this.timeFormat(detail.delivery_time),
                        order_id: detail.order_id
                    }
                    this.expressData = [expressData];

                    let selfExpressData = {
                        shop_name: detail.extract_shop ? detail.extract_shop.shop_name : '',
                        extract_clerk: detail.extract_clerk ? detail.extract_clerk.real_name : '',
                        extract_status: detail.delivery_status.value == 20 ? '已核销' : '待核销' ,
                        examine_time: this.timeFormat(detail.delivery_time)
                    };
                    this.selfExpressData = [selfExpressData];

                    if(detail.extract_shop){
                        let extract_shop = detail.extract_shop;
                        let extractShopData = {
                            shop_id: extract_shop.shop_id,
                            shop_name: extract_shop.shop_name,
                            logo: extract_shop.logo.file_path,
                            linkman: extract_shop.linkman,
                            phone: extract_shop.phone,
                            address: extract_shop.address,
                        };
                        this.extractShopData = [extractShopData];
                    }

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
                    let order = {express_id, express_no, delivery_remark} = expressForm;
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
                    $.post('<?= url('order/delivery') ?>', {order, order_id: detail.order_id}, (res)=>{
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
                    let express = {express_id, express_no} = expressEditForm;
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
                    $.post('<?= url('order/updateExpress') ?>', {express, order_id: detail.order_id}, (res)=>{
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
                    $.post('<?= url('order.operate/extract') ?>', {order, order_id: detail.order_id}, (res)=>{
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
                    let [order_status, pay_status, delivery_status, receipt_status] = [this.detail.order_status.value, this.detail.pay_status.value, this.detail.delivery_status.value, this.detail.receipt_status.value];
                    let step = 1;
                    if(pay_status == 20){
                        step = 2;
                    }
                    if(delivery_status == 20){
                        step = 3;
                    }
                    if(receipt_status == 20)
                        step = 4
                    if(order_status == 20 || order_status == 21)
                        step = 5
                    if(order_status == 40)
                        step = 6
                    return step;
                },

            },
            created: function(){
                this.initData();
            }
        });


    });
</script>

