<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">库存转移</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table" v-cloak>
                        <el-row :gutter="20">
                            <el-col :span="7">
                                <el-row>
                                    <el-col :span="18">
                                        <el-form label-position="top" label-width="80px" :model="formExchange">
                                            <el-form-item label="被转移库存用户user_id">
                                                <el-input v-model="formExchange.user_id" @blur="getUserInfo(1)"></el-input>
                                            </el-form-item>

                                            <el-row v-show="formExchange.user_info.mobile" type="flex" justify="justify-start" align="middle">
                                                <el-col :span="4">
                                                    <el-avatar shape="square" :size="50" :src="formExchange.user_info.avatar"></el-avatar>
                                                </el-col>
                                                <el-col :span="4">
                                                    <el-row>
                                                        <el-col>{{formExchange.user_info.nickname}}</el-col>
                                                        <el-col style="margin-top:10px;">{{formExchange.user_info.mobile}}</el-col>
                                                    </el-row>
                                                </el-col>
                                            </el-row>

                                            <el-form-item label="选择商品">
                                                <el-cascader :options="goods_options" :show-all-levels="false" @change="chooseGoods"></el-cascader>
                                            </el-form-item>

                                            <el-row v-show="stock>=0" type="flex" justify="justify-start" align="middle">
                                                <el-col :span="6">当前用户库存</el-col>
                                                <el-col :span="4">{{stock}}</el-col>
                                            </el-row>

                                            <el-form-item label="接收库存用户user_id">
                                                <el-input v-model="formExchange.receive_user_id" @blur="getUserInfo(2)"></el-input>
                                            </el-form-item>

                                            <transition name="el-zoom-in-top">
                                            <el-row v-show="formExchange.receive_user_info.mobile" type="flex" justify="justify-start" align="middle">
                                                <el-col :span="4">
                                                    <el-avatar shape="square" :size="50" :src="formExchange.receive_user_info.avatar"></el-avatar>
                                                </el-col>
                                                <el-col :span="4">
                                                    <el-row>
                                                        <el-col>{{formExchange.receive_user_info.nickname}}</el-col>
                                                        <el-col style="margin-top:10px;">{{formExchange.receive_user_info.mobile}}</el-col>
                                                    </el-row>
                                                </el-col>
                                            </el-row>
                                            </transition>

                                            <el-form-item label="转移库存数">
                                                <el-input v-model="formExchange.stock"></el-input>
                                            </el-form-item>

                                            <el-form-item label="备注">
                                                <el-input
                                                        type="textarea"
                                                        :autosize="{ minRows: 2, maxRows: 4}"
                                                        placeholder="请输入内容"
                                                        maxlength="200"
                                                        show-word-limit
                                                        v-model="formExchange.remark">
                                                </el-input>
                                            </el-form-item>

                                            <el-button type="primary" plain @click="submit">确定转移库存</el-button>

                                        </el-form>
                                    </el-col>
                                </el-row>
                            </el-col>
                            <el-col :span="16">
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
                                                        <el-input v-model="keywords" placeholder="转换库存用户"></el-input>
                                                    </el-col>
                                                    <el-col :span="1">
                                                        <el-button icon="el-icon-search" circle @click="getExchangeList(1)"></el-button>
                                                    </el-col>
                                                </el-row>
                                            </el-header>
                                            <el-main>
                                                <template>
                                                    <el-table
                                                            :data="list"
                                                            style="width: 100%">
                                                        <el-table-column
                                                                label="出货用户"
                                                                width="150">
                                                            <template slot-scope="scope">
                                                                <el-row>
                                                                    <el-col :span="24">{{scope.row.user.user_id}}</el-col>
                                                                    <el-col :span="24">{{scope.row.user.nickName}}</el-col>
                                                                    <el-col :span="24">{{scope.row.user.mobile}}</el-col>
                                                                </el-row>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="入货用户"
                                                                width="150">
                                                            <template slot-scope="scope">
                                                                <el-row>
                                                                    <el-col :span="24">{{scope.row.receive_user.user_id}}</el-col>
                                                                    <el-col :span="24">{{scope.row.receive_user.nickName}}</el-col>
                                                                    <el-col :span="24">{{scope.row.receive_user.mobile}}</el-col>
                                                                </el-row>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="商品图"
                                                                width="120">
                                                            <template slot-scope="scope">
                                                                <el-image
                                                                        style="width: 60px; height: 60px"
                                                                        :src="scope.row.spec.image.file_path"
                                                                        fit="fill"></el-image>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="goods.goods_name"
                                                                label="商品名称"
                                                                width="130">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="spec.attr"
                                                                label="商品规格"
                                                                width="180">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="stock"
                                                                label="转移库存数"
                                                                width="150">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="transfer_stock"
                                                                label="转移老系统迁移库存数"
                                                                width="120">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="remark"
                                                                label="备注"
                                                                width="220">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="create_time"
                                                                label="操作时间"
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
                formExchange:{
                    user_id: 0,
                    goods_id: 0,
                    goods_sku_id: 0,
                    receive_user_id: 0,
                    stock: 0,
                    remark: '',
                    user_info: {
                        avatar: '',
                        nickname: '',
                        mobile: '',
                    },
                    receive_user_info: {
                        avatar: '',
                        nickname: '',
                        mobile: '',
                    }
                },
                goods_options: [
                    {
                        label: '一级',
                        value: 10001,
                        children: [
                            {
                                label: '二级',
                                value: 10001,
                            }
                        ]
                    }
                ],
                stock: -1,


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
                keywords: ''
            },
            methods:{
                getExchangeList: function(page){
                    this.cur_page = page;
                    let that = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let [keywords] = [this.keywords];
                    $.post("<?= url('user.stock/getExchangeList') ?>", {page, keywords, start_time, end_time}, function(res){
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

                getUserInfo: function(type){
                    let user_id = type == 1?this.formExchange.user_id:this.formExchange.receive_user_id;
                    let that = this;
                    $.post("<?= url('user.stock/userInfo') ?>", {user_id}, function(res){
                        let user_info = {
                            avatar: '',
                            nickname: '',
                            mobile: ''
                        }
                        if(res.code == 1){
                            user_info.nickname = res.data.nickName;
                            user_info.mobile = res.data.mobile_hide;
                            user_info.avatar = res.data.avatarUrl;
                        }
                        if(type == 2){
                            that.formExchange.receive_user_info = user_info;
                        }else{
                            that.formExchange.user_info = user_info;
                            that.userStock();
                        }
                        console.log(user_info)
                    }, 'json')
                },

                goodsList: function(){
                    let that = this;
                    $.post("<?= url('user.stock/goodsSkuList') ?>", {}, function(res){
                        that.goods_options = res.data;
                    }, 'json');
                },

                userStock: function(){
                    let data = this.formExchange;
                    let that = this;
                    let [user_id, goods_sku_id] = [data.user_id, data.goods_sku_id]
                    if(user_id > 0 && goods_sku_id > 0){
                        $.post("<?= url('user.stock/userStock') ?>", {user_id, goods_sku_id}, function(res){
                            that.stock = res.data;
                        }, 'json')
                    }else{
                        this.stock = -1;
                    }
                },

                chooseGoods: function(e){
                    this.formExchange.goods_id = e[0];
                    this.formExchange.goods_sku_id = e[1];
                    this.userStock()
                },

                submit: function(){
                    let data = this.formExchange;
                    let json_ = {
                        user_id: data.user_id,
                        goods_sku_id: data.goods_sku_id,
                        receive_user_id: data.receive_user_id,
                        stock: data.stock,
                        remark: data.remark
                    }
                    if(!json_.user_id || !json_.goods_sku_id || !json_.receive_user_id || !json_.stock){
                        this.$message({
                            message: '请将数据补充完整',
                            type: 'warning'
                        });
                        return false;
                    }
                    if(json_.stock > this.stock){
                        this.$message({
                            message: '转移库存不能大于用户当前剩余库存',
                            type: 'warning'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('user.stock/exchangeStock') ?>", json_, function(res){
                        if(res.code == 1){
                            that.$message({
                                message: res.msg,
                                type: 'success'
                            });
                            that.userStock();
                        }else{
                            that.$message.error(res.msg);
                        }

                    }, 'json')
                }
            },
            computed:{

            },
            created: function(){
                this.goodsList();
                this.getExchangeList(1);
            }
        });


    });
    function getExchangeList(page){
        App.getExchangeList(page);
    }
</script>

