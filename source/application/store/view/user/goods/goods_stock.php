<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">库存明细</div>
                </div>
                <div class="widget-body am-fr">

                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>商品名</th>
                                <th>商品图片</th>
                                <th>当前库存</th>
                                <th>历史库存</th>
                                <th>历史出库</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['goods']['goods_name'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['goods']['image'][0]['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['goods']['image'][0]['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['stock'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['history_stock'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['history_sale'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <a class="tpl-table-black-operation-default"
                                           href="<?= url('user.goods/log',['user_id'=>$item['user_id'], 'goods_id'=>$item['goods_id']])?>"
                                           title="库存明细"
                                        >
                                            库存明细
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

    });
</script>

