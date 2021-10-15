<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>
    [v-cloak] {
        display: none;
    }
</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">二维码列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table" v-cloak>
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="0.5">
                                                <el-button icon="el-icon-circle-plus-outline" circle @click="add"></el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list"
                                                    style="width: 100%">
                                                <el-table-column
                                                        prop="title"
                                                        label="二维码标题"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        label="二维码"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 100px; height: 100px"
                                                                :src="scope.row.image.file_path"
                                                                fit="fill"></el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="商品规格"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false">{{scope.row.qrcode_type == 10 ? "群二维码" : "营养师二维码"}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="启用状态"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-link :underline="false" @click="editField(scope, 'status')">{{scope.row.status == 1 ? "启用" : "未启用"}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="create_time"
                                                        label="创建时间"
                                                        width="180">
                                                </el-table-column>

                                                <el-table-column
                                                        label="操作"
                                                        width="220">
                                                    <template slot-scope="scope">
                                                        <el-button type="primary" icon="el-icon-edit" circle @click="edit(scope.row.qrcode_id)"></el-button>
                                                        <el-button type="danger" icon="el-icon-delete" circle @click="del(scope)"></el-button>
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
                total: 0,
            },
            methods:{
                getList: function(page){
                    this.cur_page = page;
                    let that = this;
                    $.post("<?= url('content.qrcode/lists') ?>", {page}, function(res){
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
                add: function(){
                    location.href= "<?= url('content.qrcode/add') ?>"
                },
                edit: function(qrcode_id){
                    location.href = "<?= url('content.qrcode/edit') ?>/qrcode_id/" + qrcode_id;
                },
                del: function(scope){
                    let qrcode_id = scope.row.qrcode_id;
                    let that = this;
                    this.$confirm('确定删除二维码吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        $.post("<?= url('content.qrcode/del') ?>", {qrcode_id}, function(res){
                            if(res.code == 1){
                                that.$message({
                                    message: res.msg,
                                    type: 'success'
                                });
                                that.list.splice(scope.$index,1);
                            }else{
                                that.$message.error(res.msg);
                            }
                        }, 'json')
                    }).catch(() => {});
                },
                editField: function(scope, field){
                    let that = this;
                    let [qrcode_id] = [scope.row.qrcode_id];
                    $.post("<?= url('content.qrcode/editField') ?>", {qrcode_id,field}, function(res){

                        if(res.code == 1){
                            if(field=='status'){
                                that.getList(that.cur_page);
                            }else{
                                that.list[scope.$index][field] = res.data
                            }
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                }
            },
            computed:{

            },
            created: function(){
                this.getList(1);
            }
        });


    });
    function getList(page){
        App.cur_page = page;
        App.getList(page);
    }
</script>

