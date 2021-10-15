<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">迁移管理</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header height="auto">
                                        <el-row justify="space-around" :gutter="20" style="border: 1px solid #ddd; padding:1%;width:98%;margin-bottom:20px;border-radius: 10px;">
                                            <el-col :xl={span:20}>
                                                    <el-col :span="5">
                                                        <el-tag type="warning">转移库存总量：{{transfer_data.transfer_total}}</el-tag>
                                                    </el-col>
                                                    <el-col :span="5">
                                                        <el-tag type="warning">已消耗总量：{{transfer_data.used_transfer_stock}}</el-tag>
                                                    </el-col>
                                                    <el-col :span="5">
                                                        <el-tag type="warning">转移用户总数：{{transfer_data.transfer_user_num}}</el-tag>
                                                    </el-col>
                                                    <el-col :span="5">
                                                        <el-tag type="warning">已转化用户数：{{transfer_data.active_transfer_user_num}}</el-tag>
                                                    </el-col>
                                            </el-col>
                                        </el-row>
                                        <!--<el-row>-->
                                        <!--    <el-col :xl={span:20}>-->
                                        <!--        <el-row :gutter="10" style="border: 1px solid #ddd; padding:1%;width:98%;margin-bottom:20px;border-radius: 10px;">-->
                                        <!--            <el-col :span="3">-->
                                        <!--                <el-tag type="warning">转移库存总量：{{transfer_data.transfer_total}}</el-tag>-->
                                        <!--            </el-col>-->
                                        <!--            <el-col :span="3">-->
                                        <!--                <el-tag type="warning">已消耗总量：{{transfer_data.used_transfer_stock}}</el-tag>-->
                                        <!--            </el-col>-->
                                        <!--            <el-col :span="3">-->
                                        <!--                <el-tag type="warning">转移用户总数：{{transfer_data.transfer_user_num}}</el-tag>-->
                                        <!--            </el-col>-->
                                        <!--            <el-col :span="3">-->
                                        <!--                <el-tag type="warning">已转化用户数：{{transfer_data.active_transfer_user_num}}</el-tag>-->
                                        <!--            </el-col>-->
                                        <!--        </el-row>-->
                                        <!--    </el-col>-->
                                        <!--</el-row>-->
                                    </el-header>
                                    <el-header>
                                        <el-row :gutter="20">
                                            <el-col :span="3">
                                                <el-button type="primary" plain @click="confirmExport">导出迁移数据</el-button>
                                            </el-col>
                                            <el-col :span="4">
                                                <el-select v-model="search.grade_id" placeholder="请选择">
                                                    <el-option label="全部等级" :value="0"></el-option>
                                                    <el-option
                                                            v-for="(item, key) in grade_list"
                                                            :key="item.grade_id"
                                                            :label="item.name"
                                                            :value="item.grade_id">
                                                    </el-option>
                                                </el-select>
                                            </el-col>

                                            <el-col :span="4">
                                                <el-select v-model="search.is_active" placeholder="请选择">
                                                    <el-option label="全部状态" :value="0"></el-option>
                                                    <el-option label="未转化" :value="1"></el-option>
                                                    <el-option label="已转化" :value="2"></el-option>
                                                </el-select>
                                            </el-col>

                                            <el-col :span="4">
                                                <el-input v-model="search.user_id" placeholder="用户ID"></el-input>
                                            </el-col>

                                            <el-col :span="1">
                                                <el-button icon="el-icon-search" circle @click="getTransferUserList(1)"></el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>

                                    <el-main>
                                        <el-row>
                                            <el-col :xl={span:20}>
                                                <template>
                                                    <el-table
                                                            :data="list"
                                                            style="width: 100%">
                                                        <el-table-column
                                                                prop="user_id"
                                                                label="用户ID">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="nickName"
                                                                label="昵称">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="头像"
                                                                width="180">
                                                            <template slot-scope="scope">
                                                                <el-image
                                                                        style="width: 60px; height: 60px"
                                                                        :src="scope.row.avatarUrl"
                                                                        fit="fill"></el-image>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="grade.name"
                                                                label="用户等级">
                                                        </el-table-column>
                                                        <el-table-column
                                                                prop="transfer_stock_data.transfer_stock_history"
                                                                label="迁移总库存">
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="消耗库存量">
                                                            <template slot-scope="scope">
                                                                <el-link>{{scope.row.transfer_stock_data.transfer_stock_history - scope.row.transfer_stock_data.transfer_stock}}</el-link>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column
                                                                label="操作">
                                                            <template slot-scope="scope">
                                                                <el-button plain size="mini" @click="stockLog(scope)">库存明细</el-button>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                </template>

                                                <div class="am-u-lg-12 am-cf">
                                                    <div class="am-fr" v-html="page"></div>
                                                    <div class="am-fr pagination-total am-margin-right">
                                                        <div class="am-vertical-align-middle">总记录：{{total}}</div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                    </el-main>
                                </el-container>
                            </el-col>
                        </el-row>
                        <el-drawer
                                title="false"
                                :with-header="false"
                                :visible.sync="show_stock_log"
                                direction="ltr"
                                size="40%">
                            <el-table :data="stock_log_list" style="padding:2%;width:96%">
                                <el-table-column prop="change_num" label="变动库存" ></el-table-column>
                                <el-table-column prop="balance_stock" label="变动前库存"></el-table-column>
                                <el-table-column prop="change_type.text" label="变动场景"></el-table-column>
                                <el-table-column prop="remark" label="描述/说明"></el-table-column>
                                <el-table-column prop="create_time" label="变动时间"></el-table-column>
                            </el-table>
                            <el-pagination
                                    background
                                    layout="prev, pager, next"
                                    :pager-count="9"
                                    :page-size="log_size"
                                    :current-page="log_page"
                                    @current-change="changeLogPage"
                                    hide-on-single-page
                                    :total="log_total">
                            </el-pagination>
                        </el-drawer>
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
                grade_list: <?= json_encode($grade_list) ?>,
                transfer_data: <?= json_encode($transfer_data) ?>,
                page:'',
                size: 15,
                cur_page: 1,
                list:[],
                total: 0,
                search: {
                    user_id: '',
                    grade_id: 0,
                    is_active: 0
                },
                show_stock_log: false,
                stock_log_list: [],
                log_page: 1,
                log_size: 15,
                log_total: 0,
            },
            methods:{
                confirmExport(){
                    let [user_id, grade_id, is_active] = [this.search.user_id, this.search.grade_id, this.search.is_active];
                    window.open('<?= url('user.stock/exportTransferData') ?>'+`&user_id=${user_id}&grade_id=${grade_id}&is_active=${is_active}`,'_blank');
                },

                getTransferUserList(page){
                    this.cur_page = page;
                    let that = this;
                    let [user_id, grade_id, size, is_active] = [this.search.user_id, this.search.grade_id, this.size, this.search.is_active];
                    $.post("<?= url('user.stock/transferUserList') ?>", {page, user_id, grade_id, size, is_active}, function(res){
                        that.page = res.data.page;
                        that.list = res.data.list;
                        that.total = res.data.total;
                    }, 'json')
                },

                stockLog(scope){
                    this.show_stock_log = true;
                    this.log_user_id = scope.row.user_id;
                    this.log_page = 1;
                    this.getStockLog();
                },

                getStockLog(){
                    let [size, page, user_id] = [this.log_size, this.log_page, this.log_user_id];
                    let that = this;
                    $.post("<?= url('user.stock/userTransferStockLog') ?>", {size, page, user_id}, function(res){
                        that.stock_log_list = res.data.list;
                        that.log_total = res.data.total;
                    }, 'json')
                },

                changeLogPage(e){
                    this.log_page = e;
                    this.getStockLog();
                },

            },
            computed:{

            },
            created: function(){
                // this.getSalespersonList();
                this.getTransferUserList();
            }
        });


    });

    function getTransferUserList(page){
        App.getTransferUserList(page);
    }
</script>

