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
                                <div class="widget-title am-fl">配置权限</div>
                            </div>

                            <el-form ref="form" label-width="120px">
                                <el-checkbox-group v-model="page">
                                <el-row v-for="(auth, index) in auths">
                                    <el-checkbox :label="auth.id" :key="auth.title" @change="handleCheckAllChange(index)">{{auth.title}}</el-checkbox>
                                    <div style="margin: 15px 0;"></div>
                                    <el-checkbox-group v-model="power[index]" @change="handleCheckedCitiesChange">
                                        <el-checkbox v-for="needle in auth.needle" :label="needle.id" :key="needle.title">{{needle.title}}</el-checkbox>
                                    </el-checkbox-group>
                                </el-row>
                                </el-checkbox-group>

                                <el-row style="margin-top:40px;">
                                    <el-button type="primary" @click="onSubmit">确定</el-button>
                                    <el-button @click="goBack">取消</el-button>
                                </el-row>
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
                checkAll: false,
                isIndeterminate: false,
                auths: <?= json_encode($auths) ?>,
                power: [],
                page: [],
                role_id: <?= $role_id ?>
            },
            created(){
                let power = <?= json_encode($power); ?>;
                //生成model
                this.auths.forEach((v, k) => {
                    let p = [];
                    v.needle.forEach((vv, kk) => {
                        if(power.indexOf(vv.id) != -1){
                            p.push(vv.id)
                        }
                    })
                    this.power.push(p)
                })
                this.init()
            },
            methods: {
                onSubmit() {
                    let that = this;
                    let {role_id, power} = this
                    $.post("<?= url('project.role/auth') ?>", {power, role_id}, function(res){
                        if(res.code == 1){
                            that.$message.success(res.msg);
                        }else{
                            that.$message.error(res.msg)
                        }
                    }, 'json')
                },
                handleCheckAllChange(val) {
                    if(this.page.indexOf(this.auths[val].id) != -1){ //点击了全选
                        this.power[val] = [];
                        this.auths[val].needle.forEach((v, k) => {
                            this.power[val].push(v.id);
                        })
                    }else{  //点击取消全选
                        this.power[val] = [];
                    }
                },
                handleCheckedCitiesChange(value) {
                    this.page = [];
                    this.init()
                },

                goBack(){
                    window.history.go(-1)
                },

                init(){
                    this.power.forEach((v, k) => {
                        if(this.auths[k].needle.length === v.length){
                            this.page.push(this.auths[k].id)
                        }
                    })
                },
            },
            computed: {

            },
        })

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
