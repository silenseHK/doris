<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">讲师列表</div>
                </div>
                <div class="widget-body am-fr">

                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('college.lecturer/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('college.lecturer/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="keywords" placeholder="请输入讲师名称"
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
                                <th>讲师名称</th>
                                <th>头像</th>
                                <th>描述</th>
                                <th>标签</th>
                                <th>关注数</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['lecturer_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['name'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['image']['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['image']['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['desc'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php foreach($item['label_list'] as $label):?>
                                        <span class="am-badge am-badge-secondary"><?= $label ?></span>
                                        <?php endforeach;?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['notice_num'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['create_time'] ?>)
                                    </td>
                                    <td class="am-text-middle">

                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('college.lecturer/edit')): ?>
                                                <a href="<?= url('college.lecturer/edit',
                                                    ['lecturer_id' => $item['lecturer_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('college.lecturer/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['lecturer_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
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
        // 删除元素
        var url = "<?= url('college.lecturer/delete') ?>";
        $('.item-delete').delete('lecturer_id', url);

    });
</script>

