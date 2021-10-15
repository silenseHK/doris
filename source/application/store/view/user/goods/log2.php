<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">库存明细</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
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
                                                <el-select v-model="scene" placeholder="请选择">
                                                    <el-option label="全部" :value="-1"></el-option>
                                                    <el-option
                                                            v-for="item in scene_list"
                                                            :key="item.value"
                                                            :label="item.text"
                                                            :value="item.value">
                                                    </el-option>
                                                </el-select>
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
                                                        prop="id"
                                                        label="ID"
                                                        width="80">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="goods.goods_name"
                                                        label="商品名">
                                                </el-table-column>
                                                <el-table-column
                                                        label="商品图片"
                                                        width="120">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 60px; height: 60px"
                                                                :src="scope.row.goods.image[0].file_path"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="变动库存">
                                                    <template slot-scope="scope">
                                                        <el-link v-if="scope.row.change_direction == 10" :underline="false" type="primary">+{{scope.row.change_num}}</el-link>
                                                        <el-link v-else :underline="false" type="danger">{{scope.row.change_num}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="balance_stock"
                                                        label="变动前库存">
                                                </el-table-column>

                                                <el-table-column
                                                        label="库存变动场景">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary">{{scope.row.change_type.text}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="opposite_user.nickName"
                                                        label="收货人/出货人">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="remark"
                                                        label="描述说明">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="create_time"
                                                        label="变动时间">
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
                user_id: <?= $user_id ?>,
                goods_id: <?= $goods_id ?>,
                goods_sku_id: <?= $goods_sku_id ?>,
                size: 10,
                page:1,
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
                scene_list: <?= json_encode($sceneList) ?>,
                scene: -1,
                nickname: ''
            },
            methods:{
                search(e){
                    this.page = e;
                    this.getLogList();
                },
                getLogList: function(){
                    let that = this;
                    let {page, scene, user_id, goods_id, goods_sku_id} = this;
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    $.post("<?= url('user.goods/getLogList') ?>", {page, scene, goods_id, goods_sku_id, user_id, start_time, end_time}, function(res){
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
            },
            computed:{

            },
            created: function(){
                this.getLogList();
            }
        });


    });
</script>

