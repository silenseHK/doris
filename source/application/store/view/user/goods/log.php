<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">库存明细</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <?php $scene = $request->get('scene'); ?>
                                    <select name="scene"
                                            data-am-selected="{btnSize: 'sm', placeholder: '库存变动场景'}">
                                        <option value=""></option>
                                        <option value="-1"
                                            <?= $scene === '-1' ? 'selected' : '' ?>>全部
                                        </option>
                                        <?php foreach($sceneList as $k => $v){ ?>
                                            <option value="<?=$k?>" <?= $k==$scene? "selected" : "" ?> ><?= $v['text'] ?></option>
                                        <?php } ?>
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
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <div class="am-input-group-btn" style="width:auto;">

                                            <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>商品名</th>
                                <th>商品图片</th>
                                <th>变动库存</th>
                                <th>变动前库存</th>
                                <th>库存变动场景</th>
                                <th>收货人/出货人</th>
                                <th>描述/说明</th>
                                <th>变动时间</th>
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
                                        <p class=""><?= $item['change_num'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['balance_stock'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-secondary"><?= $item['change_type']['text'] ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= !$item['opposite_user']? '--' : ($item['opposite_user']['nickName'] ."[".$item['opposite_user']['grade']['name']) . "]" ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['remark'] ?: '--' ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
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

    });
</script>

