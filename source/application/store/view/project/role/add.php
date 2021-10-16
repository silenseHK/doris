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
                                <div class="widget-title am-fl">添加分公司</div>
                            </div>

                            <el-form ref="form" :model="form" label-width="120px">
                                <el-form-item label="角色名 *">
                                    <el-input v-model="form.title" maxlength="20"></el-input>
                                </el-form-item>

                                <el-form-item label="描述">
                                    <el-input v-model="form.desc" maxlength="255"></el-input>
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
                form: {
                    title: '',
                    desc: '',
                },
                can_submit: true,
            },
            created(){

            },
            methods: {
                onSubmit() {
                    if(!this.check){
                        this.$message('请将数据补充完整')
                        return false;
                    }
                    let that = this;
                    this.can_submit = false;
                    $.post("<?= url('project.role/add') ?>", {...this.form}, function(res){
                        that.can_submit = true;
                        if(res.code == 1){
                            that.$message.success(res.msg);
                            that.init();
                        }else{
                            that.$message.error(res.msg)
                        }
                    }, 'json')
                },
                init(){
                    this.form = {
                        title: '',
                        desc: '',
                    }
                },
                goBack(){
                    window.history.go(-1)
                },
            },
            computed: {
                check(){
                    if(!this.form.title || !this.can_submit)
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
