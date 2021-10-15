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
                                            <div v-show="imgs.length > 0" class="uploader-list am-cf">
                                                <draggable  v-model="imgs" @end="drag" :options="{delay:30,touchStartThreshold: 1,preventOnFilter: false,animation:300,chosenClass:'sortable-chosen',forceFallback:true,fallbackOnBody:false,scroll:true,scrollSensitivity:120,filter: '.undraggable'}">
                                                    <div class="file-item" v-for="(item, key) in imgs">
                                                        <a :href="item.file_path" title="点击查看大图" target="_blank">
                                                            <img :src="item.file_path" width="150px" height="auto">
                                                        </a>
                                                        <i @click="delImg(key)" style="position:absolute;right:-10px;top:-10px;cursor: pointer" class="am-icon-close"></i>
                                                    </div>
                                                </draggable>
                                            </div>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 类型 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <select name="type" v-model="type_">
                                        <?php if (isset($typeList)): foreach ($typeList as $item): ?>
                                            <option value="<?= $item['value'] ?>"><?= $item['title'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
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
<script src="assets/common/js/Sortable.min.js?v=<?= $version ?>"></script>
<script src="assets/common/js/vuedraggable.min.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        var App = new Vue({
            el: '#my-form',
            data: {
                max_bmi: <?= $data['max_bmi'] ?>,
                min_bmi: <?= $data['min_bmi'] ?>,
                imgs: <?= json_encode($data['images']) ?>,
                is_special: "<?= $data['is_special'] ?>",
                id: <?= $data['id'] ?>,
                type_: "<?= $data['type']['value'] ?>"
            },
            methods:{
                drag: function(e){
                    console.log(e)
                },
                delImg: function(idx){
                    this.imgs.splice(idx, 1);
                },
                submit: function(){
                    let [max_bmi, min_bmi, imgs, id, is_special, type_] = [parseFloat(this.max_bmi), parseFloat(this.min_bmi), this.imgs, this.id, parseInt(this.is_special), parseInt(this.type_)]
                    if(!max_bmi || min_bmi<0){
                        layer.msg('请将填写全部选项')
                        return false;
                    }
                    if(max_bmi <= min_bmi){
                        layer.msg('bmi最大值必须大于bmi最小值')
                        return false;
                    }
                    if(imgs.length <= 0){
                        layer.msg('请上传配餐图')
                        return false;
                    }
                    if(!type_){
                        layer.msg('请选择类型')
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('content.foodGroup/edit') ?>",{max_bmi,min_bmi,imgs,id,is_special,type_}, function(res){
                        if(res.code == 1){
                            layer.msg('操作成功');
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
                data.forEach((v, k)=>{
                    App.imgs.push(v);
                })
            }
        });

    });
</script>
