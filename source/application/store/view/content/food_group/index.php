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
                                               href="<?= url('content.food_group/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
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
                                <th>图片</th>
                                <th>最大BMI</th>
                                <th>最小BMI</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="javascript:void(0);" onclick="showImage.call(this)" data-image="<?= $item['image']['file_path'] ?>" title="点击查看大图" target="">
                                            <img src="<?= $item['image']['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['max_bmi'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['min_bmi'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <a class="tpl-table-black-operation-default"
                                               href="<?= url('content.foodGroup/edit', ['id'=>$item['id']]) ?>"
                                               title="编辑"
                                            >
                                                <i class="iconfont am-icon-edit"></i>
                                                编辑
                                            </a>
                                            <a class="tpl-table-black-operation-default btn-del"
                                               data-id="<?= $item['id'] ?>"
                                               href="javascript:void(0);"
                                               title="删除"
                                            >
                                                <i class="iconfont am-icon-close"></i>
                                                删除
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

        $('.btn-del').on('click', function(e){
            let id = $(this).data('id');
            let index = layer.confirm('确定删除吗？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                layer.close(index);
                $.post("<?= url('content.foodGroup/del') ?>", {id}, function(res){
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

    function showImage(){
        let img = $(this).data('image')
        let content = '<img style="width:375px;" src="'+ img +'" />'
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['375px','auto'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: content
        });
    }
</script>

