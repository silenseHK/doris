<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">库存总览</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
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
                                                        label="商品名">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="info">{{scope.row.goods.goods_name}}{{scope.row.specs}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="商品图片"
                                                        width="200">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 80px; height: 80px"
                                                                :src="scope.row.spec.image.file_path"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>

                                                <el-table-column
                                                        label="当前库存">
                                                    <template slot-scope="scope">
                                                        <el-link v-if="scope.row.stock > 0" :underline="false" type="primary">{{scope.row.stock}}</el-link>
                                                        <el-link v-else :underline="false" type="danger">{{scope.row.stock}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="历史库存">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="info">{{scope.row.history_stock}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="历史出库">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="info">{{scope.row.history_sale}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="操作">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" type="primary" :href=`<?= url('user.goods/log') ?>/user_id/${scope.row.user_id}/goods_id/${scope.row.goods_id}/goods_sku_id/${scope.row.goods_sku_id}`>库存明细</el-link>
                                                    </template>
                                                </el-table-column>
                                            </el-table>
                                        </template>
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
                list:<?= json_encode($list)?>,
            },
            methods:{

            },
            computed:{

            },
            created: function(){

            }
        });


    });
</script>

