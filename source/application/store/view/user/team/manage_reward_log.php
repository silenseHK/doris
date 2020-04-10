<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">团队管理奖</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">

                        <div class="am-u-sm-12 am-u-md-3">
                            <div class="am-form-group">
                                <?php if (checkPrivilege('goods/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success"
                                           href="javascript:void(0);"
                                            onclick="refreshData()"
                                        >
                                            刷新本月数据
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group tpl-form-border-form am-fl">
                                    <input type="text" class="am-form-field" name="date" placeholder="YYYY-MM"
                                           value="<?= $request->get('date') ?>">
                                </div>
                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="search" placeholder="请输入用户昵称或电话"
                                               value="<?= $request->get('search') ?>">
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
                                <th>战略董事</th>
                                <th width="15%">商品</th>
                                <th>进货金额</th>
                                <th>获得奖励</th>
                                <th>团队总进货金额</th>
                                <th>团队总奖励</th>
                                <th>日期</th>
                                <th>统计时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle">
                                        <p><?= $item['user']['nickName']?></p>
                                        <p><?= $item['user']['mobile']?></p>
                                    </td>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <img src="<?= $item['goods']['image'][0]['file_path'] ?>" alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $item['goods']['goods_name'] ?></p>
                                        </div>
                                    </td>
                                    <td class="am-text-middle">
                                        <p><?= $item['self_money']?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p><?= $item['self_reward']?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p><?= $item['total_money']?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p><?= $item['total_reward']?></p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['date'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="am-text-center">暂无记录</td>
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
    function refreshData(){
        var index1 = layer.confirm('确定刷新？', {
            btn: ['确定','取消'] //按钮
        }, function(){
            $.post("<?= url('user.team/updateManageData') ?>", {test:1}, function(res){
                if(res.code != 1){
                    layer.msg(res.msg, {icon: 2});
                }else{
                    layer.msg('操作成功', {icon: 1});
                    setTimeout(function(){
                        location.reload();
                    }, 1500)
                }
            }, 'json')

        }, function(){
            layer.close(index1);
        });
    }
</script>

