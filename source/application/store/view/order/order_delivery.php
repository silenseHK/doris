<?php

use app\common\enum\DeliveryType as DeliveryTypeEnum;

?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf"><?= $title ?></div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('order.delivery/export')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>订单导出
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $deliveryType = $request->get('deliver_type'); ?>
                                        <select name="deliver_type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '配送方式'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $deliveryType === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach ($deliveryTypeList as $key => $item): ?>
                                                <option value="<?= $item['value'] ?>"
                                                    <?= $item['value'] == $deliveryType ? 'selected' : '' ?>><?= $item['text'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <?php $deliveryStatus = $request->get('deliver_status'); ?>
                                        <select name="deliver_status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '配送状态'}">
                                            <option value=""></option>
                                            <option value="0"
                                                <?= $deliveryStatus === '0' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="10"
                                                <?= $deliveryStatus === '10' ? 'selected' : '' ?>>待发货
                                            </option>
                                            <option value="20"
                                                <?= $deliveryStatus === '20' ? 'selected' : '' ?>>已发货
                                            </option>
                                            <option value="30"
                                                <?= $deliveryStatus === '30' ? 'selected' : '' ?>>已取消
                                            </option>
                                            <option value="40"
                                                <?= $deliveryStatus === '40' ? 'selected' : '' ?>>已完成
                                            </option>
                                        </select>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl" style="width: 200px;">
                                        <input type="text" class="am-form-field" name="order_no"
                                               placeholder="请输入订单号" value="<?= $request->get('order_no') ?>">
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="keywords"
                                                   placeholder="请输入用户电话/用户昵称" value="<?= $request->get('keywords') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order-list am-scrollable-horizontal am-u-sm-12 am-margin-top-xs">
                        <table width="100%" class="am-table am-table-centered
                        am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th width="25%" class="goods-detail">商品信息</th>
                                <th width="10%">数量</th>
                                <th width="15%">实付款</th>
                                <th>发货人</th>
                                <th>收货人</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $colspan = 8; ?>
                            <?php if (!$list->isEmpty()): foreach ($list as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="<?= $colspan ?>"></td>
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="<?= $colspan ?>">
                                        <span class="am-margin-right-lg"> <?= $order['create_time'] ?></span>
                                        <span class="am-margin-right-lg">订单号：<?= $order['order_no'] ?></span>
                                    </td>
                                </tr>
                                <?php $goods = $order['goods'] ?>
                                    <tr>
                                        <td class="goods-detail am-text-middle">
                                            <div class="goods-image">
                                                <img src="<?= $goods['image'][0]['file_path'] ?>" alt="">
                                            </div>
                                            <div class="goods-info">
                                                <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                            </div>
                                        </td>
                                        <td class="am-text-middle">
                                            <p>×<?= $order['goods_num'] ?></p>
                                        </td>

                                            <td class="am-text-middle" rowspan="">
                                                <p class="am-link-muted">运费：￥<?= $order['freight_money'] ?></p>
                                            </td>
                                            <td class="am-text-middle" rowspan="">
                                                <p><?= $order['nickName'] ?></p>
                                                <p><?= $order['mobile'] ?></p>
                                                <p class="am-link-muted">(用户id：<?= $order['user_id'] ?>)</p>
                                            </td>

                                        <td class="am-text-middle" rowspan="">
                                            <?php if($order['deliver_type']['value'] == 10): ?>
                                                <p><?= $order['receiver_user'] ?></p>
                                                <p><?= $order['receiver_mobile'] ?></p>
                                                <p><?= $order['address'] ?></p>
                                            <?php else: ?>
                                                <p class="am-link-muted">用户自提</p>
                                            <?php endif ?>
                                        </td>

                                            <td class="am-text-middle" rowspan="">
                                                <span class="am-badge am-badge-secondary">
                                                    <?= $order['deliver_type']['text'] ?>
                                                </span>
                                            </td>
                                            <td class="am-text-middle" rowspan="">
                                                <p>付款状态：
                                                    <span class="am-badge
                                                <?= $order['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['pay_status']['text'] ?></span>
                                                </p>
                                                <p>订单状态：
                                                    <span class="am-badge
                                                <?= $order['deliver_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['deliver_status']['text'] ?></span>
                                                </p>
                                            </td>
                                            <td class="am-text-middle" rowspan="">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('order/detail')): ?>
                                                        <a class="tpl-table-black-operation-green"
                                                           href="<?= url('order/deliveryDetail', ['order_id' => $order['deliver_id']]) ?>">
                                                            订单详情</a>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege(['order/detail', 'order/delivery'])): ?>
                                                        <?php if ($order['pay_status']['value'] == 20
                                                            && $order['deliver_status']['value'] == 10
                                                            && $order['deliver_type']['value'] == 10
                                                        ): ?>
                                                            <a class="tpl-table-black-operation"
                                                               href="<?= url('order/deliveryDetail#delivery',
                                                                   ['order_id' => $order['deliver_id']]) ?>"> 去发货 </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege(['order/detail', 'order/delivery'])): ?>
                                                        <?php if ($order['pay_status']['value'] == 20
                                                            && $order['deliver_status']['value'] == 10
                                                            && $order['deliver_type']['value'] == 20
                                                        ): ?>
                                                            <a style="cursor: pointer" javascript:void(0); class="tpl-table-black-operation"
                                                               onclick="submitSelfOrder(<?= $order['deliver_id'] ?>)"
                                                               > 确认提货 </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege(['order/detail', 'order/delivery'])): ?>
                                                        <?php if ($order['pay_status']['value'] == 20
                                                            && $order['deliver_status']['value'] == 10
                                                        ): ?>
                                                            <a style="cursor: pointer" javascript:void(0); class="tpl-table-black-operation"
                                                               onclick="cancelOrder(<?= $order['deliver_id'] ?>)"
                                                            > <?= $order['deliver_type']['value'] == 10 ? "取消发货" : "取消提货" ?> </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                    </tr>

                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="<?= $colspan ?>" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    $(function () {

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('order.delivery/export') ?>" + '&' + $.urlEncode(data);
        });



    });

    /**
     * 确认已提货
     * @param deliver_id
     */
    function submitSelfOrder(deliver_id){
        var idx = layer.confirm('确认客户已提货?', {
            btn: ['确定','取消'] //按钮
        }, function(){
            layer.close(idx);
            $.post("<?= url('order/submitSelfOrder') ?>", {deliver_id}, function(res){
                layer.msg(res.msg);
                setTimeout(function(){
                    location.reload();
                }, 1500)
            }, 'json')
        }, function(){
            layer.close(idx);
        });
    }

    /**
     * 确认取消订单
     * @param deliver_id
     */
    function cancelOrder(deliver_id){
        var idx = layer.confirm('确认取消发货/自提?', {
            btn: ['确定','取消'] //按钮
        }, function(){
            layer.close(idx);
            $.post("<?= url('order/cancelOrder') ?>", {deliver_id}, function(res){
                layer.msg(res.msg);
                if(res.code==1){
                    setTimeout(function(){
                        location.reload();
                    }, 1500)
                }
            }, 'json')
        }, function(){
            layer.close(idx);
        });
    }

</script>

