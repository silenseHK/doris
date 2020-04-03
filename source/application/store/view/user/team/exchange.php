<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">转换团队</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">

                    </div>

                    <form id="my-form" class="am-form tpl-form-line-form" method="post">
                        <div class="widget-body">
                            <fieldset>

                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 需转换团队用户id </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" name="user_id"
                                               placeholder="请输入需转换团队用户id" required>
                                    </div>
                                </div>

                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 新的上级id </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" name="exchange_user_id"
                                               placeholder="请输入新的上级id" required>
                                    </div>
                                </div>

                                <div class="am-form-group">
                                    <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                        <button type="submit" class="j-submit am-btn am-btn-secondary">提交
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
</div>

<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>

<script src="assets/common/js/ddsort.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script src="assets/common/js/vue.min.js"></script>

<script>

    $(function(){

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    })

</script>


