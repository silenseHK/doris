<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">填写用户列表</div>
                </div>
                <div class="widget-body am-fl" style="width:50%;">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <div style="width:250px;" class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="keywords" placeholder="请输入用户电话"
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
                                <th>用户名/ID</th>
                                <th>头像</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle">
                                        <?= $item['nickName'] . "/" . $item['user_id']?>
                                    </td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <a class="tpl-table-black-operation-default"
                                               href="<?= url('content.questionnaire/fillList', ['user_id'=>$item['user_id'], 'questionnaire_id'=>$item['questionnaire_id']]) ?>"
                                               title="答卷列表"
                                            >
                                                <i class="iconfont am-icon-edit">答卷列表</i>

                                            </a>
                                        </div>
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

