<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">导出数据</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table" v-cloak>
                        <el-row :gutter="20">
                            <el-col :span="7">
                                <el-row>
                                    <el-col :span="18">
                                        <el-form label-position="top" label-width="80px">
                                            <el-form-item label="时间筛选[不筛选则是全时段的数据]">
                                                <el-date-picker
                                                        v-model="date"
                                                        type="datetimerange"
                                                        :picker-options="pickerOptions"
                                                        range-separator="至"
                                                        start-placeholder="开始日期"
                                                        end-placeholder="结束日期"
                                                        align="right">
                                                </el-date-picker>
                                            </el-form-item>

                                            <el-form-item label="选择商品">
                                                <el-cascader :options="goods_options" :show-all-levels="false" @change="chooseGoods"></el-cascader>
                                            </el-form-item>

                                            <el-button type="primary" plain @click="submit">导出</el-button>

                                        </el-form>
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
                goods_sku_id: 0,
                goods_id: 0
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

                submit: function(){
                    let [start_time, end_time] = ['', ''];
                    if(this.date){
                        start_time = this.initDate(this.date[0]);
                        end_time = this.initDate(this.date[1]);
                    }
                    let goods_sku_id = this.goods_sku_id;
                    location.href = "<?= url('operate.index/exportUserSaleData') ?>" + `&start_time=${start_time}&end_time=${end_time}&goods_sku_id=${goods_sku_id}`
                },

                chooseGoods: function(e){
                    this.goods_id = e[0];
                    this.goods_sku_id = e[1];
                },

                goodsList: function(){
                    let that = this;
                    $.post("<?= url('user.stock/goodsSkuList') ?>", {}, function(res){
                        that.goods_options = res.data;
                    }, 'json');
                },
            },
            computed:{

            },
            created: function(){
                this.goodsList()
            }
        });

    });
</script>

