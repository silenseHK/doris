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
                                <div class="widget-title am-fl">添加课时</div>
                            </div>

                            <el-container>

                                <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm" style="width:500px;">

                                    <el-form-item label="课时标题" prop="title">
                                        <el-input v-model="ruleForm.title" maxlength="50" show-word-limit></el-input>
                                    </el-form-item>

                                    <el-form-item label="封面" prop="img">
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

                                    <el-form-item label="描述" prop="desc">
                                        <el-input
                                                type="textarea"
                                                autosize
                                                placeholder="请输入内容"
                                                maxlength="255"
                                                show-word-limit
                                                v-model="ruleForm.desc">
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item label="视频" prop="video_url">
                                        <el-input
                                                placeholder=""
                                                v-model="ruleForm.video_url"
                                                clearable>
                                        </el-input>
                                        <el-link v-show="ruleForm.video_url" :href="ruleForm.video_url" target="_blank">预览视频</el-link>
                                        <el-button id="test5" type="primary" size="medium">{{ruleForm.video_url?"重新上传":"上传"}}<i class="el-icon-upload el-icon--right"></i></el-button>
                                    </el-form-item>

                                    <el-form-item label="详情描述" prop="content">
                                        <textarea id="container">{{ruleForm.content}}</textarea>
                                    </el-form-item>

                                    <el-form-item label="是否试看" prop="is_free">
                                        <el-switch v-model="ruleForm.is_free" active-value="1" inactive-value="0" active-text="是" inactive-text="否"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="状态" prop="status">
                                        <el-switch v-model="ruleForm.status" active-value="1" inactive-value="0" active-text="上线" inactive-text="下线"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="排序" prop="sort">
                                        <el-input-number size="small" v-model="ruleForm.sort" min="1" max="99999"></el-input-number>
                                        <el-tag  effect="plain" type="warning">越小越靠前</el-tag>
                                    </el-form-item>

                                    <el-form-item>
                                        <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
<!--                                        <el-button @click="resetForm('ruleForm')">重置</el-button>-->
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
                ruleForm: {
                    title: "<?= $info['title'] ?>",
                    desc: "<?= $info['desc'] ?>",
                    img_id: <?= $info['cover'] ?>,
                    img: "<?= $info['image']['file_path'] ?>",
                    img_list: ["<?= $info['image']['file_path'] ?>"],
                    video_url: "<?= $info['video_url'] ?>",
                    is_free: "<?= $info['is_free'] ?>",
                    status: "<?= $info['status'] ?>",
                    sort: <?= $info['sort'] ?>,
                    lesson_id: " <?= $info['lesson_id'] ?>",
                    content: '<?= $info['content'] ?>',
                    class_id: "<?= $info['class_id'] ?>"
                },
                rules: {
                    title: [
                        { required: true, message: '请输入活动名称', trigger: 'blur' },
                        { min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
                    ],
                    img: [
                        { required: true, message: '请上传封面', trigger: 'blur' },
                    ],
                    video_url: [
                        { required: true, message: '请上传视频', trigger: 'blur' },
                    ]
                }
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
                            data.content = editor.getContent();
                            $.post("<?= url('college.college_class/edit') ?>", data, function(res){
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
                resetForm(formName) {
                    this.$refs[formName].resetFields();
                    editor.execCommand('cleardoc')
                }
            },
            mounted: function(){

            }
        })

        // 富文本编辑器
        var editor = UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        layui.use('upload', function() {
            var $ = layui.jquery
                , upload = layui.upload;
            upload.render({
                elem: '#test5'
                , url: "<?= url('upload/video')?>" //改成您自己的上传接口
                , accept: 'video' //视频
                ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                    layer.load(); //上传loading
                }
                , done: function (res) {
                    layer.closeAll();
                    layer.msg('上传成功');
                    App.ruleForm.video_url = res.data.url;
                }
            });
        })

    });
</script>
