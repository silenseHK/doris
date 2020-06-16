<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">文章分类</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('content.online_questions/cateadd')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('content.online_questions/cateadd') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black">
                            <thead>
                            <tr>
                                <th>分类ID</th>
                                <th>名称</th>
                                <th>图标</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['cate_id'] ?></td>
                                    <td class="am-text-middle"><?= $first['title'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="javascript:void(0);" onclick="showImage.call(this)" data-image="<?= $first['icon']?$first['icon']['file_path']:'' ?>" title="点击查看大图" target="">
                                            <img src="<?= $first['icon']?$first['icon']['file_path']:'' ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle"><?= $first['sort'] ?></td>
                                    <td class="am-text-middle"><?= $first['create_time'] ?></td>
                                    <td class="am-text-middle"><span onclick="changeStatus(<?= $first['cate_id'] ?>)" style="cursor:pointer"><?= $first['status'] == 1? '上线': '下线' ?></span></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('content.online_questions/cateedit')): ?>
                                                <a href="<?= url('content.online_questions/cateedit',
                                                    ['cate_id' => $first['cate_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('content.online_questions/catedelete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['cate_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
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
        var url = "<?= url('content.online_questions/catedelete') ?>";
        $('.item-delete').delete('cate_id', url);

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

    function changeStatus(cate_id){
        $.post("<?= url('content.online_questions/catestatuschange') ?>",{cate_id},function(res){
            layer.msg(res.msg);
            if(res.code == 1){
                setTimeout(function(){
                    location.reload()
                },1000)
            }
        },'json')
    }

</script>

