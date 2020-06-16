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
                                               href="<?= url('content.questionnaire/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <?php $type = $request->get('status'); ?>
                                    <select name="status"
                                            data-am-selected="{btnSize: 'sm', placeholder: '是否生效'}">
                                        <option value=""></option>
                                        <option value="0"
                                            <?= $type === '0' ? 'selected' : '' ?>>全部
                                        </option>
                                        <option value="1"
                                            <?= $type === '1' ? 'selected' : '' ?>>上线
                                        </option>
                                        <option value="2"
                                            <?= $type === '2' ? 'selected' : '' ?>>下线
                                        </option>
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
                                <th>问卷标题</th>
                                <th>编号</th>
                                <th>提交问卷数</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['questionnaire_id'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['title'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['questionnaire_no'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['fill_num'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['status'] == 1? "上线" : "下线" ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('content.questionnaire/edit')): ?>
                                            <a class="tpl-table-black-operation-default"
                                               href="<?= url('content.questionnaire/edit', ['questionnaire_id'=>$item['questionnaire_id']]) ?>"
                                               title="编辑"
                                            >
                                                <i class="iconfont am-icon-edit"></i>
                                                编辑
                                            </a>
                                            <?php endif;?>
                                            <?php if (checkPrivilege('content.questionnaire/userfilllist')): ?>
                                            <a class="tpl-table-black-operation-default"
                                               href="<?= url('content.questionnaire/userFillList', ['questionnaire_id'=>$item['questionnaire_id']]) ?>"
                                               title="答卷"
                                            >
                                                <i class="iconfont am-icon-newspaper-o"></i>
                                                答卷
                                            </a>
                                            <?php endif;?>
                                            <?php if (checkPrivilege('content.questionnaire/del')): ?>
                                            <a class="tpl-table-black-operation-default btn-del"
                                               data-id="<?= $item['questionnaire_id'] ?>"
                                               href="javascript:void(0);"
                                               title="删除"
                                            >
                                                <i class="iconfont am-icon-close"></i>
                                                删除
                                            </a>
                                            <?php endif;?>
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

        $('.btn-del').on('click', function(e){
            let questionnaire_id = $(this).data('id');
            let index = layer.confirm('确定删除吗？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                layer.close(index);
                $.post("<?= url('content.questionnaire/del') ?>", {questionnaire_id}, function(res){
                    if(res.code == 1){
                        layer.msg(res.msg,{icon:1})
                        setTimeout(function(){
                            location.reload();
                        }, 1500)
                    }else{
                        layer.msg(res.msg,{icon:2})
                    }
                }, 'json')
            }, function(){
                layer.close(index)
            });
        })

    });
</script>

