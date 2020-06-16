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
                                <?php if (checkPrivilege('college.lesson/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('college.lesson/add') ?>">
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

                            <div class="am fr">

                                <div class="am-form-group am-fl">
                                    <?php $lesson_type = $request->get('lesson_type'); ?>
                                    <select name="lesson_type"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择课程类型'}">
                                        <option value=""></option>
                                        <option value="-1"
                                            <?= $lesson_type === '0' ? 'selected' : '' ?>>全部
                                        </option>
                                        <option value="10"
                                            <?= $lesson_type === '10' ? 'selected' : '' ?>>视频
                                        </option>
                                        <option value="20"
                                            <?= $lesson_type === '20' ? 'selected' : '' ?>>直播
                                        </option>
                                    </select>
                                </div>

                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="keywords" placeholder="请输入讲师名称或课程名"
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
                            style="width: 100%">
                            <el-table-column
                                fixed
                                prop="lesson_id"
                                label="ID"
                                width="80">
                            </el-table-column>
                            <el-table-column
                                prop="title"
                                label="课程名称"
                                width="180">
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
                                prop="lecturer.name"
                                label="讲师"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                label="开放权限"
                                width="150">
                                <template slot-scope="scope">
                                    <el-tag v-if="scope.row.is_public" type="success">公开</el-tag>
                                    <el-tag v-if="scope.row.is_private" type="warning">大咖私享</el-tag>
                                    <el-tooltip v-if="scope.row.is_grade" class="item" effect="dark" :content="scope.row.grade" placement="right">
                                        <el-tag type="warning">等级可见</el-tag>
                                    </el-tooltip>
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="cate_crumbs"
                                label="课程分类"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                prop="lesson_type.text"
                                label="课程类型"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                prop="lesson_size.text"
                                label="课程规模"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                prop="total_size"
                                label="课程总节数"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                prop="watch_num"
                                label="观看次数"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                prop="notice_num"
                                label="关注数"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                label="状态"
                                width="120">
                                <template slot-scope="scope">
                                    <el-tag style="cursor: pointer;" v-if="scope.row.status" type="success" @click="changeField(scope, 'status')">上线</el-tag>
                                    <el-tag style="cursor: pointer;" v-else type="info" @click="changeField(scope, 'status')">下线</el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="sort"
                                label="排序"
                                width="120">
                            </el-table-column>
                            <el-table-column
                                label="推荐状态"
                                width="120">
                                <template slot-scope="scope">
                                    <el-tag style="cursor: pointer;" v-if="scope.row.is_recom" type="success" @click="changeField(scope, 'is_recom')">推荐</el-tag>
                                    <el-tag style="cursor: pointer;" v-else type="info" @click="changeField(scope, 'is_recom')">未推荐</el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    prop="create_time"
                                    label="推荐状态"
                                    width="120">
                            </el-table-column>
                            <el-table-column
                                fixed="right"
                                label="操作"
                                width="100">
                                <template slot-scope="scope">
                                    <el-button v-show="scope.row.lesson_size.value == 20" type="text" size="small">
                                        <el-link type="primary" :underline="false" :href="initClassUrl(scope.row.lesson_id)" target="_self">课时列表</el-link>
                                    </el-button>
                                    <el-button v-show="scope.row.is_private == 1" type="text" size="small">
                                        <el-link type="primary" :underline="false" @click="code(scope.row.lesson_id)" target="_self">私享码</el-link>
                                    </el-button>
                                    <el-button type="text" size="small">
                                        <el-link type="primary" :underline="false" :href="initEditUrl(scope.row.lesson_id)" target="_self">编辑</el-link>
                                    </el-button>
                                    <el-button type="text" size="small">
                                        <el-link class="item-delete" type="primary" :underline="false" @click="del(scope.row.lesson_id)">删除</el-link>
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
                                            <el-button @click="delCode(scope)" type="text" size="small">删除</el-button>
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
                edit_url:"<?= url('college.lesson/edit') ?>",
                tableData: <?= json_encode($data) ?>,
                del_url: "<?= url('college.lesson/delete') ?>",
                class_url: "<?= url('college.college_class/index') ?>",
                field_change_url: "<?= url('college.lesson/changefield') ?>",
                drawer: false,
                innerDrawer: false,
                lesson_id: 0,
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
                        let grade = '';
                        v.limit_grade.forEach(function(vv,kk){
                            if(kk==0){
                                grade += vv.name
                            }else{
                                grade += "," + vv.name
                            }
                        });
                        that.tableData[k]['grade'] = grade;
                    })
                },
                initEditUrl:function(lesson_id){
                    return this.edit_url + "/lesson_id/" + lesson_id;
                },
                initClassUrl:function(lesson_id){
                    return this.class_url + "/lesson_id/" + lesson_id;
                },
                del: function(lesson_id){
                    let url = this.del_url;
                    this.$confirm('课程下的课时将一同删除,确定删除该吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let that = this;
                        $.post(url,{lesson_id}, function(res){
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

                code: function(lesson_id){
                    this.drawer = true;
                    this.lesson_id = lesson_id;
                    this.openCodeDrawer();
                },
                addCode: function(){
                    this.innerDrawer = true
                },
                openCodeDrawer: function(){
                    if(this.drawer){
                        let that = this;
                        let lesson_id = this.lesson_id;
                        $.post("<?= url('college.lesson/codelist') ?>", {lesson_id}, function(res){
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
                    let lesson_id = this.lesson_id;
                    let that = this;
                    $.post("<?= url('college.lesson/addCode') ?>", {start_time, expire_time, can_use_num, lesson_id}, function(res){
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
                },
                changeField: function(e, field){
                    let that = this;
                    let lesson_id = e.row.lesson_id;
                    $.post(that.field_change_url, {lesson_id, field}, function(res){
                        if(res.code == 1){
                            that.tableData[e.$index][field] = res.data;
                        }else{
                            that.$message.error(res.msg);
                        }
                    }, 'json')
                },
            },
            computed:{

            },
            created: function(){
                this.initList();
            }
        });


    });
</script>

