
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
                <form id="my-form" class="am-form tpl-form-line-form" method="post" v-cloak>
                    <div class="widget-body">
                        <fieldset>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑二维码</div>
                            </div>

                            <el-container>

                                <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm" style="width:500px;">

                                    <el-form-item label="二维码标题" prop="title">
                                        <el-input v-model="ruleForm.title"></el-input>
                                    </el-form-item>

                                    <el-form-item label="二维码类型" prop="qrcode_type">
                                        <el-select v-model="ruleForm.qrcode_type" placeholder="请选择二维码类型" style="width:100%;">
                                            <el-option label="群二维码" value="10"></el-option>
                                            <el-option label="营养师二维码" value="20"></el-option>
                                        </el-select>
                                    </el-form-item>

                                    <el-form-item label="封面" prop="img" prop="img">
                                        <div v-if="ruleForm.img" class="demo-image__preview">
                                            <el-image
                                                    style="width: 100px; height: 100px"
                                                    :src="ruleForm.img"
                                                    :preview-src-list="ruleForm.img_list">
                                            </el-image>
                                        </div>
                                        <el-col style="margin-left: 0; padding-left:0; text-align: left;" :span="8" id="cover-wrap">
                                            <el-button type="primary" @click="uploadImg" size="medium">上传<i class="el-icon-upload el-icon--right"></i></el-button>
                                        </el-col>
                                    </el-form-item>

                                    <el-form-item label="启用状态" prop="status">
                                        <el-switch v-model="ruleForm.status" active-value="1" inactive-value="0" active-text="启用" inactive-text="不启用"></el-switch>
                                    </el-form-item>

                                    <el-form-item>
                                        <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
                                    </el-form-item>

                                </el-form>

                            </el-container>

                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>

<script>
    $(function () {

        var App = new Vue({
            el: '#my-form',
            data: {
                ruleForm: <?= json_encode($info) ?>,
                rules: {
                    title: [
                        { required: true, message: '请输入二维码标题', trigger: 'blur' },
                        { min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur' }
                    ],
                    img: [
                        { required: true, message: '请上传二维码', trigger: 'blur' },
                    ],
                },
                qrcode_id: <?= $info['qrcode_id'] ?>
            },
            methods:{
                uploadImg: function(){
                    $('#cover-wrap').selectImages({
                        multiple: false,
                        done: function (data) {
                            App.ruleForm.img_id = data[0]['file_id']
                            App.ruleForm.img = data[0]['file_path']
                            App.ruleForm.img_list.push(data[0]['file_path'])
                        }
                    });
                },

                submitForm(formName) {
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let data = this.ruleForm;
                            let that = this;
                            let qrcode_id = this.qrcode_id;
                            data.qrcode_id = qrcode_id;
                            $.post("<?= url('content.qrcode/edit') ?>", data, function(res){
                                that.$message({
                                    type: 'success',
                                    message: res.msg
                                });
                            }, 'json')
                        } else {
                            return false;
                        }
                    });
                },
            }
        })

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
