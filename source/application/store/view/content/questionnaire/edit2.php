<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">问卷信息</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 标题 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" v-model="title" name="title"
                                           value="" placeholder="请输入标题" required>
                                    <small>例如：新用户入门评测</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 编号 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" v-model="questionnaire_no" name="questionnaire_no"
                                           value="" placeholder="请输入编号" required>
                                    <small>例如：20200201</small>
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
                                        <input type="radio" name="status" value="2" data-am-ucheck v-model="status">
                                        <span>下线</span>
                                    </label>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">问卷问题</div>
                            </div>
                            <draggable  v-model="questions" @end="drag" :options="{delay:30,touchStartThreshold: 1,preventOnFilter: false,animation:300,chosenClass:'sortable-chosen',forceFallback:true,fallbackOnBody:false,scroll:true,scrollSensitivity:120,filter: '.undraggable'}">

                            <div class="am-form-group" v-for="(item,index) in questions">

                                <template v-if="item.type.value === 10">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <input type="text" class="tpl-form-input" :name="item.name"
                                               value="" placeholder="请输入" required>
                                    </div>
                                    <i style="cursor: pointer" @click="delOption($event, index)" class="am-icon-ban am-icon-fw"></i>
                                </template>

                                <template v-if="item.type.value === 40">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <textarea class="" rows="5" id="doc-ta-1"></textarea>
                                    </div>
                                    <i style="cursor: pointer" @click="delOption($event, index)" class="am-icon-ban am-icon-fw"></i>
                                </template>

                                <template v-if="item.type.value === 20">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <label class="am-radio-inline" v-for="it in item.option">
                                            <input type="radio" :name="item.name" :value="item.name" data-am-ucheck>
                                            <span>{{it.mark}} . {{it.label}}  ({{it.point}}分)</span>
                                        </label>
                                    </div>
                                    <i style="cursor: pointer" @click="delOption($event, index)" class="am-icon-ban am-icon-fw"></i>
                                </template>

                                <template v-if="item.type.value === 30">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">{{item.label}} </label>
                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                        <template v-for="it in item.option">
                                            <label class="am-checkbox-inline" v-if="it.is_input == 0">
                                                <input type="checkbox" :name="item.name" :value="item.name" data-am-ucheck>
                                                <span>{{it.mark}} . {{it.label}}  ({{it.point}}分)</span>
                                            </label>
                                        </template>
                                        <template v-for="it in item.option">
                                            <label class="am-checkbox-inline" v-if="it.is_input == 1" style="width:310px;">
                                                <input type="checkbox" :name="item.name" :value="item.name" data-am-ucheck>
                                                <div>
                                                    <span style="">{{it.mark}} . {{it.label}}  ({{it.point}}分)</span>
                                                    <input style="width:100px;display: inline-block;" type="text" class="tpl-form-input" name="title"
                                                           value="" placeholder="请输入">
                                                </div>

                                            </label>
                                        </template>
                                    </div>
                                    <i style="cursor: pointer" @click="delOption($event, index)" class="am-icon-ban am-icon-fw"></i>
                                </template>
                            </div>

                            </draggable>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">  </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <button type="button" @click="chooseQuestion" class="am-btn am-btn-success am-btn-xs">添加问题</button>
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

<script src="assets/common/js/vue.min.js"></script>
<script src="assets/common/js/Sortable.min.js?v=<?= $version ?>"></script>
<script src="assets/common/js/vuedraggable.min.js?v=<?= $version ?>"></script>

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
                title: "<?= $info['title'] ?>",
                questionnaire_no: "<?= $info['questionnaire_no'] ?>",
                status: <?= $info['status'] ?>,
                questions:<?= $info['questions'] ?>,
                questionnaire_id: <?= $info['questionnaire_id'] ?>
            },
            methods : {
                drag:function(e){
                  console.log(this.questions);
                },
                chooseQuestion : function(){
                    layer.open({
                        type: 2,
                        area: ['900px', '800px'],
                        fixed: false, //不固定
                        maxmin: true,
                        content: "<?= url('content.questionnaire.question/questions') ?>"
                    });
                },
                delOption: function(e, index){
                    this.questions.splice(index,1)
                },
                submit: function(){
                    let [title, status, questions, questionnaire_id, questionnaire_no] = [this.title, this.status, this.questions, this.questionnaire_id,this.questionnaire_no]
                    if(!title || !status || !questionnaire_no || questions.length <= 0){
                        layer.msg('请将数据补充完整', {icon:2})
                        return false;
                    }
                    let question_ids = [];
                    questions.forEach(function(value, key){
                        question_ids.push(value.question_id)
                    })
                    $.post("<?= url('content.questionnaire/edit') ?>", {title, questionnaire_no, status, question_ids, questionnaire_id}, function(res){
                        if(res.code == 1){
                            layer.msg('操作成功',{icon:1})
                        }else{
                            layer.msg(res.msg, {icon:2})
                        }
                    }, 'json')
                }
            }
        })

    });
</script>
