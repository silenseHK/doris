<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">问答列表</div>
                </div>
                <div class="widget-body am-fr">

                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">

                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('content.online_questions/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success am-radius"
                                               href="<?= url('content.online_questions/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <select name="cate_id"
                                            data-am-selected="{btnSize: 'sm', placeholder: '分类'}">
                                        <option value=""></option>
                                        <option value="0"
                                            <?= $cate_id == '0' ? 'selected' : '' ?>>全部
                                        </option>
                                        <?php foreach($cate_list as $cate):?>
                                            <option value="<?= $cate['cate_id'] ?>"
                                                <?= $cate_id ==$cate['cate_id'] ? 'selected' : '' ?>><?= $cate['title'] ?>
                                            </option>
                                        <?php endforeach;?>
                                    </select>
                                </div>

                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
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
                                <th>问答ID</th>
                                <th>问题</th>
                                <th>问答分类</th>
                                <th>简答</th>
                                <th>阅读量</th>
                                <th>状态</th>
                                <th>添加时间</th>
                                <th>更新时间</th>
                                <th>排序</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['question_id'] ?></td>
                                    <td class="am-text-middle">
                                        <p class="item-title"
                                           style="max-width: 400px;"><?= $item['title'] ?></p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['cate']['title'] ?></td>
                                    <td class="am-text-middle"><?= $item['desc'] ?></td>
                                    <td class="am-text-middle"><?= $item['scan_times'] ?></td>
                                    <td class="am-text-middle">
                                           <span style="cursor: pointer;" data-id="<?= $item['question_id'] ?>" class="am-badge change-status am-badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                                               <?= $item['status'] ? '显示' : '隐藏' ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle"><?= $item['update_time'] ?></td>
                                    <td class="am-text-middle"><?= $item['sort'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('content.online_questions/edit')): ?>
                                                <a href="<?= url('content.online_questions/edit',
                                                    ['question_id' => $item['question_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('content.online_questions/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['question_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="am-text-center">暂无记录</td>
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
        var url = "<?= url('content.online_questions/delete') ?>";
        $('.item-delete').delete('question_id', url);

        $('.change-status').on('click', function(){
            let question_id = $(this).data('id');
            $.post("<?= url('content.online_questions/changeStatus') ?>",{question_id}, function(res){
                layer.msg(res.msg)
                if(res.code == 1){
                    setTimeout(function(){
                        location.reload()
                    }, 1000)
                }
            }, 'json')
        })

    });
</script>

