<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">问卷列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">

                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('goods/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('content.questionnaire.question/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="am fr">
                                <div class="am-form-group am-fl">

                                    <?php $type = $request->get('type'); ?>
                                    <select name="type"
                                            data-am-selected="{btnSize: 'sm', placeholder: '类型'}">
                                        <option value="0" <?= 0 == $type ? 'selected' : '' ?>>全部</option>
                                        <?php foreach($typeList as $item):?>
                                        <option value="<?= $item['value'] ?>"
                                            <?= $item['value'] == $type ? 'selected' : '' ?> ><?= $item['text'] ?>
                                        </option>
                                        <?php endforeach; ?>
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
                                <th>ID</th>
                                <th>问题</th>
                                <th>name</th>
                                <th>类型</th>
                                <th>是否必填</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['question_id'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['label'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['name'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['type']['text'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['is_require'] == 1 ? "是" : "否" ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>

                                    <td class="am-text-middle">
                                        <li>
                                            <a class="am-dropdown-item" target=""
                                               href="<?= url('content.questionnaire.question/edit', ['question_id' => $item['question_id']]) ?>">编辑</a>
                                        </li>
                                        <li>
                                            <a data-id="<?= $item['question_id'] ?>" class="am-dropdown-item btn-del" target=""
                                               href="javascript:void(0);">删除</a>
                                        </li>
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

        $('.btn-del').on('click', function(e){
            let question_id = $(this).data('id');
            let index = layer.confirm('确定删除吗？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                layer.close(index);
                $.post("<?= url('content.questionnaire.question/del') ?>", {question_id}, function(res){
                    if(res.code == 0){
                        layer.msg(res.msg,{icon:2})
                        setTimeout(function(){
                            location.reload();
                        }, 1000)
                    }else{
                        layer.msg(res.msg,{icon:1})
                    }
                }, 'json')
            }, function(){
                layer.close(index)
            });
        })

    });
</script>

