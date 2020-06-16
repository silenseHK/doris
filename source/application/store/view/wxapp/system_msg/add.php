<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加系统消息</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 标题 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="title"
                                           value="" placeholder="请输入标题" required>
                                    <small>例如：平台新福利</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 简述 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="content"
                                           value="" placeholder="请输入简述" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 跳转链接 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="url"
                                           value="" placeholder="请输入跳转链接">
                                    <small>小程序页面链接,例如：pages/index/index</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 跳转参数 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="params"
                                           value="" placeholder="请输入跳转链接">
                                    <small>小程序页面参数,例如：goods_id=10&title=mark</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">详情 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="detail" type="text/plain"></textarea>
                                </div>
                            </div>

                            <div class="am-form-group switch-expire_type expire_type__20">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">生效时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="j-startTime am-form-field am-margin-bottom-sm"
                                           name="effect_time" placeholder="请选择生效时间" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary"> 提交
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

<script>
    /**
     * 时间选择
     */
    $(function () {
        var nowTemp = new Date();
        var nowDay = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0).valueOf();
        var nowMoth = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), 1, 0, 0, 0, 0).valueOf();
        var nowYear = new Date(nowTemp.getFullYear(), 0, 1, 0, 0, 0, 0).valueOf();
        var $startTime = $('.j-startTime');

        var checkin = $startTime.datepicker({
            onRender: function (date, viewMode) {
                // 默认 days 视图，与当前日期比较
                var viewDate = nowDay;
                switch (viewMode) {
                    // moths 视图，与当前月份比较
                    case 1:
                        viewDate = nowMoth;
                        break;
                    // years 视图，与当前年份比较
                    case 2:
                        viewDate = nowYear;
                        break;
                }
                return date.valueOf() < viewDate ? 'am-disabled' : '';
            }
        }).on('changeDate.datepicker.amui', function (ev) {
            checkin.close();
        }).data('amui.datepicker');
    });
</script>

<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>

<script>
    $(function () {

        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
