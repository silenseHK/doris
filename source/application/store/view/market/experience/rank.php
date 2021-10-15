<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">体验装推荐购买排行</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-header>
                            <el-switch
                                    @change="getRankList(1)"
                                    v-model="rankType"
                                    active-text="T排行"
                                    inactive-text="F排行">
                            </el-switch>
                        </el-header>
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <el-container >
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list1"
                                                    style="width: 100%">
                                                <el-table-column
                                                        label="排行"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-tag
                                                                v-if="(scope.$index + (cur_page-1) * 20 + 1) <= 3"
                                                                :type="items[(scope.$index + (cur_page-1) * 20 + 1) -1].type"
                                                                effect="dark">
                                                            {{ scope.$index + (cur_page-1) * 20 + 1 }}
                                                        </el-tag>
                                                        <el-tag
                                                                v-if="(scope.$index + (cur_page-1) * 20 + 1) > 3"
                                                                type="info"
                                                                effect="dark">
                                                            {{ scope.$index + (cur_page-1) * 20 + 1 }}
                                                        </el-tag>

                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="first_user.nickName"
                                                        label="姓名"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        label="头像">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.first_user.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="num"
                                                        label="推荐下单数"
                                                        width="150">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="member_num"
                                                        label="团队人数"
                                                        width="150">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="redirect_member_num"
                                                        label="直推人数"
                                                        width="150">
                                                </el-table-column>
                                            </el-table>
                                        </template>

                                        <div class="am-u-lg-12 am-cf" v-if="list.length <= 10">
                                            <div class="am-fr" v-html="page"></div>
                                            <div class="am-fr pagination-total am-margin-right">
                                                <div class="am-vertical-align-middle">总记录：{{total}}</div>
                                            </div>
                                        </div>
                                    </el-main>
                                </el-container>
                            </el-col>

                            <el-col :span="12" v-if="list.length > 10">
                                <el-container >
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list2"
                                                    style="width: 100%">
                                                <el-table-column
                                                        label="排行"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-tag
                                                                v-if="(list1.length + scope.$index + (cur_page-1) * 20 + 1) > 3"
                                                                type="info"
                                                                effect="dark">
                                                            {{ list1.length + scope.$index + (cur_page-1) * 20 + 1 }}
                                                        </el-tag>

                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="first_user.nickName"
                                                        label="姓名"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        label="头像">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.first_user.avatarUrl"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="num"
                                                        label="推荐下单数"
                                                        width="150">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="member_num"
                                                        label="团队人数"
                                                        width="150">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="redirect_member_num"
                                                        label="直推人数"
                                                        width="150">
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
                page:'',
                cur_page: 1,
                list:[],
                list1:[],
                list2:[],
                total: 0,
                rankType: true,
                items: [
                    { type: 'success', label: '标签二' },
                    { type: 'danger', label: '标签四' },
                    { type: 'warning', label: '标签五' }
                ],
                tableData: [{
                    date: '2016-05-02',
                    name: '王小虎',
                    address: '上海市普陀区金沙江路 1518 弄',
                }, {
                    date: '2016-05-04',
                    name: '王小虎',
                    address: '上海市普陀区金沙江路 1518 弄'
                }, {
                    date: '2016-05-01',
                    name: '王小虎',
                    address: '上海市普陀区金沙江路 1518 弄',
                }, {
                    date: '2016-05-03',
                    name: '王小虎',
                    address: '上海市普陀区金沙江路 1518 弄'
                }]
            },
            methods:{
                getRankList: function(page){
                    this.cur_page = page;
                    let that = this;
                    let loading = this.$loading({
                        lock: true,
                        text: 'Loading',
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.7)'
                    });
                    let rankType = this.rankType;
                    $.post("<?= url('market.experience/getRankList') ?>", {page, rankType}, function(res){
                        loading.close();
                        let list = res.data.list;
                        that.list1 =[]; that.list2 = [];
                        list.forEach((v,k)=>{
                            if(k < 10){
                                that.list1.push(v)
                            }else{
                                that.list2.push(v)
                            }
                        })

                        that.page = res.data.page;
                        that.list = res.data.list;
                        that.total = res.data.total;
                    }, 'json')
                },
                tableRowClassName({row, rowIndex}) {
                    let idx = rowIndex + 1 + (this.cur_page - 1) * 15
                    console.log(idx)
                    if (idx === 1) {
                        return 'first-row';
                    } else if (idx === 2) {
                        return 'second-row';
                    } else if (idx === 3) {
                        return 'third-row';
                    }
                    return '';
                }

            },
            computed:{

            },
            created: function(){
                this.getRankList(1);
            }
        });


    });
    function getRankList(page){
        App.cur_page = page;
        App.getRankList(page);
    }
</script>

