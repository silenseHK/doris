<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">体验装订单</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2">
                                                <el-button type="primary" @click="addCate()">添加分类</el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list"
                                                    style="width: 100%">
                                                <el-table-column
                                                        prop="cate_id"
                                                        label="ID"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="title"
                                                        label="分类名"
                                                        width="200">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="alias"
                                                        label="别名"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="create_time"
                                                        label="创建时间"
                                                        width="180">
                                                </el-table-column>
                                                <el-table-column
                                                        label="操作"
                                                        width="180">
                                                    <template slot-scope="scope">
                                                        <el-button type="text" size="small">
                                                            <el-link type="primary" :underline="false" :underline="false" @click="edit(scope)" target="_self">编辑</el-link>
                                                        </el-button>
                                                        <el-button type="text" size="small">
                                                            <el-link class="item-delete" type="primary" :underline="false" @click="del(scope)">删除</el-link>
                                                        </el-button>
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

                        <el-drawer
                                :visible.sync="drawer"
                                :with-header="false">
                            <el-container>
                                <el-main>
                                    <el-form ref="form" label-width="100px" size="mini">
                                        <el-form-item label="分类名">
                                            <el-input v-model="title" placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                        <el-form-item label="分类名别名">
                                            <el-input v-model="alias" placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="primary" @click="doAddCate()">提交</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-main>
                            </el-container>
                        </el-drawer>

                        <el-drawer
                                :visible.sync="editDrawer"
                                :with-header="false">
                            <el-container>
                                <el-main>
                                    <el-form ref="form" label-width="100px" size="mini">
                                        <el-form-item label="分类名">
                                            <el-input v-model="editData.title" placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                        <el-form-item label="分类名别名">
                                            <el-input v-model="editData.alias" placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="primary" @click="doEditCate()">提交</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-main>
                            </el-container>
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
                page:'',
                cur_page: 1,
                list:[],
                total: 0,
                drawer: false,
                editDrawer: false,
                title: '',
                alias: '',
                editData: {},
                idx: 0,
            },
            methods:{
                getCateList: function(){
                    let that = this;
                    let page = this.cur_page;
                    $.post("<?= url('content.questionnaire.question/getCateIndexData') ?>", {page}, function(res){
                        that.page = res.data.page;
                        that.list = res.data.list;
                        that.total = res.data.total;
                    }, 'json')
                },

                addCate: function(){
                    this.drawer = true;
                },

                doAddCate: function(){
                    let [title, alias] = [this.title, this.alias];
                    let that = this;
                    $.post("<?= url('content.questionnaire.question/addCate') ?>", {title, alias}, function(res){
                        let type = 'error'
                        if(res.code == 1){
                            type = 'success';
                            that.drawer = false;
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type: type
                        });
                    }, 'json')
                },

                doEditCate: function(){
                    console.log(this.editData);
                    let that = this;
                    let editData = this.editData;
                    $.post("<?= url('content.questionnaire.question/editCate') ?>", editData, function(res){
                        let type = 'error'
                        if(res.code == 1){
                            that.list[editData['index']].alias = editData.alias;
                            that.list[editData['index']].title = editData.title;
                            type = 'success';
                            that.editDrawer = false;
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type: type
                        });
                    }, 'json')
                },

                edit: function(row){
                    let data = this.list[row.$index];
                    this.editData = {
                        alias: data.alias,
                        cate_id: data.cate_id,
                        create_time: data.create_time,
                        delete_time: data.delete_time,
                        title: data.title,
                        wxapp_id: data.wxapp_id,
                        index: row.$index
                    };
                    // console.log(this.editData);
                    // console.log(data);
                    this.editDrawer = true;
                },

                del: function(row){
                    let that = this;
                    console.log(this.list[row.$index]);
                    let cate_id = this.list[row.$index].cate_id
                    this.$confirm('确定删除吗？', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        $.post("<?= url('content.questionnaire.question/delCate') ?>", {cate_id}, function(res){
                            let type = 'error'
                            if(res.code == 1){
                                that.list.splice(row.$index,1);
                                type = 'success';
                                that.editDrawer = false;
                            }
                            that.$message({
                                showClose: true,
                                message: res.msg,
                                type: type
                            });
                        }, 'json')
                    }).catch(() => {

                    });
                }
            },
            computed:{

            },
            created: function(){
                this.getCateList(1);
            }
        });


    });

    function getCateList(page=1){
        App.cur_page = page;
        App.getCateList();
    }
</script>

