<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="tips am-margin-top-sm am-margin-bottom-sm">
                                <div class="pre">
                                    <p>
                                        模板消息仅用于微信小程序向用户发送服务通知，因微信限制，每笔支付订单可允许向用户在7天内推送最多3条模板消息。
                                        <a href="<?= url('store/setting.help/tplmsg') ?>" target="_blank">如何获取模板消息ID？</a>
                                    </p>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">提现申请结果通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[cash_result][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['cash_result']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[cash_result][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['cash_result']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[cash_result][template_id]"
                                           value="<?= $values['cash_result']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[cash_result][page]"
                                           value="<?= $values['cash_result']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">卖出成功通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[sale_success][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['sale_success']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[sale_success][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['sale_success']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[sale_success][template_id]"
                                           value="<?= $values['sale_success']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[sale_success][page]"
                                           value="<?= $values['sale_success']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">注册成功提醒</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[register_success][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['register_success']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[register_success][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['register_success']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[register_success][template_id]"
                                           value="<?= $values['register_success']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[register_success][page]"
                                           value="<?= $values['register_success']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">收益到账通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[rebate_income][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['rebate_income']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[rebate_income][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['rebate_income']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[rebate_income][template_id]"
                                           value="<?= $values['rebate_income']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[rebate_income][page]"
                                           value="<?= $values['rebate_income']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单发货通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[order_deliver][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['order_deliver']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[order_deliver][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['order_deliver']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[order_deliver][template_id]"
                                           value="<?= $values['order_deliver']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[order_deliver][page]"
                                           value="<?= $values['order_deliver']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">新订单提醒</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[goods_supply][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['goods_supply']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[goods_supply][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['goods_supply']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[goods_supply][template_id]"
                                           value="<?= $values['goods_supply']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[goods_supply][page]"
                                           value="<?= $values['goods_supply']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">奖励金提醒</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[manage_reward][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['manage_reward']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="subMsg[manage_reward][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['manage_reward']['is_enable'] == '0' ? 'checked' : '' ?>
                                        >
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[manage_reward][template_id]"
                                           value="<?= $values['manage_reward']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转页面
                                    <span class="tpl-form-line-small-title">page url</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="subMsg[manage_reward][page]"
                                           value="<?= $values['manage_reward']['page'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
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
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
