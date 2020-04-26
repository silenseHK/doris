<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<style>
    .am-form-label{
        color:#F37B1D !important;
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
                                <div class="widget-title am-fl">答卷详情</div>
                            </div>

                            <div class="am-form-group" v-for="(item,index) in questions">

                                <template v-if="item.question.type.value === 10">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.question.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <input type="text" class="tpl-form-input" :name="item.question.name"
                                               :value="item.answer" placeholder="请输入" required>
                                    </div>
                                </template>

                                <template v-if="item.question.type.value === 40">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.question.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <textarea class="" rows="5" id="doc-ta-1">{{item.answer}}</textarea>
                                    </div>
                                </template>

                                <template v-if="item.question.type.value === 20">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.question.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <label class="am-radio-inline" v-for="it in item.question.option">
                                            <input :checked="item.answer_mark.indexOf(it.mark) != -1" type="radio" :name="item.question.name" :value="item.question.name" data-am-ucheck>
                                            <span>{{it.mark}} . {{it.label}}  ({{it.point}}分)</span>
                                        </label>
                                    </div>
                                </template>

                                <template v-if="item.question.type.value === 30">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.question.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <template v-for="it in item.question.option">
                                            <label class="am-checkbox-inline" v-if="it.is_input == 0">
                                                <input :checked="item.answer_mark.indexOf(it.mark) != -1" type="checkbox" :name="item.question.name" :value="item.question.name" data-am-ucheck>
                                                <span>{{it.mark}} . {{it.label}}  ({{it.point}}分)</span>
                                            </label>
                                        </template>
                                        <template v-for="it in item.question.option">
                                            <label class="am-checkbox-inline" v-if="it.is_input == 1" style="width:300px;">
                                                <input :checked="item.answer_mark.indexOf(it.mark) != -1" type="checkbox" :name="item.question.name" :value="item.question.name" data-am-ucheck>
                                                <div>
                                                    <span style="">{{it.mark}} . {{it.label}}  ({{it.point}}分)</span>
                                                    <input style="width:100px;display: inline-block;" type="text" class="tpl-form-input" name="title"
                                                           :value="item.answer_mark.indexOf(it.mark) != -1 ? item.answer : ''" placeholder="请输入">
                                                </div>

                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </div>

                        </fieldset>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/vue.min.js"></script>

<script>
    var App;
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        App = new Vue({
            el : "#my-form",
            data : {
                title: '',
                questionnaire_no: '',
                status: 1,
                questions:<?= $data['user_answer'] ?>,
            },
            methods : {
                drag: function(){}
            }
        })

    });
</script>
