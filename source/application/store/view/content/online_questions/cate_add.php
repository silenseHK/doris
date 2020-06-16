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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" v-model="title" name="title"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> ICON </label>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 排序(从小到大) </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="number" v-model="sort" class="tpl-form-input" name="sort"
                                           value="" placeholder="请输入排序值" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">状态 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="status" value="1" data-am-ucheck v-model="status">
                                        <span>上线</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="status" value="0" data-am-ucheck v-model="status">
                                        <span>下线</span>
                                    </label>
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
                status: 1,
                title: '',
                sort: 9999,
                img: '',
                img_id: 0
            },
            methods:{
                delImg: function(){
                    this.img = '';
                    this.img_id = 0;
                },
                submit: function(){
                    let [title, sort, img_id, status] = [this.title, parseInt(this.sort), parseInt(this.img_id), parseInt(this.status)]
                    if(!title || sort<0 || !img_id){
                        layer.msg('请将填写全部选项')
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('content.onlineQuestions/cateAdd') ?>",{title,img_id,sort,status}, function(res){
                        if(res.code == 1){
                            that.img_id = 0;
                            that.img = '';
                            that.sort = 9999;
                            that.status = 1;
                            that.title = '';
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
