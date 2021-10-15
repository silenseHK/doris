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
                                        <?php $orderStatus = $request->get('order_status'); ?>
                                        <select name="order_status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '订单状态'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $orderStatus === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="10"
                                                <?= 10 == $orderStatus ? 'selected' : '' ?>>未付款
                                            </option>
                                            <option value="20"
                                                <?= 20 == $orderStatus ? 'selected' : '' ?>>已付款
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

                                    <div class="am-form-group am-fl">
                                        <?php $searchType = $request->get('search_type'); ?>
                                        <select name="search_type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '关键字搜索类型'}">
                                            <option <?=$searchType==10?"selected":""?> value="10">订单号</option>
                                            <option <?=$searchType==20?"selected":""?> value="20">发货人</option>
                                            <option <?=$searchType==30?"selected":""?> value="30">进货人</option>
                                        </select>
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
                                <th width="20%" class="goods-detail">商品信息</th>
                                <th width="10%">单价/数量</th>
                                <th width="10%">实付款</th>
                                <th>进货</th>
                                <th>出货</th>
                                <th>返利</th>
                                <th>付款状态</th>
                                <th>支付方式</th>
<!--                                <th>操作</th>-->
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
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['user']['nickName'] ?></p>
                                                <p><?= $order['user_grade']['name'] ?></p>
                                                <p class="am-link-muted">(用户id：<?= $order['user_id'] ?>)</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <?php if($order['supply_user_id'] > 0):?>
                                                    <p><?= $order['supply_user']['nickName'] ?></p>
                                                    <p><?= $order['supply_grade']['name'] ?></p>
                                                    <p class="am-link-muted">(用户id：<?= $order['supply_user']['user_id'] ?>)</p>
                                                <?php else:?>
                                                    平台
                                                <?php endif;?>
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
                                                    <?= $order['pay_status']['text'] ?>
                                                </span>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="am-badge am-badge-secondary">
                                                    <?= $order['pay_type']['text'] ?>
                                                </span><br />
                                                <?php if($order['transaction_id']):?>
                                                <span class="am-badge am-badge-secondary">
                                                    <?= $order['transaction_id'] ?>
                                                </span>
                                                <?php endif?>
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

