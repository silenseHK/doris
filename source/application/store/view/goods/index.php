<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">出售中的商品</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('goods/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('goods/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $category_id = $request->get('category_id') ?: null; ?>
                                        <select name="category_id"
                                                data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder: '商品分类', maxHeight: 400}">
                                            <option value=""></option>
                                            <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                                                <option value="<?= $first['category_id'] ?>"
                                                    <?= $category_id == $first['category_id'] ? 'selected' : '' ?>>
                                                    <?= $first['name'] ?></option>
                                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                    <option value="<?= $two['category_id'] ?>"
                                                        <?= $category_id == $two['category_id'] ? 'selected' : '' ?>>
                                                        　　<?= $two['name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $status = $request->get('status') ?: null; ?>
                                        <select name="status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '商品状态'}">
                                            <option value=""></option>
                                            <option value="10"
                                                <?= $status == 10 ? 'selected' : '' ?>>上架
                                            </option>
                                            <option value="20"
                                                <?= $status == 20 ? 'selected' : '' ?>>下架
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入商品名称"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
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
                                <th>商品ID</th>
                                <th>商品图片</th>
                                <th>商品名称</th>
                                <th>商品分类</th>
                                <th>商品类型</th>
                                <th>实际销量</th>
                                <th>商品排序</th>
                                <th>商品状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['goods_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['image'][0]['file_path'] ?>"
                                           title="点击查看大图" target="_blank">
                                            <img src="<?= $item['image'][0]['file_path'] ?>"
                                                 width="50" height="50" alt="商品图片">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['goods_name'] ?></p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['category']['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['sale_type'] == 1? "代理商品" : "直营商品" ?></td>
                                    <td class="am-text-middle"><?= $item['sales_actual'] ?></td>
                                    <td class="am-text-middle"><?= $item['goods_sort'] ?></td>
                                    <td class="am-text-middle">
                                           <span class="j-state am-badge x-cur-p
                                           am-badge-<?= $item['goods_status']['value'] == 10 ? 'success' : 'warning' ?>"
                                                 data-id="<?= $item['goods_id'] ?>"
                                                 data-state="<?= $item['goods_status']['value'] ?>">
                                               <?= $item['goods_status']['text'] ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('goods/edit')): ?>
                                                <a href="<?= url('goods/edit',
                                                    ['goods_id' => $item['goods_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>

                                            <?php if (checkPrivilege('goods/addStock')): ?>
                                                <a href="javascript:void(0);" data-goods-id="<?= $item['goods_id'] ?>" class="item-add-stock">
                                                    <i class="am-icon-plus"></i> 补充库存
                                                </a>
                                            <?php endif; ?>

                                            <?php if (checkPrivilege('goods/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['goods_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>

                                            <?php /* if (checkPrivilege('goods/copy')): ?>
                                                <a class="tpl-table-black-operation-green" href="<?= url('goods/copy',
                                                    ['goods_id' => $item['goods_id']]) ?>">
                                                    一键复制
                                                </a>
                                            <?php endif; */ ?>
                                        </div>
                                    </td>
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

<script id="tpl-add-stock" type="text/template">
        <form class="am-form tpl-form-line-form" method="post" action="">
                <div class="am-padding-xs">
                    <div class="am-tab-panel am-padding-0">
                        {{ if spec_list.length >= 1 }}
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                补充库存商品
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <select id="doc-select-1" name="" onchange="goodsSku.call(this)">
                                    <option value="0">请选择商品</option>
                                    {{each spec_list item key}}
                                    <option {{ if item.goods_sku_id == goods_sku_id }} selected {{ /if }} value="{{ item.goods_sku_id }}">{{ item['attr'] }}</option>
                                    {{/each}}
                                </select>
                            </div>
                        </div>
                        {{ /if }}

                        <input class="ipt-goods-sku-id" type="hidden" name="goods_sku_id" value="{{ goods_sku_id }}">

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前库存
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static stock-wrap">{{stock}}</div>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                补充方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="mode"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="mode" value="dec" data-am-ucheck>
                                    减少
                                </label>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                变更数量
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="num" value="0" required>
                            </div>
                        </div>

                    </div>
                </div>

        </form>
</script>

<script>
    $(function () {

        // 商品状态
        $('.j-state').click(function () {
            // 验证权限
            if (!"<?= checkPrivilege('goods/state')?>") {
                return false;
            }
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data.state) === 10 ? '下架' : '上架') + '该商品吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('goods/state') ?>"
                        , {
                            goods_id: data.id,
                            state: Number(!(parseInt(data.state) === 10))
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });

        // 删除元素
        var url = "<?= url('goods/delete') ?>";
        $('.item-delete').delete('goods_id', url);

        $('.item-add-stock').on('click', function(){
            let goods_id = $(this).data('goods-id');
            $.post("<?= url('goods/getGoodsSpec') ?>", {goods_id}, function(res){
                let data = [];
                let goods_sku_id = res.data.list[0].goods_sku_id
                data['spec_list'] = res.data.list;
                data['goods_sku_id'] = goods_sku_id;
                $.post("<?= url('goods/getSkuStock') ?>", {goods_sku_id}, function(result){
                    data['stock'] = result.data;
                    $.showModal({
                        title: '补充库存'
                        , area: '460px'
                        , content: template('tpl-add-stock', data)
                        , uCheck: true
                        , success: function ($content) {

                        }
                        , yes: function ($content) {
                            $content.find('form').myAjaxSubmit({
                                url: '<?= url('goods/recharge') ?>',
                                data: {

                                },
                            });
                            return true;
                        }
                    });
                }, 'json')
            }, 'json')
        })

    });

    function goodsSku(){
        let goods_sku_id = parseInt($(this).val());
        if(!goods_sku_id)return false;
        $.post("<?= url('goods/getSkuStock') ?>", {goods_sku_id}, function(result){
            $('.stock-wrap').text(result.data);
            $('.ipt-goods-sku-id').val(goods_sku_id);
        }, 'json')
    }

</script>

