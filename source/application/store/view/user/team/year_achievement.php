<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">代理业绩</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="4.5">
                                                <div class="block">
                                                    <el-date-picker
                                                            v-model="date"
                                                            type="year"
                                                            placeholder="选择查询业绩的年度">
                                                    </el-date-picker>
                                                </div>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-select v-model="scene" placeholder="请选择">
                                                    <el-option
                                                            v-for="item in scene_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="2.5">
                                                <el-input v-model="user_id" placeholder="请输入用户id"></el-input>
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
                                                    border
                                                    style="width: 100%">
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
                                                        prop="user_id"
                                                        align="center"
                                                        label="用户ID"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        prop="user.nickName"
                                                        label="微信昵称">
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="日期">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="success">{{scope.row.year}} - {{scope.row.month}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="个人业绩">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.total_self_achievement}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="团队业绩">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.total_team_achievement}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="总业绩">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{parseFloat(scope.row.total_team_achievement) + parseFloat(scope.row.total_self_achievement)}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        align="center"
                                                        label="操作">
                                                    <template slot-scope="scope">
                                                        <el-button type="primary" plain size="mini" @click="showAchievementDetail(scope)">业绩详情</el-button>
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
                user_id: '',
                page:1,
                list:[],
                total: 0,
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
                nickname: ''
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getYearAchievementList();
                },
                getYearAchievementList: function(){
                    let that = this;
                    let [year, month] = [0, 0];
                    let {page, scene, user_id, date, size} = this;
                    if(date){
                        year = '' + date.getFullYear();
                        month = date.getMonth() + 1;
                        if(month < 10){
                            month = '0' + month
                        }else{
                            month = '' + month
                        }
                    }
                    $.post("<?= url('user.team/getYearAchievementList') ?>", {page, scene, user_id, month, year, size}, function(res){
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
                showAchievementDetail(scope){
                        location.href = `<?= url('user.team/achievementDetail') ?>/user_id/${scope.row.user_id}`;
                },
            },
            computed:{

            },
            created: function(){
                this.date = new Date();
                this.getYearAchievementList();
            }
        });


    });
</script>

