<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">业绩明细</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal" @select="handleSelect">
                                    <el-menu-item index="1">个人业绩</el-menu-item>
                                    <el-menu-item index="2">团队业绩</el-menu-item>
                                </el-menu>

                                <el-container style="margin-top:20px;">
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="4.5">
                                                <div class="block">
                                                    <el-date-picker
                                                            v-model="date"
                                                            type="month"
                                                            placeholder="选择查询业绩的月度">
                                                    </el-date-picker>
                                                </div>
                                            </el-col>
                                            <el-col :span="1">
                                                <el-button icon="el-icon-search" circle @click="search(1)"></el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <template>
                                            <el-table
                                                    v-show="mode==1"
                                                    :data="self_list"
                                                    border
                                                    style="width: 100%">
                                                <el-table-column
                                                        align="center"
                                                        prop="id"
                                                        label="ID"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="微信头像"
                                                        width="120">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.user.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="用户id"
                                                        prop="user_id"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="user.nickName"
                                                        label="微信昵称">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="业绩金额">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.achievement}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="场景">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" v-if="scope.row.direction == 10" type="success">增加</el-link>
                                                        <el-link :underline="false" v-else type="warning">减少</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="order_info.order_no"
                                                        align="center"
                                                        label="订单编号">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="时间">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="info">{{scope.row.create_time}}</el-link>
                                                    </template>
                                                </el-table-column>
                                            </el-table>
                                        </template>
                                        <template>
                                            <el-table
                                                    v-show="mode==2"
                                                    :data="achievement_list"
                                                    border
                                                    style="width: 100%">
                                                <el-table-column
                                                        align="center"
                                                        prop="detail.id"
                                                        label="ID"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="微信头像"
                                                        width="120">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.detail.user.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="用户id"
                                                        prop="detail.user_id"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="detail.user.nickName"
                                                        label="微信昵称">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="业绩金额">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.detail.achievement}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="场景">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" v-if="scope.row.detail.direction == 10" type="success">增加</el-link>
                                                        <el-link :underline="false" v-else type="warning">减少</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="detail.order_info.order_no"
                                                        align="center"
                                                        label="订单编号">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="时间">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="info">{{scope.row.create_time}}</el-link>
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
                mode: 1,
                size: 10,
                user_id: <?= $user_id ?>,
                page:1,
                total: 0,
                self_list: [],
                achievement_list: [],
                date:'',
                scene_list: [
                    {
                        value: 0,
                        text: '全部等级'
                    },
                    {
                        value: 3,
                        text: '推广合伙人'
                    },
                    {
                        value: 4,
                        text: '联合创始人'
                    }
                ],
                scene: 0,
                nickname: '',
                activeIndex: '1'
            },
            methods:{
                search(e){
                    this.page = e;
                    this.initTab();
                },
                getSelfAchievementDetailList: function(){
                    let that = this;
                    let [year, month] = [0, 0];
                    let {page, user_id, date, size} = this;
                    if(date){
                        year = '' + date.getFullYear();
                        month = date.getMonth() + 1;
                        if(month < 10){
                            month = '0' + month
                        }else{
                            month = '' + month
                        }
                    }
                    $.post("<?= url('user.team/getSelfAchievementDetailList') ?>", {page, user_id, month, year, size}, function(res){
                        that.self_list = res.data.data;
                        that.self_total = res.data.total;
                    }, 'json')
                },
                getTeamAchievementDetailList(){
                    let that = this;
                    let [year, month] = [0, 0];
                    let {page, user_id, date, size} = this;
                    if(date){
                        year = '' + date.getFullYear();
                        month = date.getMonth() + 1;
                        if(month < 10){
                            month = '0' + month
                        }else{
                            month = '' + month
                        }
                    }
                    $.post("<?= url('user.team/getTeamAchievementDetailList') ?>", {page, user_id, month, year, size}, function(res){
                        if(res.code == 1){
                            that.achievement_list = res.data.data;
                            that.total = res.data.total;
                        }else{
                            that.achievement_list = [];
                            that.total = 0;
                        }
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
                showAchievementDetail(scope){
                        location.href = `<?= url('user.team/achievementDetail') ?>/user_id/${scope.row.user_id}`;
                },
                handleSelect(key, keyPath) {
                    this.mode = key;
                    this.initTab();
                },
                initTab(){
                    this.page = 1;
                    if(this.mode == 1){
                        this.getSelfAchievementDetailList();
                    }else{
                        this.getTeamAchievementDetailList();
                    }
                }
            },
            computed:{

            },
            created: function(){
                this.date = new Date();
                this.getSelfAchievementDetailList();
            }
        });


    });
</script>

