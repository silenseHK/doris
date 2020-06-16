<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">课程列表</div>
                </div>
                <div class="widget-body am-fr">

                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('college.college_class/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('college.college_class/add', ['lesson_id'=>$lesson_id]) ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>" />

                            <div class="am fr">

                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="keywords" placeholder="请输入课时名称"
                                               value="<?= $request->get('keywords') ?>">
                                        <div class="am-input-group-btn">
                                            <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">

                        <el-table
                            :data="tableData"
                            border
                            size="medium"
                            style="width: 100%">
                            <el-table-column
                                fixed
                                prop="class_id"
                                label="ID"
                                width="80">
                            </el-table-column>
                            <el-table-column
                                prop="title"
                                label="课时名称"
                                width="220">
                            </el-table-column>
                            <el-table-column
                                label="封面图"
                                width="120">
                                <template slot-scope="scope">
                                    <div class="demo-image__preview">
                                    <el-image style="width: 100px; height: 100px" :src="scope.row.image.file_path" :preview-src-list="scope.row.image_url"></el-image>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="filter_desc"
                                label="简介"
                                width="300">
                            </el-table-column>
                            <el-table-column
                                label="课程资源"
                                width="120">
                                <template slot-scope="scope">
                                    <el-tooltip v-if="scope.row.lesson.lesson_type.value == 10" class="item" effect="dark" content="点击可预览" placement="right">
                                        <el-link type="info" :underline="false" :href="scope.row.video_url" target="_blank">视频</el-link>
                                    </el-tooltip>
                                    <el-tooltip v-if="scope.row.lesson.lesson_type.value == 20" class="item" effect="dark" :content="scope.row.live_room.room_name" placement="right">
                                        <el-link type="info" :underline="false">直播</el-link>
                                    </el-tooltip>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="状态"
                                width="120">
                                <template slot-scope="scope">
                                    <el-button @click="changeField(scope, 'status')" size="mini" v-if="scope.row.status" type="success">上线</el-button>
                                    <el-button @click="changeField(scope, 'status')" size="mini" v-else type="info">下线</el-button>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="排序"
                                width="180">
                                <template slot-scope="scope">
                                    <el-input-number @change="changeSort(scope)" min="1" max="99999" size="mini" v-model="scope.row.sort"></el-input-number>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="免费试看"
                                width="120">
                                <template slot-scope="scope">
                                    <el-button @click="changeField(scope, 'is_free')" size="mini" v-if="scope.row.is_free" type="success">是</el-button>
                                    <el-button @click="changeField(scope, 'is_free')" size="mini" v-else type="info">否</el-button>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    prop="create_time"
                                    label="创建时间"
                                    width="180">
                            </el-table-column>
                            <el-table-column
                                    label="操作"
                                    width="160">
                                <template slot-scope="scope">
                                    <el-button type="text" size="small">
                                        <el-link type="primary" :underline="false" :href="initEditUrl(scope.row.class_id)" target="_self">编辑</el-link>
                                    </el-button>
                                    <el-button type="text" size="small">
                                        <el-link class="item-delete" type="primary" :underline="false" @click="del(scope.row.class_id)">删除</el-link>
                                    </el-button>
                                    <el-button v-if="scope.row.lesson.is_private" type="text" size="small">
                                        <el-link type="primary" :underline="false" @click="code(scope.row.class_id)">私享码</el-link>
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>

                        <el-drawer
                                title="私享码"
                                :visible.sync="drawer"
                                :with-header="false"
                        >
                            <template>
                                <el-table
                                        :data="tableData2"
                                        border
                                        style="width: 90%; margin: 30px 20px; height:auto;"
                                >
                                    <el-table-column
                                            prop="code"
                                            label="私享码"
                                            width="100">
                                    </el-table-column>
                                    <el-table-column
                                            prop="start_time"
                                            label="生效时间"
                                            width="120">
                                    </el-table-column>
                                    <el-table-column
                                            prop="expire_time"
                                            label="有效期至"
                                            width="120">
                                    </el-table-column>
                                    <el-table-column
                                            prop="can_use_num"
                                            label="可使用次数"
                                            width="100">
                                    </el-table-column>
                                    <el-table-column
                                            prop="had_use_num"
                                            label="已使用次数"
                                            width="100">
                                    </el-table-column>
                                    <el-table-column
                                            prop="create_time"
                                            label="创建时间"
                                            width="120">
                                    </el-table-column>
                                    <el-table-column
                                            fixed="right"
                                            label="操作"
                                            width="80">
                                        <template slot-scope="scope">
                                            <el-button v-if="scope.row.code_type == 10" @click="delCode(scope)" type="text" size="small">删除</el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>

                            </template>

                            <el-container>
                                <el-header>
                                    <el-button @click="addCode" type="primary" size="medium">添加私享码</el-button>
                                </el-header>
                            </el-container>

                            <el-drawer
                                    title="添加私享码"
                                    :append-to-body="true"
                                    :visible.sync="innerDrawer">
                                <el-container>
                                    <el-main>
                                        <el-form ref="form" :model="sizeForm" label-width="100px" size="mini">
                                            <el-form-item label="生效时间">
                                                <div class="block">
                                                    <el-date-picker
                                                            v-model="sizeForm.date"
                                                            type="datetimerange"
                                                            range-separator="至"
                                                            start-placeholder="开始日期"
                                                            end-placeholder="结束日期">
                                                    </el-date-picker>
                                                </div>
                                            </el-form-item>
                                            <el-form-item label="可使用次数">
                                                <el-input-number min="0" max="99999999" v-model="sizeForm.can_use_num" :step="5"></el-input-number>
                                            </el-form-item>
                                            <el-form-item size="large">
                                                <el-button type="primary" @click="submitCode" size="medium">立即添加</el-button>
                                                <el-button @click="hideInnerDrawer" size="medium">取消</el-button>
                                            </el-form-item>
                                        </el-form>
                                    </el-main>
                                </el-container>
                            </el-drawer>

                        </el-drawer>

                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
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
            el: '#my-table',
            data: {
                edit_url:"<?= url('college.college_class/edit') ?>",
                tableData: <?= json_encode($data) ?>,
                del_url: "<?= url('college.college_class/delete') ?>",
                field_change_url: "<?= url('college.college_class/changefield') ?>",
                drawer: false,
                innerDrawer: false,
                class_id: 0,
                tableData2: [],

                sizeForm: {
                    date: [],
                    can_use_num: 100
                }
            },
            methods:{
                initList: function(){
                    let that = this;
                    this.tableData.forEach(function(v,k){
                        that.tableData[k]['image_url'] = [v.image.file_path];
                    })
                },
                initEditUrl:function(class_id){
                    return this.edit_url + "/class_id/" + class_id;
                },
                del: function(class_id){
                    let url = this.del_url;
                    this.$confirm('确定删除该吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post(url,{class_id}, function(res){
                            if(res.code == 1){
                                setTimeout(function(){
                                    location.reload();
                                },1000)
                            }
                            that.$message({
                                type: 'success',
                                message: res.msg
                            });
                        }, 'json')

                    }).catch(() => {});
                },
                changeField: function(e, field){
                    let that = this;
                    let class_id = e.row.class_id;
                    $.post(that.field_change_url, {class_id, field}, function(res){
                        if(res.code == 1){
                            that.tableData[e.$index][field] = res.data;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },
                changeSort: function(e){
                    let that = this;
                    let [class_id, field, value] = [e.row.class_id, 'sort', e.row.sort];
                    $.post(that.field_change_url, {class_id, field, value}, function(res){
                        if(res.code == 1){
                            that.tableData[e.$index][field] = res.data;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },
                code: function(class_id){
                    this.drawer = true;
                    this.class_id = class_id;
                    this.openCodeDrawer();
                },
                addCode: function(){
                    this.innerDrawer = true
                },
                openCodeDrawer: function(){
                    if(this.drawer){
                        let that = this;
                        let class_id = this.class_id;
                        $.post("<?= url('college.college_class/codelist') ?>", {class_id}, function(res){
                            if(res.code == 1){
                                that.tableData2 = res.data.list;
                                return false;
                            }else{
                                that.$message.error(res.msg);
                            }
                        }, 'json')
                    }
                },
                submitCode: function () {
                    if(!this.sizeForm.date){
                        this.$message.error('请选择有效期');
                        return false;
                    }
                    let start_time = this.initDate(this.sizeForm.date[0]);
                    let expire_time = this.initDate(this.sizeForm.date[1]);
                    let can_use_num = this.sizeForm.can_use_num;
                    let class_id = this.class_id;
                    let that = this;
                    $.post("<?= url('college.college_class/addCode') ?>", {start_time, expire_time, can_use_num, class_id}, function(res){
                        that.$message({
                            type: 'success',
                            message: res.msg
                        });
                        if(res.code == 1){
                            that.sizeForm.can_use_num = 100;
                            that.sizeForm.date = [];
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
                hideInnerDrawer: function(){
                    this.innerDrawer = false;
                },
                delCode: function(e){
                    this.$confirm('确定删除该吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        let code_id = e.row.code_id;
                        $.post("<?= url('college.college_class/delcode') ?>",{code_id}, function(res){
                            if(res.code == 1){
                                that.tableData2.splice(e.$index, 1)
                            }
                            that.$message({
                                type: 'success',
                                message: res.msg
                            });
                        }, 'json')

                    }).catch(() => {});
                }
            },
            computed:{

            },
            created: function(){
                this.initList();
            }
        });


    });
</script>

