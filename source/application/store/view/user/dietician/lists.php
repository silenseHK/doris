<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">营养师列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>营养师</th>
                                <th>管理团队数</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['store_user_id'] ?></td>
                                    <td class="am-text-middle">
                                        <p><?= $item['real_name']?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p><?= $item['team_member_num'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <a class="tpl-table-black-operation-default"
                                           href="<?= url('user.dietician/editTeam',['store_user_id'=>$item['store_user_id']])?>"
                                           title="库存明细"
                                        >
                                            编辑管理团队
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" class="am-text-center">暂无记录</td>
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

</script>

