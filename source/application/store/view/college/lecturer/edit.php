<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<link rel="stylesheet" href="assets/store/css/element.css">

<style>
    .el-tag + .el-tag {
        margin-left: 10px;
    }
    .button-new-tag {
        margin-left: 10px;
        height: 32px;
        line-height: 30px;
        padding-top: 0;
        padding-bottom: 0;
    }
    .input-new-tag {
        width: 90px;
        margin-left: 10px;
        vertical-align: bottom;
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
                                <div class="widget-title am-fl">编辑讲师</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 讲师名称 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" v-model="name" class="tpl-form-input" name="name"
                                           value="" placeholder="请输入讲师名称" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 头像 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="j-image upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div v-show="img_id > 0" class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a :href="img" title="点击查看大图" target="_blank">
                                                        <img :src="img">
                                                    </a>
                                                    <input type="hidden" name="goods[images][]" value="4">
                                                    <i @click="delImg" style="position:absolute;right:-10px;top:-10px;cursor: pointer" class="am-icon-close"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="help-block">
                                            <small>尺寸：宽750像素 高大于(等于)1200像素</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 讲师描述 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" v-model="desc" class="tpl-form-input" name="desc"
                                           value="" placeholder="请输入讲师描述" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 讲师标签 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <el-tag
                                        :key="tag"
                                        v-for="tag in label"
                                        closable
                                        :disable-transitions="false"
                                        @close="handleClose(tag)">
                                        {{tag}}
                                    </el-tag>
                                    <el-input
                                        class="input-new-tag"
                                        v-if="inputVisible"
                                        v-model="inputValue"
                                        ref="saveTagInput"
                                        size="small"
                                        @keyup.enter.native="handleInputConfirm"
                                        @blur="handleInputConfirm"
                                    >
                                    </el-input>
                                    <el-button v-else class="button-new-tag" size="small" @click="showInput">+ 标签</el-button>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button @click="submit" type="button" class="j-submit am-btn am-btn-secondary"> 提交
                                    </button>
                                </div>
                            </div>
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
<script>
    $(function () {

        var App = new Vue({
            el: '#my-form',
            data: {
                name: "<?= $info['name'] ?>",
                img: "<?= $info['image']['file_path'] ?>",
                img_id: "<?= $info['avatar'] ?>",
                desc: "<?= $info['desc'] ?>",
                lecturer_id: "<?= $info['lecturer_id'] ?>",
                label: <?= json_encode($info['label_list']) ?>,
                inputVisible: false,
                inputValue: ""
            },
            methods:{
                delImg: function(){
                    this.img = '';
                    this.img_id = 0;
                },
                submit: function(){
                    let [name, desc, label, img_id, lecturer_id] = [this.name, this.desc, this.label, parseFloat(this.img_id), this.lecturer_id]
                    if(!name || !img_id){
                        layer.msg('请将填写必填项')
                        return false;
                    }
                    if(!img_id){
                        layer.msg('请上传配餐图')
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('college.lecturer/edit') ?>",{name,desc,avatar:img_id,label,lecturer_id}, function(res){
                        if(res.code == 1){
                            layer.msg('操作成功');
                        }else{
                            layer.msg(res.msg)
                        }
                    },'json')
                },

                handleClose(tag) {
                    this.label.splice(this.label.indexOf(tag), 1);
                },

                showInput() {
                    this.inputVisible = true;
                    this.$nextTick(_ => {
                        this.$refs.saveTagInput.$refs.input.focus();
                    });
                },

                handleInputConfirm() {
                    let inputValue = this.inputValue;
                    if (inputValue) {
                        this.label.push(inputValue);
                    }
                    this.inputVisible = false;
                    this.inputValue = '';
                }
            },
        })

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        // 选择图片：分销中心首页
        $('.j-image').selectImages({
            multiple: false,
            done: function (data) {
                App.img_id = data[0]['file_id']
                App.img = data[0]['file_path']
            }
        });

    });
</script>
