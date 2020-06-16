<?php

use app\common\enum\DeliveryType as DeliveryTypeEnum;

// 订单详情
$detail = isset($detail) ? $detail : null;

?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget__order-detail widget-body am-margin-bottom-lg">

                    <!-- 订单进度步骤条 -->
                    <div class="am-u-sm-12">
                        <?php
                        // 计算当前步骤位置
                        $progress = 1;
                        $detail['pay_status']['value'] == 20 && $progress += 1;
                        $detail['deliver_status']['value'] == 20 && $progress += 1;
                        $detail['deliver_status']['value'] == 40 && $progress += 1;
                        // $detail['order_status']['value'] == 30 && $progress += 1;
                        ?>
                        <ul class="order-detail-progress progress-<?= $progress ?>">
                            <li>
                                <span>下单时间</span>
                                <div class="tip"><?= $detail['create_time'] ?></div>
                            </li>
                            <li>
                                <span>付款</span>
                                <?php if ($detail['pay_status']['value'] == 20): ?>
                                    <div class="tip">
                                        付款于 <?= date('Y-m-d H:i:s', $detail['pay_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>发货</span>
                                <?php if ($detail['deliver_status']['value'] == 20): ?>
                                    <div class="tip">
                                        发货于 <?= date('Y-m-d H:i:s', $detail['deliver_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>已完成</span>
                                <?php if ($detail['deliver_status']['value'] == 40): ?>
                                    <div class="tip">
                                        完成于 <?= date('Y-m-d H:i:s', $detail['complete_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>提货人</th>
                                <th>运费</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                            </tr>
                            <tr>
                                <td><?= $detail['order_no'] ?></td>
                                <td>
                                    <p><?= $detail['user']['nickName'] ?></p>
                                    <p class="am-link-muted">(用户id：<?= $detail['user']['user_id'] ?>)</p>
                                    <p class="am-link-muted">(联系电话：<?= $detail['user']['mobile'] ?>)</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price am-text-left">
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">￥<?= $detail['freight_money'] ?> </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <span class="am-badge am-badge-secondary"><?= $detail['deliver_type']['text'] ?></span>
                                </td>
                                <td>
                                    <p>付款状态：
                                        <span class="am-badge
                                        <?= $detail['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                <?= $detail['pay_status']['text'] ?></span>
                                    </p>

                                    <p>订单状态：
                                        <span class="am-badge am-badge-warning"><?= $detail['deliver_status']['text'] ?></span>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 商品信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">商品信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>商品名称</th>
                                <th>商品编码</th>
                                <th>重量(Kg)</th>
                                <th>购买数量</th>
                            </tr>
                            <?php $goods = $detail['goods']; ?>
                                <tr>
                                    <td class="goods-detail am-text-middle" width="30%">
                                        <div class="goods-image">
                                            <img src="<?= $goods['image'][0]['file_path'] ?>" alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                        </div>
                                    </td>
                                    <td><?= $goods['specs'][0]['goods_no'] ?: '--' ?></td>
                                    <td><?= $goods['specs'][0]['goods_weight'] ?: '--' ?></td>
                                    <td>×<?= $detail['goods_num'] ?></td>
                                </tr>
                            <tr>
                                <td colspan="6" class="am-text-right am-cf">
                                    <span class="am-fl">备注：<?= $detail['remark'] ?: '无' ?></span>
                                    <span class="am-fr">总计金额：￥<?= $detail['freight_money'] ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 收货信息 -->
                    <?php if ($detail['deliver_type']['value'] == DeliveryTypeEnum::EXPRESS): ?>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">收货信息</div>
                        </div>
                        <div class="am-scrollable-horizontal">
                            <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                                <tbody>
                                <tr>
                                    <th>收货人</th>
                                    <th>收货电话</th>
                                    <th>收货地址</th>
                                </tr>
                                <tr>
                                    <td><?= $detail['receiver_user'] ?></td>
                                    <td><?= $detail['receiver_mobile'] ?></td>
                                    <td>
                                        <?= $detail['address'] ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- 自提门店信息 -->
                    <?php if ($detail['deliver_type']['value'] == DeliveryTypeEnum::EXTRACT): ?>

                    <?php endif; ?>

                    <!-- 发货信息 -->
                    <?php if (
                        $detail['pay_status']['value'] == 20    // 支付状态：已支付
                        && $detail['deliver_type']['value'] == DeliveryTypeEnum::EXPRESS
                    ): ?>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">发货信息</div>
                        </div>
                        <?php if ($detail['deliver_status']['value'] == 10): ?>
                            <?php if (checkPrivilege('order/delivery')): ?>
                                <!-- 去发货 -->
                                <form id="delivery" class="my-form am-form tpl-form-line-form" method="post"
                                      action="<?= url('order/deliverOrderDeliver', ['deliver_id' => $detail['deliver_id']]) ?>">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流公司 </label>
                                        <div class="am-u-sm-9 am-u-end am-padding-top-xs">
                                            <select name="order[express_id]"
                                                    data-am-selected="{btnSize: 'sm', maxHeight: 240}" required>
                                                <option value=""></option>
                                                <?php if (isset($expressList)): foreach ($expressList as $expres): ?>
                                                    <option value="<?= $expres['express_id'] ?>">
                                                        <?= $expres['express_name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                            <div class="help-block am-margin-top-xs">
                                                <small>可在 <a href="<?= url('setting.express/index') ?>" target="_blank">物流公司列表</a>
                                                    中设置
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流单号 </label>
                                        <div class="am-u-sm-9 am-u-end">
                                            <input type="text" class="tpl-form-input" name="order[express_no]" required>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label">备注 </label>
                                        <div class="am-u-sm-9 am-u-end">
                                            <textarea name="order[express_remark]" class="" rows="4" id="doc-ta-1"></textarea>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                            <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">
                                                确认发货
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php elseif($detail['deliver_status']['value'] == 20 || $detail['deliver_status']['value'] == 40): ?>
                            <div class="am-scrollable-horizontal">
                                <table class="regional-table am-table am-table-bordered am-table-centered
                                    am-text-nowrap am-margin-bottom-xs">
                                    <tbody>
                                    <tr>
                                        <th>物流公司</th>
                                        <th>物流单号</th>
                                        <th>发货状态</th>
                                        <th>备注</th>
                                        <th>发货时间</th>
                                    </tr>
                                    <tr>
                                        <td><?= $detail['express']['express_name'] ?></td>
                                        <td><?= $detail['express_no'] ?></td>
                                        <td>
                                             <span class="am-badge
                                            <?= $detail['deliver_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                    <?= $detail['deliver_status']['text'] ?></span>
                                        </td>
                                        <td><?= $detail['express_remark'] ?></td>
                                        <td>
                                            <?= date('Y-m-d H:i:s', $detail['deliver_time']) ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 自提核销 -->
                    <?php if (
                        $detail['pay_status']['value'] == 20    // 支付状态：已支付
                        && $detail['deliver_type']['value'] == DeliveryTypeEnum::EXTRACT
                    ): ?>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">自提核销</div>
                        </div>
                        <?php if ($detail['deliver_status']['value'] == 20): ?>
                            <?php if (checkPrivilege('order/submitselforder')): ?>
                                <form id="delivery" class="my-form am-form tpl-form-line-form" method="post"
                                      action="<?= url('order/submitselforder', ['deliver_id' => $detail['deliver_id']]) ?>">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">买家取货状态 </label>
                                        <div class="am-u-sm-9 am-u-end">
                                            <label class="am-radio-inline">
                                                <input type="radio" name="order[extract_status]" value="1"
                                                       checked data-am-ucheck required>
                                                已取货
                                            </label>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                            <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">
                                                确认核销
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php elseif($detail['deliver_status']['value'] == 40): ?>
                            <div class="am-scrollable-horizontal">
                                <table class="regional-table am-table am-table-bordered am-table-centered
                                    am-text-nowrap am-margin-bottom-xs">
                                    <tbody>
                                    <tr>
                                        <th>核销时间</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?= date('Y-m-d H:i:s', $detail['complete_time']) ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
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
        $('.my-form').superForm();

    });
</script>
