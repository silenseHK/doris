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
                            <input type="hidden" name="dataType" value="<?= $dataType ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('order.operate/export')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>订单导出
                                                </a>
                                            <?php endif; ?>
                                            <?php /* if (checkPrivilege('order.operate/batchdelivery')): ?>
                                                <?php if (in_array($dataType, ['all', 'delivery'])): ?>
                                                    <a class="j-export am-btn am-btn-secondary am-radius"
                                                       href="<?= url('order.operate/batchdelivery') ?>">
                                                        <i class="iconfont icon-daoru am-margin-right-xs"></i>批量发货
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; */ ?>
                                        </div>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('order.operate/deliveryexport')): ?>
                                                <a class="j-delivery-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>发货订单导出
                                                </a>
                                            <?php endif; ?>
                                            <?php /* if (checkPrivilege('order.operate/batchdelivery')): ?>
                                                <?php if (in_array($dataType, ['all', 'delivery'])): ?>
                                                    <a class="j-export am-btn am-btn-secondary am-radius"
                                                       href="<?= url('order.operate/batchdelivery') ?>">
                                                        <i class="iconfont icon-daoru am-margin-right-xs"></i>批量发货
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; */ ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $deliveryType = $request->get('delivery_type'); ?>
                                        <select name="delivery_type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '配送方式'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $deliveryType === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach (DeliveryTypeEnum::data() as $item): ?>
                                                <option value="<?= $item['value'] ?>"
                                                    <?= $item['value'] == $deliveryType ? 'selected' : '' ?>><?= $item['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '自提门店名称'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractShopId === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"
                                                    <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               autocomplete="off"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               autocomplete="off"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入订单号/用户昵称" value="<?= $request->get('search') ?>">
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
                                <th width="10%">单价/数量</th>
                                <th width="15%">实付款</th>
                                <th>买家</th>
                                <th>出货方</th>
                                <th>返利</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $colspan = 9; ?>
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
                                <?php $i = 0;
                                foreach ($order['goods'] as $goods): $i++; ?>
                                    <tr>
                                        <td class="goods-detail am-text-middle">
                                            <div class="goods-image">
                                                <img src="<?= $goods['image']['file_path'] ?>" alt="">
                                            </div>
                                            <div class="goods-info">
                                                <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                                <p class="goods-spec am-link-muted"><?= $goods['goods_attr'] ?></p>
                                            </div>
                                        </td>
                                        <td class="am-text-middle">
                                            <p>￥<?= $goods['goods_price'] ?></p>
                                            <p>×<?= $goods['total_num'] ?></p>
                                        </td>
                                        <?php if ($i === 1) : $goodsCount = count($order['goods']); ?>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>￥<?= $order['pay_price'] ?></p>
                                                <p class="am-link-muted">(含运费：￥<?= $order['express_price'] ?>)</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['user']['nickName'] ?></p>
                                                <p class="am-link-muted">(用户id：<?= $order['user']['user_id'] ?>)</p>
                                                <p class="am-link-muted">(用户等级：<?= $order['user_grade']['name'] ?>)</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <?php if($order['supply_user_id'] > 0):?>
                                                    <p><?= $order['supplyUser']['nickName'] ?></p>
                                                    <p class="am-link-muted">(用户id：<?= $order['supplyUser']['user_id'] ?>)</p>
                                                    <p class="am-link-muted">(用户等级：<?= $order['supply_grade']['name'] ?>)</p>
                                                <?php else:?>
                                                    平台
                                                <?php endif?>
                                            </td>

                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <?php if(!empty($order['rebate_info'])):?>
                                                    <p><?= $order['rebate_money']?></p>
                                                    <span class="am-badge am-badge-secondary"
                                                          style="cursor: pointer",
                                                          data-arr='<?= json_encode($order['rebate_info']); ?>'
                                                          onclick="showRebateDetail.call(this)"
                                                    >
                                                        查看详情
                                                    </span>
                                                <?php else:?>
                                                    无返利
                                                <?php endif;?>
                                            </td>

                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="am-badge am-badge-secondary">
                                                    <?= $order['pay_type']['text'] ?>
                                                </span>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="am-badge am-badge-secondary">
                                                    <?= $order['delivery_type']['text'] ?>
                                                </span>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>付款状态：
                                                    <span class="am-badge
                                                <?= $order['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['pay_status']['text'] ?></span>
                                                </p>
                                                <p>发货状态：
                                                    <span class="am-badge
                                                <?= $order['delivery_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['delivery_status']['text'] ?></span>
                                                </p>
                                                <p>收货状态：
                                                    <span class="am-badge
                                                <?= $order['receipt_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['receipt_status']['text'] ?></span>
                                                </p>
                                                <?php if ($order['order_status']['value'] == 20 || $order['order_status']['value'] == 21): ?>
                                                    <p>订单状态：
                                                        <span class="am-badge am-badge-warning"><?= $order['order_status']['text'] ?></span>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($order['order_status']['value'] == 40): ?>
                                                    <p>订单状态：
                                                        <span class="am-badge am-badge-warning"><?= $order['order_status']['text'] ?></span>
                                                    </p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('order/detail')): ?>
                                                        <a class="tpl-table-black-operation-green"
                                                           href="<?= url('order/detail', ['order_id' => $order['order_id']]) ?>">
                                                            订单详情</a>
                                                    <?php endif; ?>
                                                    <?php if($order['delivery_type']['value'] != 30): ?>

                                                        <?php if (checkPrivilege(['order/detail', 'order/delivery'])): ?>
                                                            <?php if ($order['pay_status']['value'] == 20
                                                                && $order['delivery_status']['value'] == 10
                                                                && $order['order_status']['value'] != 20
                                                                && $order['order_status']['value'] != 21
                                                                && $order['order_status']['value'] != 40
                                                            ): ?>
                                                                <a class="tpl-table-black-operation"
                                                                   href="<?= url('order/detail#delivery',
                                                                       ['order_id' => $order['order_id']]) ?>">去发货</a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if (checkPrivilege(['order/detail', 'order.operate/confirmcancel'])): ?>
                                                            <?php if ($order['order_status']['value'] == 21): ?>
                                                                <a class="tpl-table-black-operation-del"
                                                                   href="<?= url('order/detail#cancel',
                                                                       ['order_id' => $order['order_id']]) ?>">去审核</a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if (checkPrivilege('store/order/refund')): ?>
                                                            <?php if ($order['pay_status']['value'] == 20
                                                                && $order['order_status']['value'] != 20
                                                                && $order['order_status']['value'] != 21
                                                                && $order['order_status']['value'] != 40
                                                            ): ?>
                                                                <a data-id="<?= $order['order_id'] ?>" class="tpl-table-black-operation-del btn-request-refund"
                                                                   href="javascript:void(0);">退款</a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
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

<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <table class="table table-border table-bordered table-hover" border="1" style="width:100%">
                        <thead>
                        <tr>
                            <th style="text-align: center" width="20%">用户名</th>
                            <th style="text-align: center" width="10%">用户id</th>
                            <th style="text-align: center" width="20%">返利金额</th>
                            <th style="text-align: center" width="30%">备注</th>
                            <th style="text-align: center" width="20%">等级</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{ each $data }}
                        <tr class="active">
                            <td align="center" valign="middle">{{ $value['user']['nickName'] }}</td>
                            <td align="center" valign="middle">{{ $value['user']['user_id'] }}</td>
                            <td align="center" valign="middle">{{ $value['money'] }}</td>
                            <td align="center" valign="middle">{{ $value['remark'] }}</td>
                            <td align="center" valign="middle">{{ $value['grade'] }}</td>
                        </tr>
                        {{/each}}
                        </tbody>
                    </table>

                </div>
            </div>
        </form>
    </div>
</script>

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
            window.location = "<?= url('order.operate/export') ?>" + '&' + $.urlEncode(data);
        });

        /**
         * 发货订单导出
         */
        $('.j-delivery-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('order.operate/deliveryexport') ?>" + '&' + $.urlEncode(data);
        });

        /**
         * 退款
         */
        $('.btn-request-refund').click(function(){
            let order_id = $(this).data('id');
            layer.prompt({title: '退款理由', formType: 2}, function(pass, index){
                pass = $.trim(pass);
                layer.close(index);
                if(!pass){
                    layer.msg('请填写退款理由');
                    return false;
                }
                $.post("<?= url("store/order/refund") ?>", {order_id}, function(res){
                    layer.msg(res.msg);
                    if(res.code == 1){
                        setTimeout(function(){
                            location.reload()
                        }, 1000);
                    }
                }, 'json')
            });
        })

    });

    function showRebateDetail(){
        var arr = $(this).data('arr');
        $.showModal({
            title: '返利详情'
            , area: '600px'
            , content: template('tpl-grade', arr)
            , uCheck: true
            , btn: ['确定']
            , success: function ($content) {

            }
            , yes:function($content){
                return true;
            }
        });
    }

</script>

