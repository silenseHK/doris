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
                                <div class="widget-title am-fl">添加员工</div>
                            </div>

                            <el-form ref="form" :model="form" label-width="120px">
                                <el-form-item label="员工名 *">
                                    <el-input v-model="form.title" maxlength="20"></el-input>
                                </el-form-item>

                                <el-form-item label="登录账号 *">
                                    <el-input v-model="form.account" maxlength="30"></el-input>
                                </el-form-item>

                                <el-form-item label="登录密码 *">
                                    <el-input v-model="form.pwd" type="password" maxlength="30"></el-input>
                                </el-form-item>

                                <el-form-item label="所属分公司 *">
                                    <el-cascader
                                            v-model="form.c_id"
                                            :options="company_list"
                                            :props="{ checkStrictly: true }"
                                            @change="selectCompany"
                                            clearable></el-cascader>
                                </el-form-item>

                                <el-form-item label="所属部门 *">
                                    <el-select v-model="form.a_id" filterable placeholder="请选择">
                                        <el-option
                                                v-for="item in department_list"
                                                :key="item.id"
                                                :label="item.title"
                                                :value="item.id">
                                        </el-option>
                                    </el-select>
                                </el-form-item>

                                <el-form-item label="员工角色 *">
                                    <el-select v-model="form.role_id" filterable placeholder="请选择">
                                        <el-option
                                                v-for="item in role_list"
                                                :key="item.id"
                                                :label="item.title"
                                                :value="item.id">
                                        </el-option>
                                    </el-select>
                                </el-form-item>

                                <el-form-item label="专责 *">
                                    <el-radio v-model="form.is_expert" :label="1">是</el-radio>
                                    <el-radio v-model="form.is_expert" :label="0">否</el-radio>
                                </el-form-item>

                                <el-form-item label="状态 *">
                                    <el-radio v-model="form.status" :label="1">正常</el-radio>
                                    <el-radio v-model="form.status" :label="2">冻结</el-radio>
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
                company_list: <?= json_encode($company_ist) ?>,
                role_list: <?= $role_list ?>,
                department_lists: <?= json_encode($department_list) ?>,

                form: {
                    title: '',
                    a_id: '',
                    c_id: '',
                    is_expert: 0,
                    role_id: '',
                    pwd: '',
                    account: '',
                    status: 1
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
                    let params = {...this.form}
                    params.c_id = params.c_id[params.c_id.length - 1];
                    $.post("<?= url('project.staff/add') ?>", params, function(res){
                        if(res.code == 1){
                            that.$message.success(res.msg);
                            that.init();
                        }else{
                            that.$message.error(res.msg)
                        }
                        that.can_submit = true;
                    }, 'json')
                },
                init(){
                    this.form = {
                        title: '',
                        a_id: '',
                        c_id: '',
                        is_expert: 0,
                        role_id: '',
                        pwd: '',
                        account: '',
                        status: 1
                    }
                },
                selectCompany(){
                    this.form.a_id = '';
                },
                goBack(){
                    window.history.go(-1)
                },
            },
            computed: {
                department_list(){
                    if(!this.form.c_id)return [];
                    let c_id = this.form.c_id[this.form.c_id.length - 1];
                    if(!this.department_lists[c_id])return [];
                    return this.department_lists[c_id];
                },
                check(){
                    if(!this.form.title || !this.form.a_id || !this.form.c_id || (this.form.is_expert != 0 && this.form.is_expert != 1) || !this.form.role_id || !this.form.account || !this.form.pwd || !this.form.status || !this.can_submit)
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
