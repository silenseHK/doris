<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>

    body > .el-container {
        margin-bottom: 40px;
    }

    .el-row {
        margin-bottom: 20px;
    &:last-child {
         margin-bottom: 0;
     }
    }

    .el-row .el-col:first-child{
        text-align: right;
    }

    .el-tag--plain{
        border:none;
    }

</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加问题分类</div>
                            </div>

                            <el-form ref="form" :model="form" label-width="120px">
                                <el-form-item label="分类名 *">
                                    <el-input v-model="form.title" maxlength="20"></el-input>
                                </el-form-item>

                                <el-form-item label="一级分类 *">
                                    <el-select v-model="form.level_1" filterable placeholder="请选择" @change="selectLevel1">
                                        <el-option label="一级分类" value="-1"></el-option>
                                        <el-option
                                                v-for="(item, index) in cate_list"
                                                :key="item.id"
                                                :label="item.title"
                                                :value="index">
                                        </el-option>
                                    </el-select>
                                </el-form-item>

                                <el-form-item label="二级分类" v-show="cate_list[form.level_1]">
                                    <el-select v-model="form.level_2" filterable placeholder="请选择">
                                        <el-option label="二级分类" value="-1"></el-option>
                                        <el-option
                                                v-for="(item, index) in (cate_list[form.level_1] ? cate_list[form.level_1].children : [])  "
                                                :key="item.id"
                                                :label="item.title"
                                                :value="index">
                                        </el-option>
                                    </el-select>
                                </el-form-item>

                                <el-form-item label="状态 *">
                                    <el-radio v-model="form.status" :label="1">使用</el-radio>
                                    <el-radio v-model="form.status" :label="2">禁用</el-radio>
                                </el-form-item>

                                <el-form-item>
                                    <el-button type="primary" @click="onSubmit">添加</el-button>
                                    <el-button @click="goBack">取消</el-button>
                                </el-form-item>
                            </el-form>

                        </fieldset>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>
<script>
    $(function () {

        var App = new Vue({
            el: '#my-form',
            data: {
                form: <?= json_encode($info) ?>,
                can_submit: true,
                cate_list: <?= json_encode($cateList) ?>
            },
            created(){
                //初始化
                if(this.form.level == 1){
                    this.form.level_1 = '-1'
                    this.form.level_2 = ''
                }else{
                    if(this.form.level == 2){ //二级
                        this.cate_list.forEach((v, k) => {
                            if(v.id == this.form.pid){
                                this.form.level_1 = k;
                            }
                        })
                        this.form.level_2 = '-1'
                    }else{ //三级
                        console.log(this.form)
                        this.cate_list.forEach((v, k) => {
                            if(v.id == this.form.parent.pid){
                                this.form.level_1 = k;
                            }
                        })
                        this.cate_list[this.form.level_1].children.forEach((v, k) => {
                            if(v.id == this.form.pid){
                                this.form.level_2 = k;
                            }
                        })
                    }
                }
            },
            methods: {
                onSubmit() {
                    if(!this.check){
                        this.$message('请将数据补充完整')
                        return false;
                    }
                    //判断上级id
                    let {id, level_1, level_2, title, status} = this.form;
                    let p_id, level;
                    if(level_1 == -1){
                        p_id = 0;
                        level = 1;
                    }else{
                        if(level_2 == -1){ //二级分类
                            p_id = this.cate_list[level_1].id
                            level = 2;
                        }else{ //三级分类
                            p_id = this.cate_list[level_1].children[level_2].id
                            level = 3;
                        }
                    }
                    let that = this;
                    this.can_submit = false;
                    $.post("<?= url('project.matter/editCate') ?>", {id, title, status, pid: p_id, level}, function(res){
                        that.can_submit = true;
                        if(res.code == 1){
                            that.$message.success(res.msg);
                        }else{
                            that.$message.error(res.msg)
                        }
                    }, 'json')
                },
                goBack(){
                    window.history.go(-1)
                },
                selectLevel1(e)
                {
                    console.log(this.form)
                    this.form.level_2 = '';
                },
            },
            computed: {
                check(){
                    if(!this.form.title || !this.can_submit)
                        return false;
                    if(this.form.level_1 == '' || this.form.level_2 == '')
                        return false;
                    return true;
                },
            },
        })

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
