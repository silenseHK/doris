<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">问题分类列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-form-group">
                            <?php if (checkPrivilege('matter/addCate')): ?>
                                <div class="am-btn-group am-btn-group-xs">
                                    <a class="am-btn am-btn-default am-btn-success"
                                       href="<?= url('addCate') ?>">
                                        <span class="am-icon-plus"></span> 新增
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>分类ID</th>
                                <th>分类名</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$lists->isEmpty()): foreach ($lists as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['title'] ?></td>
                                    <td class="am-text-middle"><?= $item['status']['title'] ?></td>

                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('project.matter/editCate')): ?>
                                                <a href="<?= url('project.matter/editCate', ['id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('project.matter/deleteCate')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php if ($item): foreach ($item['children'] as $child): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $child['id'] ?></td>
                                        <td class="am-text-middle"><?= 'LL_' . $child['title'] ?></td>
                                        <td class="am-text-middle"><?= $child['status']['title'] ?></td>

                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('project.matter/editCate')): ?>
                                                    <a href="<?= url('project.matter/editCate', ['id' => $child['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('project.matter/deleteCate')): ?>
                                                    <a href="javascript:void(0);"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $child['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if ($child): foreach ($child['children'] as $ch): ?>
                                        <tr>
                                            <td class="am-text-middle"><?= $ch['id'] ?></td>
                                            <td class="am-text-middle"><?= 'LL_LL_' .$ch['title'] ?></td>
                                            <td class="am-text-middle"><?= $ch['status']['title'] ?></td>

                                            <td class="am-text-middle">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('project.matter/editCate')): ?>
                                                        <a href="<?= url('project.matter/editCate', ['id' => $ch['id']]) ?>">
                                                            <i class="am-icon-pencil"></i> 编辑
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('project.matter/deleteCate')): ?>
                                                        <a href="javascript:void(0);"
                                                           class="item-delete tpl-table-black-operation-del"
                                                           data-id="<?= $ch['id'] ?>">
                                                            <i class="am-icon-trash"></i> 删除
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif;?>
                            <?php endforeach; endif;?>
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

        // 删除元素
        var url = "<?= url('project.matter/deleteCate') ?>";
        $('.item-delete').delete('id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

