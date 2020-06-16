<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">成员列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <?php $scene = $request->get('grade_id'); ?>
                                    <select name="grade_id"
                                            data-am-selected="{btnSize: 'sm', placeholder: '等级'}">
                                        <option value=""></option>
                                        <option value="0"
                                            <?= $scene === '0' ? 'selected' : '' ?>>全部
                                        </option>
                                        <?php foreach ($grade_list as $attr): ?>
                                            <option value="<?= $attr['grade_id'] ?>"
                                                <?= $scene === (int)$attr['grade_id'] ? 'selected' : '' ?>>
                                                <?= $attr['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="keywords" placeholder="请输入用户昵称"
                                               value="<?= $request->get('keywords') ?>">
                                        <div class="am-input-group-btn">
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
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>代理等级</th>
                                <th>电话</th>
                                <th>邀请人</th>
                                <th>创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['user_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['nickName'] ?></p>
                                        <p class="am-link-muted">(用户ID：<?= $item['user_id'] ?>)</p>
                                    </td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-secondary"><?= $item['grade']['name'] ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['mobile_hide'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if(empty($item['invitation_user'])):?>
                                            无
                                        <?php else:?>
                                            <?= $item['invitation_user']['nickName'] ?>(id:<?= $item['invitation_user']['user_id'] ?>)
                                        <?php endif?>
                                    </td>
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

