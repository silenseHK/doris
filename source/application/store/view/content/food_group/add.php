<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加搭配</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 配餐图 </label>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 最大BMI </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="number" v-model="max_bmi" class="tpl-form-input" name="max_bmi"
                                           value="" placeholder="请输入BMI值" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 最小BMI </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input v-model="min_bmi" type="number" class="tpl-form-input" name="min_bmi"
                                           value="" placeholder="请输入BMI值" required>
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
<script>
    $(function () {

        var App = new Vue({
            el: '#my-form',
            data: {
                max_bmi: 0,
                min_bmi: 0,
                img: '',
                img_id: 0
            },
            methods:{
                delImg: function(){
                    this.img = '';
                    this.img_id = 0;
                },
                submit: function(){
                    let [max_bmi, min_bmi, img_id] = [parseFloat(this.max_bmi), parseFloat(this.min_bmi), parseFloat(this.img_id)]
                    if(!max_bmi || min_bmi<0 || !img_id){
                        layer.msg('请将填写全部选项')
                        return false;
                    }
                    if(max_bmi <= min_bmi){
                        layer.msg('bmi最大值必须大于bmi最小值')
                        return false;
                    }
                    if(!img_id){
                        layer.msg('请上传配餐图')
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('content.foodGroup/add') ?>",{max_bmi,min_bmi,img_id}, function(res){
                        if(res.code == 1){
                            that.img_id = 0;
                            that.img = '';
                            that.max_bmi = 0;
                            that.min_bmi = 0;
                            layer.msg('添加成功');
                        }else{
                            layer.msg(res.msg)
                        }
                    },'json')
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
