<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">分公司列表</div>
                </div>
                <div class="widget-body am-fr" id="widget-body">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-form-group">
                            <?php if (checkPrivilege('shop/add')): ?>
                                <div class="am-btn-group am-btn-group-xs">
                                    <a class="am-btn am-btn-default am-btn-success"
                                       href="<?= url('add') ?>">
                                        <span class="am-icon-plus"></span> 新增
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">

                        <el-table
                                :data="lists"
                                style="width: 100%;margin-bottom: 20px;"
                                row-key="id"
                                border
                                default-expand-all
                                :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
                            <el-table-column
                                    prop="id"
                                    label="公司ID"
                                    sortable
                                    width="200">
                            </el-table-column>
                            <el-table-column
                                    prop="title"
                                    label="公司名"
                                    sortable
                                    width="240">
                            </el-table-column>
                            <el-table-column
                                    prop="level"
                                    label="层级"
                                    sortable
                                    width="120">
                            </el-table-column>
                            <el-table-column label="操作">
                                <template slot-scope="scope">
                                    <el-button type="primary" @click="goToEdit(scope)" plain>编辑</el-button>
                                    <el-button type="success" @click="goToAdd(scope)" plain>添加</el-button>
                                    <el-button type="danger" @click="del(scope)" plain>删除</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
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

    $(function () {

        var App = new Vue({
            el: '#widget-body',
            data: {
                form: {
                    title: '',
                },
                lists: <?= json_encode($lists) ?>,
                can_submit: true,
            },
            created(){

            },
            methods: {
                init(){
                    this.form = {
                        title: '',
                    }
                },
                goBack(){
                    window.history.go(-1)
                },
                goToEdit(e){
                    window.location.href="<?= url('edit') ?>/id/" + e.row.id
                },
                goToAdd(e){
                    window.location.href="<?= url('add') ?>/id/" + e.row.id
                },
                del(e){
                    if(e.row.children.length > 0)
                    {
                        this.$message.error('这条数据下还有下级数据，请先处理下级数据');
                        return false;
                    }
                    this.$confirm('此操作将永久删除该条数据, 是否继续?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this
                        $.post("<?= url('delete') ?>",{id: e.row.id}, function(res){
                            if(res.code == 1){
                                that.$message({
                                    type: 'success',
                                    message: '删除成功!'
                                });
                                setTimeout(function(){
                                    window.location.reload();
                                },1500)
                            }else{
                                this.$message({
                                    type: 'error',
                                    message: res.msg
                                });
                            }
                        }, 'json')

                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消删除'
                        });
                    });
                }
            },
            computed: {

            },
        })

    });
</script>

