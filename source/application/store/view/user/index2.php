<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">用户列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2.5">
                                                <el-select v-model="grade_id" placeholder="请选择">
                                                    <el-option label="全部" :value="0"></el-option>
                                                    <el-option
                                                            v-for="item in grade_list"
                                                            :key="item.grade_id"
                                                            :label="item.name"
                                                            :value="item.grade_id">
                                                    </el-option>
                                                </el-select>
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
                                            <el-col :span="2.5">
                                                <el-input v-model="user_id" placeholder="用户id"></el-input>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input v-model="mobile" placeholder="手机号"></el-input>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input v-model="nickname" placeholder="微信昵称"></el-input>
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
                                                        align="center"
                                                        prop="user_id"
                                                        label="用户ID"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="微信头像"
                                                        width="100">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="nickName"
                                                        label="微信昵称">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="balance"
                                                        label="用户余额">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="grade.name"
                                                        label="会员等级">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="expend_money"
                                                        label="实际消费金额">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="gender"
                                                        label="性别">
                                                </el-table-column>
                                                <el-table-column label="地址">
                                                    <el-table-column
                                                            prop="country"
                                                            label="国家">
                                                    </el-table-column>
                                                    <el-table-column
                                                            prop="province"
                                                            label="省份">
                                                    </el-table-column>
                                                    <el-table-column
                                                            prop="city"
                                                            label="城市">
                                                    </el-table-column>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        width="110"
                                                        prop="mobile_hide"
                                                        label="手机号">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        width="110"
                                                        label="邀请人">
                                                    <template slot-scope="scope">
                                                        <el-link v-if='scope.row.invitation_user_id' :underline="false" type="info">
                                                            {{scope.row.invitation_user.nickName}}
                                                            ({{scope.row.invitation_user.user_id}})
                                                        </el-link>
                                                        <el-link v-else :underline="false" type="info">无</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="状态">
                                                    <template slot-scope="scope">
                                                        <el-link v-if='scope.row.status == 1' :underline="false" type="info">
                                                            正常
                                                        </el-link>
                                                        <el-link v-else :underline="false" type="info">冻结</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        width="180"
                                                        prop="create_time"
                                                        label="注册时间">
                                                </el-table-column>
                                                <el-table-column
                                                        fixed="right"
                                                        width="200"
                                                        label="操作">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" style="flex-direction: column;">
                                                            <el-col style="margin-bottom: 5px;">
                                                                <?php if (checkPrivilege('user/recharge')): ?>
                                                                <el-col :span="10">
                                                                    <el-button size="mini" type="info" plain @click="showCharge(scope)">充值</el-button>
                                                                </el-col>
                                                                <?php endif; ?>
                                                                <?php if (checkPrivilege('user/grade')): ?>
                                                                <el-col :span="10">
                                                                    <el-button size="mini" type="info" plain @click="showGrade(scope)">会员等级</el-button>
                                                                </el-col>
                                                                <?php endif; ?>
                                                            </el-col>
                                                            <el-col>
                                                                <?php if (checkPrivilege('user/delete')): ?>
                                                                <el-col :span="10">
                                                                    <el-button v-if="scope.row.status == 1" size="mini" type="info" plain @click="frozenUser(scope)">冻结</el-button>
                                                                    <el-button v-else size="mini" type="info" plain @click="disFrozenUser(scope)">解冻</el-button>
                                                                </el-col>
                                                                <?php endif; ?>
                                                                <el-col :span="10">
                                                                    <el-dropdown @command="handleMoreHandle">
                                                                        <el-button type="info" size="mini">
                                                                            更多菜单<i class="el-icon-arrow-down el-icon--right"></i>
                                                                        </el-button>
                                                                        <el-dropdown-menu slot="dropdown">
                                                                            <?php if (checkPrivilege('user.order/index')): ?>
                                                                            <el-dropdown-item :command=`<?= url('user.order/index') ?>/user_id/${scope.row.user_id}`>明细</el-dropdown-item>
                                                                            <?php endif; ?>
                                                                            <?php if (checkPrivilege('user.balance/log')): ?>
                                                                            <el-dropdown-item :command=`<?= url('user.balance/log') ?>/user_id/${scope.row.user_id}`>余额明细</el-dropdown-item>
                                                                            <?php endif; ?>
                                                                            <?php if (checkPrivilege('user.goods/goodsstock')): ?>
                                                                            <el-dropdown-item :command=`<?= url('user.goods/goodsStock') ?>/user_id/${scope.row.user_id}`>库存信息</el-dropdown-item>
                                                                            <?php endif; ?>
                                                                            <el-dropdown-item :command=`<?= url('user.team/teamLists') ?>/user_id/${scope.row.user_id}`>团队成员</el-dropdown-item>
                                                                        </el-dropdown-menu>
                                                                    </el-dropdown>
                                                                </el-col>
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
                        <!--充值弹窗-->
                        <el-dialog
                                title=""
                                :visible.sync="show_charge"
                                width="40%">
                            <template>
                                <el-tabs v-model="charge_module" @tab-click="handleClickChargeModule">
                                    <!--余额充值-->
                                    <el-tab-pane label="余额充值" name="balance" :model="charge_balance_form">
                                        <el-form :label-position="labelPosition" label-width="100px">
                                            <el-form-item label="当前余额">
                                                <el-input v-model="charge_balance_form.balance" disabled></el-input>
                                            </el-form-item>
                                            <el-form-item label="充值方式">
                                                <el-radio-group v-model="charge_balance_form.charge_mode">
                                                    <el-radio label="inc">增加</el-radio>
                                                    <el-radio label="dec">减少</el-radio>
                                                    <el-radio label="final">最终金额</el-radio>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item label="变更金额">
                                                <el-input type="number" v-model="charge_balance_form.money" min="0"></el-input>
                                            </el-form-item>
                                            <el-form-item label="管理员备注备注">
                                                <el-input
                                                        type="textarea"
                                                        placeholder="请输入管理员备注"
                                                        v-model="charge_balance_form.remark"
                                                        maxlength="200"
                                                        show-word-limit
                                                >
                                                </el-input>
                                            </el-form-item>
                                        </el-form>
                                    </el-tab-pane>
                                    <el-tab-pane label="补充库存" name="stock">
                                        <el-form :label-position="labelPosition" label-width="100px" :model="charge_stock_form">
                                            <el-form-item label="补充库存商品">
                                                <el-select v-model="charge_stock_form.goods_id" placeholder="请选择" @change="handleStockChangeGoods">
                                                    <el-option
                                                            v-for="item in goods_list"
                                                            :key="item.goods_id"
                                                            :label="item.goods_name"
                                                            :value="item.goods_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="规格" v-if="charge_stock_form.goods_sku_list.length > 0">
                                                <el-select v-model="charge_stock_form.goods_sku_id" placeholder="请选择" @change="handleStockChangeGoodsSku">
                                                    <el-option
                                                            v-for="item in charge_stock_form.goods_sku_list"
                                                            :key="item.goods_sku_id"
                                                            :label="item.attr"
                                                            :value="item.goods_sku_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="当前库存">
                                                <el-input v-model="charge_stock_form.balance_stock" disabled></el-input>
                                            </el-form-item>
                                            <el-form-item label="充值方式">
                                                <el-radio-group v-model="charge_stock_form.mode">
                                                    <el-radio label="inc">增加</el-radio>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item label="充值数量">
                                                <el-input type="number" v-model="charge_stock_form.stock" min="0"></el-input>
                                            </el-form-item>
                                            <el-form-item label="管理员备注备注">
                                                <el-input
                                                        type="textarea"
                                                        placeholder="请输入管理员备注"
                                                        v-model="charge_stock_form.remark"
                                                        maxlength="200"
                                                        show-word-limit
                                                >
                                                </el-input>
                                            </el-form-item>
                                        </el-form>
                                    </el-tab-pane>
                                    <el-tab-pane label="活动补充库存" name="activity_stock">
                                        <el-form :label-position="labelPosition" label-width="100px" :model="charge_activity_stock_form">
                                            <el-form-item label="充值后会员等级">
                                                <el-select v-model="charge_activity_stock_form.grade_id" placeholder="请选择">
                                                    <el-option
                                                            v-for="item in grade_list"
                                                            :key="item.grade_id"
                                                            :label="item.name"
                                                            :value="item.grade_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="补充库存商品">
                                                <el-select v-model="charge_activity_stock_form.goods_id" placeholder="请选择" @change="handleStockChangeGoods">
                                                    <el-option
                                                            v-for="item in goods_list"
                                                            :key="item.goods_id"
                                                            :label="item.goods_name"
                                                            :value="item.goods_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="规格" v-if="charge_activity_stock_form.goods_sku_list.length > 0">
                                                <el-select v-model="charge_activity_stock_form.goods_sku_id" placeholder="请选择" @change="handleStockChangeGoodsSku">
                                                    <el-option
                                                            v-for="item in charge_activity_stock_form.goods_sku_list"
                                                            :key="item.goods_sku_id"
                                                            :label="item.attr"
                                                            :value="item.goods_sku_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="当前库存">
                                                <el-input v-model="charge_activity_stock_form.balance_stock" disabled></el-input>
                                            </el-form-item>
                                            <el-form-item label="充值方式">
                                                <el-radio-group v-model="charge_activity_stock_form.mode">
                                                    <el-radio label="inc">增加</el-radio>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item label="充值数量">
                                                <el-input type="number" v-model="charge_activity_stock_form.stock" min="0"></el-input>
                                            </el-form-item>
                                            <el-form-item label="管理员备注备注">
                                                <el-input
                                                        type="textarea"
                                                        placeholder="请输入管理员备注"
                                                        v-model="charge_activity_stock_form.remark"
                                                        maxlength="200"
                                                        show-word-limit
                                                >
                                                </el-input>
                                            </el-form-item>
                                        </el-form>
                                    </el-tab-pane>
                                    <el-tab-pane label="补充库存[DIY]" name="diy_stock">
                                        <el-form :label-position="labelPosition" label-width="100px" :model="charge_diy_stock_form">
                                            <el-form-item label="补充库存商品">
                                                <el-select v-model="charge_diy_stock_form.goods_id" placeholder="请选择" @change="handleStockChangeGoods">
                                                    <el-option
                                                            v-for="item in goods_list"
                                                            :key="item.goods_id"
                                                            :label="item.goods_name"
                                                            :value="item.goods_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="规格" v-if="charge_diy_stock_form.goods_sku_list.length > 0">
                                                <el-select v-model="charge_diy_stock_form.goods_sku_id" placeholder="请选择" @change="handleStockChangeGoodsSku">
                                                    <el-option
                                                            v-for="item in charge_diy_stock_form.goods_sku_list"
                                                            :key="item.goods_sku_id"
                                                            :label="item.attr"
                                                            :value="item.goods_sku_id">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                            <el-form-item label="当前库存">
                                                <el-input v-model="charge_diy_stock_form.balance_stock" disabled></el-input>
                                            </el-form-item>
                                            <el-form-item label="充值方式">
                                                <el-radio-group v-model="charge_diy_stock_form.mode">
                                                    <el-radio label="inc">增加</el-radio>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item label="是否升级">
                                                <el-radio-group v-model="charge_diy_stock_form.is_integral">
                                                    <el-radio label="1">正常升级</el-radio>
                                                    <el-radio label="0">不升级</el-radio>
                                                </el-radio-group>
                                            </el-form-item>

                                            <el-form-item label="强制平台发货">
                                                <el-radio-group v-model="charge_diy_stock_form.is_force_platform" @change="changeForcePlatform">
                                                    <el-radio label="0">否</el-radio>
                                                    <el-radio label="1">是</el-radio>
                                                </el-radio-group>
                                            </el-form-item>

                                            <el-form-item label="是否增加业绩">
                                                <el-radio-group v-model="charge_diy_stock_form.is_achievement">
                                                    <el-radio :disabled="charge_diy_stock_form.is_force_platform == 1" label="1">正常增加业绩</el-radio>
                                                    <el-radio :disabled="charge_diy_stock_form.is_force_platform == 1" label="0">不增加业绩</el-radio>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item label="是否返利">
                                                <el-radio-group v-model="charge_diy_stock_form.is_rebate">
                                                    <el-radio :disabled="charge_diy_stock_form.is_force_platform == 1" label="1">返利</el-radio>
                                                    <el-radio :disabled="charge_diy_stock_form.is_force_platform == 1" label="0">不返利</el-radio>
                                                </el-radio-group>
                                            </el-form-item>

                                            <el-form-item label="充值数量">
                                                <el-input type="number" v-model="charge_diy_stock_form.stock" min="0"></el-input>
                                            </el-form-item>
                                            <el-form-item label="管理员备注备注">
                                                <el-input
                                                        type="textarea"
                                                        placeholder="请输入管理员备注"
                                                        v-model="charge_diy_stock_form.remark"
                                                        maxlength="200"
                                                        show-word-limit
                                                >
                                                </el-input>
                                            </el-form-item>
                                        </el-form>
                                    </el-tab-pane>
                                </el-tabs>
                            </template>
                            <span slot="footer" class="dialog-footer">
                                <el-button @click="show_charge = false">取 消</el-button>
                                <el-button type="primary" @click="confirmRecharge">确 定</el-button>
                            </span>
                        </el-dialog>
                        <!--等级切换-->
                        <el-dialog
                                title=""
                                :visible.sync="show_level_change"
                                width="40%">
                            <template>
                                <el-form :label-position="labelPosition" label-width="100px" :model="level_form">
                                    <el-form-item label="会员等级">
                                        <el-select v-model="level_form.grade_id" placeholder="请选择">
                                            <el-option
                                                    v-for="item in grade_list"
                                                    :key="item.grade_id"
                                                    :label="item.name"
                                                    :value="item.grade_id">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="管理员备注备注">
                                        <el-input
                                                type="textarea"
                                                placeholder="请输入管理员备注"
                                                v-model="level_form.remark"
                                                maxlength="200"
                                                show-word-limit
                                        >
                                        </el-input>
                                    </el-form-item>
                                </el-form>
                            </template>
                            <span slot="footer" class="dialog-footer">
                                <el-button @click="show_level_change = false">取 消</el-button>
                                <el-button type="primary" @click="confirmGradeChange">确 定</el-button>
                            </span>
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
                grade_id: 0,
                user_id: '',
                mobile: '',
                nickname: '',
                show_charge: false,
                charge_module: 'balance',
                labelPosition: 'top',
                charge_balance_form: {
                    user_id: 0,
                    balance: 0,
                    charge_mode: 'inc',
                    money: 0,
                    remark: ''
                },
                charge_stock_form: {
                    user_id: 0,
                    goods_id: '',
                    goods_sku_id: '',
                    goods_sku_attr: '',
                    goods_sku_list: [],
                    mode: 'inc',
                    remark: '',
                    balance_stock: 0,
                    stock: 0,
                },
                charge_activity_stock_form: {
                    user_id: 0,
                    grade_id: '',
                    goods_id: '',
                    goods_sku_id: '',
                    goods_sku_attr: '',
                    goods_sku_list: [],
                    mode: 'inc',
                    remark: '',
                    balance_stock: 0,
                    stock: 0,
                },
                charge_diy_stock_form: {
                    user_id: 0,
                    goods_id: '',
                    goods_sku_id: '',
                    goods_sku_attr: '',
                    goods_sku_list: [],
                    mode: 'inc',
                    remark: '',
                    balance_stock: 0,
                    stock: 0,
                    is_rebate: '1',
                    is_force_platform: "0",  //强制平台发货
                    is_integral: "1",  //升级
                    is_achievement: "1",  //业绩
                },
                goods_list: <?= json_encode($goodsList) ?>,
                grade_list: <?= json_encode($gradeList) ?>,
                show_level_change: false,
                level_form: {
                    user_id: 0,
                    grade_id: 0,
                    remark: ''
                },
                more_handle: '',
                page:1,
                size:15,
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
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getUserList();
                },
                getUserList: function(){
                    let that = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let {user_id, grade_id, mobile, nickname, page} = this;
                    $.post("<?= url('user/getUserList') ?>", {page, user_id, mobile, nickname, grade_id, start_time, end_time}, function(res){
                        that.list = res.data.data;
                        that.total = res.data.total;
                        console.log(that.list)
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
                handleClickChargeModule(tab, event){
                    console.log(tab, event);
                },
                showCharge(scope){
                    this.initChargeForm(scope);
                    this.show_charge = true;
                },
                showGrade(scope){
                    this.show_level_change = true;
                    this.level_form = {
                        user_id: scope.row.user_id,
                        grade_id: scope.row.grade_id,
                        remark: ''
                    };
                },
                initChargeForm(scope){
                    this.charge_module = 'balance';
                    this.charge_balance_form = {
                        user_id: scope.row.user_id,
                        balance: scope.row.balance,
                        charge_mode: 'inc',
                        money: 0,
                        remark: ''
                    }
                    this.charge_stock_form = {
                        user_id: scope.row.user_id,
                        goods_id: '',
                        goods_sku_id: '',
                        goods_sku_attr: '',
                        goods_sku_list: [],
                        mode: 'inc',
                        balance_stock: 0,
                        remark: '',
                        stock: 0,
                    }
                    this.charge_activity_stock_form = {
                        user_id: scope.row.user_id,
                        grade_id: '',
                        goods_id: '',
                        goods_sku_id: '',
                        goods_sku_attr: '',
                        goods_sku_list: [],
                        mode: 'inc',
                        balance_stock: 0,
                        remark: '',
                        stock: 0
                    }
                    this.charge_diy_stock_form = {
                        user_id: scope.row.user_id,
                        goods_id: '',
                        goods_sku_id: '',
                        goods_sku_attr: '',
                        goods_sku_list: [],
                        mode: 'inc',
                        balance_stock: 0,
                        remark: '',
                        stock: 0,
                        is_rebate: '1',
                        is_force_platform: "0",  //强制平台发货
                        is_integral: "1",  //升级
                        is_achievement: "1",  //业绩
                    }
                },
                confirmRecharge(){
                    let charge_module = this.charge_module;
                    switch (charge_module){
                        case 'balance':
                            this.confirmBalance();
                            break;
                        case 'stock':
                            this.confirmStock();
                            break;
                        case 'activity_stock':
                            this.confirmActivityStock();
                            break;
                        case 'diy_stock':
                            this.confirmDiyStock();
                            break;
                        default:
                            return false;
                    }
                },
                confirmBalance(){
                    let data = this.charge_balance_form;
                    let money = parseFloat(data.money);
                    if(money <= 0){
                        this.$message({
                            type: 'error',
                            message: '充值金额必须大于0'
                        })
                        return false;
                    }
                    let recharge = {
                        balance: {
                            mode: data.charge_mode,
                            money,
                            remark: data.remark
                        }
                    }
                    let that = this;
                    $.post(`<?= url('user/recharge') ?>`, {user_id: data.user_id, source:0, recharge}, (res)=>{
                        let type = 'error';
                        if(res.code === 1){
                            type = 'success'
                            that.show_charge = false;
                            that.getUserList();
                        }
                        that.$message({
                            type,
                            message: res.msg
                        })
                    }, 'json')
                },
                confirmStock(){
                    let data = this.charge_stock_form;
                    if(!data.goods_id || !data.goods_sku_id){
                        this.$message({
                            type: 'error',
                            message: '请选择商品'
                        })
                        return false;
                    }
                    let stock = parseInt(data.stock);
                    if(stock <= 0){
                        this.$message({
                            type: 'error',
                            message: '充值库存数量必须大于0'
                        })
                        return false;
                    }
                    let recharge = {
                        points: {
                            mode: data.mode,
                            goods_sku_id: data.goods_sku_id,
                            value: stock,
                            remark: data.remark,
                        }
                    }
                    let that = this;
                    $.post(`<?= url('user/recharge') ?>`, {user_id: data.user_id, source:1, recharge}, (res)=>{
                        let type = 'error';
                        if(res.code === 1){
                            type = 'success'
                            that.show_charge = false;
                        }
                        that.$message({
                            type,
                            message: res.msg
                        })
                    }, 'json')
                },
                confirmDiyStock(){
                    let data = this.charge_diy_stock_form;
                    if(!data.goods_id || !data.goods_sku_id){
                        this.$message({
                            type: 'error',
                            message: '请选择商品'
                        })
                        return false;
                    }
                    let stock = parseInt(data.stock);
                    if(stock <= 0){
                        this.$message({
                            type: 'error',
                            message: '充值库存数量必须大于0'
                        })
                        return false;
                    }
                    let recharge = {
                        diy: {
                            mode: data.mode,
                            goods_sku_id: data.goods_sku_id,
                            value: stock,
                            remark: data.remark,
                            is_rebate: data.is_rebate,
                            is_force_platform: data.is_force_platform,
                            is_achievement: data.is_achievement,
                            is_integral: data.is_integral
                        }
                    }
                    let that = this;
                    $.post(`<?= url('user/recharge') ?>`, {user_id: data.user_id, source:4, recharge}, (res)=>{
                        let type = 'error';
                        if(res.code === 1){
                            type = 'success'
                            that.show_charge = false;
                        }
                        that.$message({
                            type,
                            message: res.msg
                        })
                    }, 'json')
                },
                confirmActivityStock(){
                    let data = this.charge_activity_stock_form;
                    if(!data.grade_id){
                        this.$message({
                            type: 'error',
                            message: '请选择用户等级'
                        })
                        return false;
                    }
                    if(!data.goods_id || !data.goods_sku_id){
                        this.$message({
                            type: 'error',
                            message: '请选择商品'
                        })
                        return false;
                    }
                    let stock = parseInt(data.stock);
                    if(stock <= 0){
                        this.$message({
                            type: 'error',
                            message: '充值库存数量必须大于0'
                        })
                        return false;
                    }
                    let recharge = {
                        grade: {
                            mode: data.mode,
                            goods_sku_id: data.goods_sku_id,
                            value: stock,
                            remark: data.remark,
                            grade_id: data.grade_id
                        }
                    }
                    let that = this;
                    $.post(`<?= url('user/recharge') ?>`, {user_id: data.user_id, source:2, recharge}, (res)=>{
                        let type = 'error';
                        if(res.code === 1){
                            type = 'success'
                            that.show_charge = false;
                        }
                        that.$message({
                            type,
                            message: res.msg
                        })
                    }, 'json')
                },
                handleStockChangeGoods(goods_id){
                    let that = this;
                    $.post('index.php?s=/store/goods/getGoodsSpec', {goods_id}, function(res){
                        if(res.code == 1){
                            if(res.data.spec_id == 0){
                                that.charge_stock_form.goods_sku_list = res.data.list;
                                that.charge_activity_stock_form.goods_sku_list = res.data.list;
                                that.charge_diy_stock_form.goods_sku_list = res.data.list;
                                that.charge_stock_form.goods_sku_id = '';
                                that.charge_activity_stock_form.goods_sku_id = '';
                                that.charge_diy_stock_form.goods_sku_id = '';
                            }else{
                                that.charge_stock_form.goods_sku_id = res.data.spec_id
                                that.charge_activity_stock_form.goods_sku_id = res.data.spec_id
                                that.charge_diy_stock_form.goods_sku_id = res.data.spec_id
                                that.charge_stock_form.goods_sku_list = [];
                                that.charge_activity_stock_form.goods_sku_list = [];
                                that.charge_diy_stock_form.goods_sku_list = [];
                                that.getUserGoodsStock(that.charge_stock_form.user_id, res.data.spec_id);
                            }
                        }else{
                            that.$message({
                                type: 'error',
                                message: res.msg
                            })
                        }
                    }, 'json')
                },
                getUserGoodsStock(user_id, goods_sku_id){
                    let that = this;
                    $.post('index.php?s=/store/user/getUserGoodsStock', {goods_sku_id, user_id}, function(res){
                        if(res.code == 1){
                            that.charge_stock_form.balance_stock = res.data
                            that.charge_activity_stock_form.balance_stock = res.data
                            that.charge_diy_stock_form.balance_stock = res.data
                        }else{
                            that.$message({
                                type: 'error',
                                message: res.msg
                            })
                        }
                    }, 'json')
                },
                handleStockChangeGoodsSku(goods_sku_id){
                    this.getUserGoodsStock(this.charge_stock_form.user_id, goods_sku_id);
                },
                confirmGradeChange(){
                    let grade = this.level_form;
                    let that = this;
                    $.post(`<?= url('user/grade') ?>`, {user_id: grade.user_id, grade}, (res)=>{
                        let type = 'error';
                        if(res.code === 1){
                            type = 'success'
                            that.show_level_change = false;
                            this.getUserList();
                        }
                        that.$message({
                            type,
                            message: res.msg
                        })
                    }, 'json')
                },
                frozenUser(scope){
                    let user_id = scope.row.user_id
                    this.$confirm('确定冻结该用户吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post(`<?= url('user/frozenUser') ?>`, {user_id}, (res)=>{
                            let type = 'error';
                            if(res.code === 1){
                                type = 'success'
                                this.getUserList();
                            }
                            that.$message({
                                type,
                                message: res.msg
                            })
                        }, 'json')
                    }).catch();
                },
                disFrozenUser(scope){
                    let user_id = scope.row.user_id
                    this.$confirm('确定解冻该用户吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post(`<?= url('user/disFrozenUser') ?>`, {user_id}, (res)=>{
                            let type = 'error';
                            if(res.code === 1){
                                type = 'success'
                                this.getUserList();
                            }
                            that.$message({
                                type,
                                message: res.msg
                            })
                        }, 'json')
                    }).catch();
                },
                handleMoreHandle(e){
                    location.href=e;
                },
                changeForcePlatform(e){
                    if(e){
                        this.charge_diy_stock_form.is_achievement = "0";
                        this.charge_diy_stock_form.is_rebate = "0";
                    }
                }
            },
            computed:{

            },
            created: function(){
                this.getUserList(1);
            }
        });


    });
    function getRankList(page){
        App.getUserList(page);
    }
</script>

