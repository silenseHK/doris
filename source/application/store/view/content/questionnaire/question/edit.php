<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加问题</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 问题 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="label" v-model="label"
                                           value="" placeholder="请输入问题" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">类型 </label>
                                <div class="am-u-sm-2 am-u-end">
                                    <select @change="changeType" name="type" required v-model="type">
                                            <option v-for="type in type_list" :value="type.value">{{type.text}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> name </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="name" v-model="name"
                                           value="" placeholder="请输入name" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否必填 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="is_require" value="1" data-am-ucheck v-model="is_require">
                                        <span>必填</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="is_require" value="0" data-am-ucheck v-model="is_require">
                                        <span>非必填</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group" v-show="showOption">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">选项 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label v-for="(item,index) in answer" class="am-radio-inline">
                                        <span>{{item.mark}}</span>
                                        <span>{{item.label}}</span>
                                        <span>({{item.point}}分)</span>
                                        <i @click="delOption($event, index)" class="am-icon-ban am-icon-fw"></i>
                                    </label>
                                    <button @click="alertAddModel" type="button" class="j-submit am-btn-success am-btn-sm"> 添加选项
                                    </button>
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

                    <fieldset v-show="showModel">

                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">添加选项</div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> mark </label>
                            <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                <input type="text" class="tpl-form-input" name="option_mark" v-model="option_mark"
                                       value="" placeholder="请输入mark" required>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> label </label>
                            <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                <input type="text" class="tpl-form-input" name="option_label" v-model="option_label"
                                       value="" placeholder="请输入label" required>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 分数 </label>
                            <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                <input type="text" class="tpl-form-input" name="option_point" v-model="option_point"
                                       value="" placeholder="请输入分数" required>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否用户填写 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="option_is_input" value="1" data-am-ucheck v-model="option_is_input">
                                    <span>是</span>
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="option_is_input" value="0" data-am-ucheck checked v-model="option_is_input">
                                    <span>否</span>
                                </label>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                <button @click="addOption" type="button" class="j-submit am-btn am-btn-secondary"> 确定添加
                                </button>
                                <button @click="cancelOption" type="button" class="j-submit am-btn am-btn-secondary"> 取消
                                </button>
                            </div>
                        </div>
                    </fieldset>

                </form>

            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/vue.min.js"></script>
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        var App = new Vue({
            el : "#my-form",
            data : {
                answer: <?= json_encode($info['option']) ?>,
                showModel : false,
                option_mark: '',
                option_label: '',
                option_point: 0,
                option_is_input: 0,
                type: <?= $info['type']['value'] ?>,
                is_require: <?= $info['is_require'] ?>,
                name: "<?= $info['name'] ?>",
                label: "<?= $info['label'] ?>",
                showOption: <?php if($info['type']['value'] == 20 || $info['type']['value'] == 30):?> true <?php else:?> false <?php endif ?>,
                type_list: <?= json_encode($typeList) ?>,
                question_id: <?= $info['question_id'] ?>
            },
            methods: {
                changeType: function(e){
                    this.type = e.target.value
                    if(this.type == 20 || this.type == 30){
                        this.showOption = true
                    }else{
                        this.showOption = false
                    }
                },
                alertAddModel : function(){
                    this.showModel = true
                },
                addOption: function(){
                    if(!this.option_mark || !this.option_label){
                        layer.msg('请填写必填项', {label:1})
                    }else{
                        let option = {
                            mark: this.option_mark,
                            label: this.option_label,
                            is_input: this.option_is_input,
                            point: this.option_point
                        }
                        this.answer.push(option);
                        this.option_mark = this.option_label = '';
                        this.option_is_input = this.option_point = 0;
                        this.showModel = false;
                    }
                },
                cancelOption: function(){
                    this.showModel = false;
                },
                submit: function(){
                    let [name, label, is_require, type, question_id] = [this.name, this.label, this.is_require, this.type, this.question_id];
                    console.log(name, label, is_require, type)
                    if(!name || !label || !type){
                        layer.msg('请填写必填项', {label:1})
                        return false;
                    }
                    if((type == 20 || type == 30) && this.answer.length <= 1){
                        layer.msg('请添加至少两个答案选项', {label:1})
                        return false;
                    }
                    let answer = this.answer
                    let that = this;
                    $.post("<?= url('content.questionnaire.question/edit') ?>", {name,label,is_require,type,answer,question_id}, function(res){
                        if(res.code == 1){
                            layer.msg('操作成功', {label:1})
                        }else{
                            layer.msg(res.msg,{label:2})
                        }
                    }, 'json')
                },
                delOption: function(e, idx){
                    this.answer.splice(idx,1);
                }
            },
        })



    });
</script>
