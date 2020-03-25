<link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">基本信息</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods[goods_name]"
                                           value="<?= $model['goods_name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="goods[category_id]" required
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                                            <option value="<?= $first['category_id'] ?>"
                                                <?= $model['category_id'] == $first['category_id'] ? 'selected' : '' ?>>
                                                <?= $first['name'] ?></option>
                                            <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                <option value="<?= $two['category_id'] ?>"
                                                    <?= $model['category_id'] == $two['category_id'] ? 'selected' : '' ?>>
                                                    　　<?= $two['name'] ?></option>
                                                <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                                    <option value="<?= $three['category_id'] ?>"
                                                        <?= $model['category_id'] == $three['category_id'] ? 'selected' : '' ?>>
                                                        　　　<?= $three['name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            <?php endforeach; endif; ?>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <small class="am-margin-left-xs">
                                        <a href="<?= url('goods.category/add') ?>">去添加</a>
                                    </small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php foreach ($model['image'] as $key => $item): ?>
                                                <div class="file-item">
                                                    <a href="<?= $item['file_path'] ?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= $item['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="goods[images][]"
                                                           value="<?= $item['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="help-block am-margin-top-sm">
                                        <small>尺寸750x750像素以上，大小2M以下 (可拖拽图片调整显示顺序 )</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品卖点 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods[selling_point]" value="<?= $model['selling_point'] ?>">
                                    <small>选填，商品卖点简述，例如：此款商品美观大方 性价比较高 不容错过</small>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">规格/库存</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 商品销售类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="hidden" name="goods[sale_type]" value="<?= $model['sale_type'] ?>" />
                                    <label class="am-radio-inline">
                                        <input disabled type="radio" name="goods[sale_type]" value="1" data-am-ucheck
                                               <?= $model['sale_type'] == 1 ? 'checked' : '' ?>>
                                        层级代理
                                    </label>
                                    <label class="am-radio-inline">
                                        <input disabled type="radio" name="goods[sale_type]" value="2" data-am-ucheck <?= $model['sale_type'] == 2 ? 'checked' : '' ?>>
                                        平台直营（零售）
                                    </label>
                                    <div class="help-block">
                                        <small>如选择层级代理则按照层级价格，代理发货；如平台直营则平台直接发货</small>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-grade__content <?= $model['sale_type'] == 2 ? 'hide' : '' ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 会员价格设置 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <!--                                        <label class="am-radio-inline">-->
                                        <!--                                            <input type="radio" name="goods[is_alone_grade]" value="0" data-am-ucheck-->
                                        <!--                                                   checked>-->
                                        <!--                                            默认折扣-->
                                        <!--                                        </label>-->
                                        <label class="am-radio-inline">
                                            <input type="radio" name="" value="1" data-am-ucheck checked>
                                            单独设置价格
                                        </label>
                                    </div>
                                </div>
                                <div class="panel-grade-alone__content">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label"> </label>
                                        <div class="am-u-sm-9 am-u-md-6 am-u-lg-1 am-u-end">
                                            <!-- 会员等级列表-->
                                            <?php foreach ($gradeList as $item): ?>
                                                <div class="am-input-group am-margin-bottom-sm">
                                                    <span class="am-input-group-label am-input-group-label__left" style="background: none;border-right: 1px solid #dcdfe6;border-radius:4px">
                                                        <?= $item['name'] ?>：
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>

                                        </div>
                                        <div class="am-u-sm-9 am-u-md-6 am-u-lg-3 am-u-end">
                                            <!-- 会员价格列表-->
                                            <?php foreach ($gradeList as $k => $item): ?>
                                                <div class="am-input-group am-margin-bottom-sm">
                                                    <span class="am-input-group-label am-input-group-label__left">
                                                        价格
                                                    </span>
                                                    <input type="number" class="am-form-field"
                                                           name="grade_goods[<?= $item['grade_id'] ?>][price]"
                                                           value="<?= isset($goodsGradeList[$k])?$goodsGradeList[$k]['price']:'' ?>" min="0.01" max="100000" required>
                                                    <span class="am-input-group-label am-input-group-label__right">元</span>

                                                </div>
                                            <?php endforeach; ?>

                                            <div class="help-block">
<!--                                                <small>注：价格范围0.01-100000</small>-->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">库存计算方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!--                                    <label class="am-radio-inline">-->
                                    <!--                                        <input type="radio" name="goods[deduct_stock_type]" value="10" data-am-ucheck>-->
                                    <!--                                        下单减库存-->
                                    <!--                                    </label>-->
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[deduct_stock_type]" value="20" data-am-ucheck
                                               checked>
                                        付款减库存
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品规格 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="hidden" name="goods[spec_type]" value="<?= $model['spec_type'] ?>" />
                                    <label class="am-radio-inline">
                                        <input disabled type="radio" name="goods[spec_type]" value="10" data-am-ucheck <?= $model['spec_type'] == 10 ? 'checked' : '' ?> >
                                        单规格
                                    </label>
                                    <label class="am-radio-inline">
                                        <input disabled type="radio" name="goods[spec_type]" value="20" data-am-ucheck <?= $model['spec_type'] == 20 ? 'checked' : '' ?> >
                                        <span>多规格</span>
                                    </label>
                                </div>
                            </div>

                            <!-- 商品多规格 -->
                            <div id="many-app" v-cloak class="goods-spec-many spec-wrap1 am-form-group" style="display:<?= ($model['sale_type'] == 1 ||$model['spec_type'] == 10)? 'none' : 'block' ?>">
                                <div class="goods-spec-box am-u-sm-9 am-u-sm-push-2 am-u-end">
                                    <!-- 规格属性 -->
                                    <div class="spec-attr">
                                        <div v-for="(item, index) in spec_attr" class="spec-group-item">
                                            <div class="spec-group-name">
                                                <span>{{ item.group_name }}</span>
                                                <i @click="onDeleteGroup(index)"
                                                   class="spec-group-delete iconfont icon-shanchu1" title="点击删除"></i>
                                            </div>
                                            <div class="spec-list am-cf">
                                                <div v-for="(val, i) in item.spec_items" class="spec-item am-fl">
                                                    <span>{{ val.spec_value }}</span>
                                                    <i @click="onDeleteValue(index, i)"
                                                       class="spec-item-delete iconfont icon-shanchu1" title="点击删除"></i>
                                                </div>
                                                <div class="spec-item-add am-cf am-fl">
                                                    <input type="text" v-model="item.tempValue"
                                                           class="ipt-specItem am-fl am-field-valid">
                                                    <button @click="onSubmitAddValue(index)" type="button"
                                                            class="am-btn am-fl">添加
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 添加规格组：按钮 -->
                                    <div v-if="showAddGroupBtn" class="spec-group-button">
                                        <button @click="onToggleAddGroupForm" type="button"
                                                class="am-btn">添加规格
                                        </button>
                                    </div>

                                    <!-- 添加规格：表单 -->
                                    <div v-if="showAddGroupForm" class="spec-group-add">
                                        <div class="spec-group-add-item am-form-group">
                                            <label class="am-form-label form-require">规格名 </label>
                                            <input type="text" class="input-specName tpl-form-input"
                                                   v-model="addGroupFrom.specName"
                                                   placeholder="请输入规格名称">
                                        </div>
                                        <div class="spec-group-add-item am-form-group">
                                            <label class="am-form-label form-require">规格值 </label>
                                            <input type="text" class="input-specValue tpl-form-input"
                                                   v-model="addGroupFrom.specValue"
                                                   placeholder="请输入规格值">
                                        </div>
                                        <div class="spec-group-add-item am-margin-top">
                                            <button @click="onSubmitAddGroup" type="button"
                                                    class="am-btn am-btn-xs am-btn-secondary"> 确定
                                            </button>
                                            <button @click="onToggleAddGroupForm" type="button"
                                                    class="am-btn am-btn-xs am-btn-default"> 取消
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 商品多规格sku信息 -->
                                    <div v-if="spec_list.length > 0" class="goods-sku am-scrollable-horizontal">
                                        <!-- 分割线 -->
                                        <div class="goods-spec-line am-margin-top-lg am-margin-bottom-lg"></div>
                                        <!-- sku 批量设置 -->
                                        <div class="spec-batch am-form-inline">
                                            <div class="am-form-group">
                                                <label class="am-form-label">批量设置</label>
                                            </div>
                                            <div class="am-form-group">
                                                <input type="text" v-model="batchData.goods_no" placeholder="商家编码">
                                            </div>
                                            <div class="am-form-group">
                                                <input type="number" v-model="batchData.goods_price" placeholder="销售价">
                                            </div>
                                            <div class="am-form-group">
                                                <input type="number" v-model="batchData.line_price" placeholder="划线价">
                                            </div>
                                            <div class="am-form-group">
                                                <input type="number" v-model="batchData.stock_num" placeholder="库存数量">
                                            </div>
                                            <div class="am-form-group">
                                                <input type="number" v-model="batchData.goods_weight" placeholder="重量">
                                            </div>
                                            <div class="am-form-group">
                                                <button @click="onSubmitBatchData" type="button"
                                                        class="am-btn am-btn-sm am-btn-secondaryam-radius">确定
                                                </button>
                                            </div>
                                        </div>
                                        <!-- sku table -->
                                        <table class="spec-sku-tabel am-table am-table-bordered am-table-centered
                                     am-margin-bottom-xs am-text-nowrap">
                                            <tbody>
                                            <tr>
                                                <th v-for="item in spec_attr">{{ item.group_name }}</th>
                                                <th>规格图片</th>
                                                <th>商家编码</th>
                                                <th>销售价</th>
                                                <th>划线价</th>
                                                <th>库存</th>
                                                <th>重量(kg)</th>
                                            </tr>
                                            <tr v-for="(item, index) in spec_list">
                                                <td v-for="td in item.rows" class="td-spec-value am-text-middle"
                                                    :rowspan="td.rowspan">
                                                    {{ td.spec_value }}
                                                </td>
                                                <td class="am-text-middle spec-image">
                                                    <div v-if="item.form.image_id" class="j-selectImg data-image"
                                                         v-bind:data-index="index">
                                                        <img :src="item.form.image_path" alt="">
                                                        <i class="iconfont icon-shanchu image-delete"
                                                           @click.stop="onDeleteSkuImage(index)"></i>
                                                    </div>
                                                    <div v-else class="j-selectImg upload-image"
                                                         v-bind:data-index="index">
                                                        <i class="iconfont icon-add"></i>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" class="ipt-goods-no" name="goods_no"
                                                           v-model="item.form.goods_no">
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="goods_price"
                                                           v-model="item.form.goods_price" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="line_price"
                                                           v-model="item.form.line_price">
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="stock_num"
                                                           v-model="item.form.stock_num" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="goods_weight"
                                                           v-model="item.form.goods_weight" required>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- 商品多规格2 -->
                            <div id="many-app2" v-cloak class="goods-spec-many spec-wrap2 am-form-group" style="display:<?= ($model['sale_type'] == 2 ||$model['spec_type'] == 10)? 'none' : 'block' ?>">

                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品编码 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" name="goods[sku2][goods_no]"
                                               value="<?= isset($specData2['spec_list'])?$specData2['spec_list'][0]['form']['goods_no']:'' ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品价格 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku2][goods_price]"
                                               value="<?= isset($specData2['spec_list'])?$specData2['spec_list'][0]['form']['goods_price']:'' ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品划线价 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku2][line_price]"
                                            value="<?= isset($specData2['spec_list'])?$specData2['spec_list'][0]['form']['line_price']:'' ?>"
                                        >
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">当前库存数量 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku2][stock_num]"
                                               value="<?= $model['stock'] ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品重量(Kg) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku2][goods_weight]"
                                               value="<?= isset($specData2['spec_list'])?$specData2['spec_list'][0]['form']['goods_weight']:'' ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="goods-spec-box am-u-sm-9 am-u-sm-push-2 am-u-end">
                                    <!-- 规格属性 -->
                                    <div class="spec-attr spec-attr-wrap">
<!--                                        <div class="spec-group-item">-->
<!--                                            <div class="spec-group-name">-->
<!--                                                <span>aaa</span>-->
<!--                                                <i class="spec-group-delete iconfont icon-shanchu1" title="点击删除"></i>-->
<!--                                            </div>-->
<!--                                            <div class="spec-list am-cf">-->
<!--                                                <div class="spec-item am-fl">-->
<!--                                                    <span>ddd</span>-->
<!--                                                    <i class="spec-item-delete iconfont icon-shanchu1" title="点击删除"></i>-->
<!--                                                </div>-->
<!--                                                <div class="spec-item-add am-cf am-fl">-->
<!--                                                    <input type="text" class="ipt-specItem am-fl am-field-valid">-->
<!--                                                    <button type="button"-->
<!--                                                            class="am-btn am-fl">添加-->
<!--                                                    </button>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        </div>-->
                                    </div>

                                    <!-- 添加规格组：按钮 -->
                                    <div class="spec-group-button spec-group-button2 <?= $model['sale_type']==1 && $model['spec_type'] ==20 ? 'hide' : '' ?>">
                                        <button onclick="showAddAttrWrap()" type="button"
                                                class="am-btn">添加规格
                                        </button>
                                    </div>

                                    <!-- 添加规格：表单 -->
                                    <div class="spec-group-add spec-group-add2 hide">
                                        <div class="spec-group-add-item am-form-group">
                                            <label class="am-form-label form-require">规格名 </label>
                                            <input type="text" class="input-specName tpl-form-input ipt-attr-key"
                                                   placeholder="请输入规格名称">
                                        </div>
                                        <div class="spec-group-add-item am-form-group">
                                            <label class="am-form-label form-require">规格值 </label>
                                            <input type="text" class="input-specValue tpl-form-input ipt-attr-val"
                                                   placeholder="请输入规格值">
                                        </div>
                                        <div class="spec-group-add-item am-margin-top">
                                            <button type="button"
                                                    onclick="addAttr.call(this)"
                                                    class="am-btn am-btn-xs am-btn-secondary"> 确定
                                            </button>
                                            <button type="button"
                                                    class="am-btn am-btn-xs am-btn-default"> 取消
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 商品多规格sku信息 -->
<!--                                    <div v-if="spec_list.length > 0" class="goods-sku am-scrollable-horizontal">-->
<!--                                         分割线 -->
<!--                                        <div class="goods-spec-line am-margin-top-lg am-margin-bottom-lg"></div>-->
<!--                                         sku table -->
<!--                                        <table class="spec-sku-tabel am-table am-table-bordered am-table-centered-->
<!--                                     am-margin-bottom-xs am-text-nowrap">-->
<!--                                            <tbody>-->
<!--                                            <tr>-->
<!--                                                <th v-for="item in spec_attr">{{ item.group_name }}</th>-->
<!--                                                <th>规格图片</th>-->
<!--                                            </tr>-->
<!--                                            <tr v-for="(item, index) in spec_list">-->
<!--                                                <td v-for="td in item.rows" class="td-spec-value am-text-middle"-->
<!--                                                    :rowspan="td.rowspan">-->
<!--                                                    {{ td.spec_value }}-->
<!--                                                </td>-->
<!--                                                <td class="am-text-middle spec-image">-->
<!--                                                    <div v-if="item.form.image_id" class="j-selectImg data-image"-->
<!--                                                         v-bind:data-index="index">-->
<!--                                                        <img :src="item.form.image_path" alt="">-->
<!--                                                        <i class="iconfont icon-shanchu image-delete"-->
<!--                                                           @click.stop="onDeleteSkuImage(index)"></i>-->
<!--                                                    </div>-->
<!--                                                    <div v-else class="j-selectImg upload-image"-->
<!--                                                         v-bind:data-index="index">-->
<!--                                                        <i class="iconfont icon-add"></i>-->
<!--                                                    </div>-->
<!--                                                </td>-->
<!--                                            </tr>-->
<!--                                            </tbody>-->
<!--                                        </table>-->
<!--                                    </div>-->
                                </div>

                            </div>

                            <!-- 商品单规格 -->
                            <div class="goods-spec-single" style="display:<?= ($model['spec_type'] != 10)? 'none' : 'block' ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品编码 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" name="goods[sku][goods_no]"
                                               value="<?= $model['sku'][0]['goods_no'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品价格 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku][goods_price]"
                                               value="<?= $model['sku'][0]['goods_price'] ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品划线价 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku][line_price]" value="<?= $model['sku'][0]['line_price'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">当前库存数量 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku][stock_num]"
                                               value="<?= $model['sku'][0]['stock_num'] ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品重量(Kg) </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="number" class="tpl-form-input" name="goods[sku][goods_weight]"
                                               value="<?= $model['sku'][0]['goods_weight'] ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <!-- 会员价格设置 -->
<!--                            <div class="widget-head am-cf">-->
<!--                                <div class="widget-title am-fl">会员价格设置</div>-->
<!--                            </div>-->


                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">商品详情</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品详情 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="goods[content]" type="text/plain"><?= htmlspecialchars_decode($model['content']) ?></textarea>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">其他设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">运费模板 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="goods[delivery_id]" required
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder:'请选择运费模板'}">
                                        <option value="">请选择运费模板</option>
                                        <?php foreach ($delivery as $item): ?>
                                            <option value="<?= $item['delivery_id'] ?>"
                                                <?= $model['delivery_id'] == $item['delivery_id'] ? 'selected' : '' ?>>
                                                <?= $item['name'] ?> (<?= $item['method']['text'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="am-margin-left-xs">
                                        <a href="<?= url('setting.delivery/add') ?>">去添加</a>
                                    </small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[goods_status]" value="10" data-am-ucheck
                                            <?= $model['goods_status']['value'] == 10 ? 'checked' : '' ?> >
                                        上架
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[goods_status]" value="20" data-am-ucheck
                                            <?= $model['goods_status']['value'] == 20 ? 'checked' : '' ?> >
                                        下架
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">初始销量</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="goods[sales_initial]"
                                           value="<?= $model['sales_initial'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="goods[goods_sort]"
                                           value="<?= $model['goods_sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否累计积分 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[is_add_integral]" value="1" data-am-ucheck
                                            <?= $model['is_add_integral'] == 1 ? 'checked' : '' ?> >
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[is_add_integral]" value="0" data-am-ucheck
                                            <?= $model['is_add_integral'] == 0 ? 'checked' : '' ?> >
                                        否
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group integral_weight_content">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">每件商品积分</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="goods[integral_weight]"
                                           value="<?= $model['integral_weight'] ?>" min="0">
                                    <small>积分用于提升会员等级</small>
                                </div>
                            </div>

                            <!-- 商品积分设置 -->
<!--                            <div class="widget-head am-cf">-->
<!--                                <div class="widget-title am-fl">积分设置</div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否开启积分赠送 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="goods[is_points_gift]" value="1" data-am-ucheck-->
<!--                                               checked>-->
<!--                                        开启-->
<!--                                    </label>-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="goods[is_points_gift]" value="0" data-am-ucheck>-->
<!--                                        关闭-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否允许使用积分抵扣 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="goods[is_points_discount]" value="1" data-am-ucheck-->
<!--                                               checked>-->
<!--                                        允许-->
<!--                                    </label>-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="goods[is_points_discount]" value="0" data-am-ucheck>-->
<!--                                        不允许-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"></label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <div class="help-block">-->
<!--                                        <small>注：如需使用积分功能必须在 [营销管理 - 积分设置] 中开启</small>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->


                            <!-- 返利设置 -->
                            <!--<div class="widget-head am-cf">
                                <div class="widget-title am-fl">返利设置</div>
                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否开启单独分销 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="goods[is_ind_dealer]" value="0" data-am-ucheck-->
<!--                                               checked>-->
<!--                                        关闭-->
<!--                                    </label>-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="goods[is_ind_dealer]" value="1" data-am-ucheck>-->
<!--                                        开启-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="panel-dealer__content hide">-->
<!--                                <div class="am-form-group">-->
<!--                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分销佣金类型 </label>-->
<!--                                    <div class="am-u-sm-9 am-u-end">-->
<!--                                        <label class="am-radio-inline">-->
<!--                                            <input type="radio" name="goods[dealer_money_type]" value="10"-->
<!--                                                   data-am-ucheck-->
<!--                                                   checked>-->
<!--                                            百分比-->
<!--                                        </label>-->
<!--                                        <label class="am-radio-inline">-->
<!--                                            <input type="radio" name="goods[dealer_money_type]" value="20"-->
<!--                                                   data-am-ucheck>-->
<!--                                            固定金额-->
<!--                                        </label>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="am-form-group">-->
<!--                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">单独分销设置 </label>-->
<!--                                    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">-->
<!--                                        <div class="am-input-group am-margin-bottom">-->
<!--                                            <span class="am-input-group-label am-input-group-label__left">一级佣金：</span>-->
<!--                                            <input type="text" name="goods[first_money]" value=""-->
<!--                                                   class="am-form-field">-->
<!--                                            <span class="widget-dealer__unit am-input-group-label am-input-group-label__right">%</span>-->
<!--                                        </div>-->
<!--                                        <div class="am-input-group am-margin-bottom">-->
<!--                                            <span class="am-input-group-label am-input-group-label__left">二级佣金：</span>-->
<!--                                            <input type="text" name="goods[second_money]" value=""-->
<!--                                                   class="am-form-field">-->
<!--                                            <span class="widget-dealer__unit am-input-group-label am-input-group-label__right">%</span>-->
<!--                                        </div>-->
<!--                                        <div class="am-input-group am-margin-bottom">-->
<!--                                            <span class="am-input-group-label am-input-group-label__left">三级佣金：</span>-->
<!--                                            <input type="text" name="goods[third_money]" value=""-->
<!--                                                   class="am-form-field">-->
<!--                                            <span class="widget-dealer__unit am-input-group-label am-input-group-label__right">%</span>-->
<!--                                        </div>-->
<!--                                        <div class="help-block">-->
<!--                                            <p>-->
<!--                                                <small>注：如需使用分销功能必须在 [分销中心 - 分销设置] 中开启</small>-->
<!--                                            </p>-->
<!--                                            <p>-->
<!--                                                <small>注：如不开启单独分销则默认使用全局分销比例</small>-->
<!--                                            </p>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->

                            <!-- 表单提交按钮 -->
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>

                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/js/vue.min.js"></script>
<script src="assets/common/js/ddsort.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script>

    $(function () {

        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });

        // 选择图片
        $('.upload-file').selectImages({
            name: 'goods[images][]'
            , multiple: true
        });

        // 图片列表拖动
        $('.uploader-list').DDSort({
            target: '.file-item',
            delay: 100, // 延时处理，默认为 50 ms，防止手抖点击 A 链接无效
            floatStyle: {
                'border': '1px solid #ccc',
                'background-color': '#fff'
            }
        });

        // 切换单/多规格
        $('input:radio[name="goods[spec_type]"]').change(function (e) {
            var $goodsSpecMany = $('.spec-wrap1')
                , $goodsSpecMany2 = $('.spec-wrap2')
                , $goodsSpecSingle = $('.goods-spec-single')
                , $goodsSaleType = $('input:radio[name="goods[sale_type]"]:checked');
            if (e.currentTarget.value === '10') {
                $goodsSpecMany.hide() && $goodsSpecSingle.show() &&$goodsSpecMany2.hide() ;
            } else {
                if($goodsSaleType.val() === '1'){  //层级代理
                    $goodsSpecMany.hide() && $goodsSpecSingle.hide() && $goodsSpecMany2.show();
                }else{  //平台直营
                    $goodsSpecMany.show() && $goodsSpecSingle.hide() && $goodsSpecMany2.hide();
                }

            }
        });

        // 注册商品多规格组件
        var specMany = new GoodsSpec({
            el: '#many-app',
            baseData: <?= $specData ?>
        });

        // 注册商品多规格组件
        var specMany2 = new GoodsSpec({
            el: '#many-app2'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            // 获取多规格sku数据
            buildData: function () {
                var specData = specMany.appVue.getData();
                return {
                    goods: {
                        spec_many: {
                            spec_attr: specData.spec_attr,
                            spec_list: specData.spec_list
                        }
                    },
                    specs:{
                        spec_val_id,
                        spec_id
                    }
                };
            },
            // 自定义验证
            validation: function () {
                var specType = $('input:radio[name="goods[spec_type]"]:checked').val();
                var saleType = $('input:radio[name="goods[sale_type]"]:checked').val();
                if (specType === '20' && saleType === '2') {
                    var isEmpty = specMany.appVue.isEmptySkuList();
                    isEmpty === true && layer.msg('商品规格不能为空');
                    return !isEmpty;
                }
                return true;
            }
        });

        // 是否开启单独分销
        var $panelDealer = $('.panel-dealer__content');
        $("input:radio[name='goods[is_ind_dealer]']").change(function (e) {
            // e.currentTarget.value === '0' ? $panelDealer.hide() : $panelDealer.show();
            $panelDealer.toggle();
        });

        // 选中百分比 后面显示% 选中固定金额 后面显示元
        $("input:radio[name='goods[dealer_money_type]']").change(function (e) {
            $('.widget-dealer__unit').text(e.currentTarget.value === '10' ? '%' : '元');
        });

        // 是否开启会员折扣
        var $panelGrade = $('.panel-grade__content');
        $("input:radio[name='goods[sale_type]']").change(function (e) {

            var $goodsSpecMany = $('.spec-wrap1')
                , $goodsSpecMany2 = $('.spec-wrap2')
                , $goodsSpecSingle = $('.goods-spec-single');

            // e.currentTarget.value === '0' ? $panelGrade.toggle() : $panelGrade.toggle();
            $panelGrade.toggle();
            var goodsSpecType = $('input:radio[name="goods[spec_type]"]:checked');
            if(goodsSpecType.val() === '20' && e.currentTarget.value === '1'){  //层级代理
                $goodsSpecMany.hide() && $goodsSpecSingle.hide() && $goodsSpecMany2.show();
            }else if(goodsSpecType.val() === '20' && e.currentTarget.value === '2'){  //平台直营
                $goodsSpecMany.show() && $goodsSpecSingle.hide() && $goodsSpecMany2.hide();
            }else{
                $goodsSpecMany.hide() && $goodsSpecSingle.show() &&$goodsSpecMany2.hide() ;
            }
        });

        // 单独设置折扣
        var $panelGradeAlone = $('.panel-grade-alone__content');
        $("input:radio[name='goods[is_alone_grade]']").change(function (e) {
            // e.currentTarget.value !== '0' ? $panelGradeAlone.hide() : $panelGradeAlone.show();
            $panelGradeAlone.toggle();
        });

        // 累计积分
        var $integralWeightContent = $('.integral_weight_content');
        $("input:radio[name='goods[is_add_integral]']").change(function(e){
            $integralWeightContent.toggle();
        })

        $('.spec-make-add-btn').on('click', function(){
            console.log(1)
            $('.spec-add-btn').hide();
        })

        $('.add-spec-btn2').on('click', function(){
            $('.spec-group-add2').show();
        })

    });

    var spec_id = <?= isset($spec_val['spec_id'])? $spec_val['spec_id'] : 0 ?>;
    var spec_val_id = <?= isset($spec_val['spec_val_id'])? json_encode($spec_val['spec_val_id']) : [] ?>;
    var spec_key = "<?= isset($spec_val['spec_key'])? $spec_val['spec_key'] : 0 ?>";
    var spec_val = <?= isset($spec_val['spec_val'])? json_encode($spec_val['spec_val']) : [] ?>;
    initAttr();
    //设置规格
    function addAttr(){
        var $attrKey = $('.ipt-attr-key').val(),
            $attrVal = $('.ipt-attr-val').val();
        if(!$.trim($attrKey) || !$.trim($attrVal))
            return layer.msg('规格名或规格值不能为空');
        $.post('index.php/?s=/store/goods.spec/addSpec', {spec_name:$attrKey,spec_value:$attrVal}, function(res){
            if(res.code === 1){
                spec_id = res.data.spec_id;
                spec_val_id.push(res.data.spec_value_id);
                spec_key = $attrKey;
                spec_val.push($attrVal)
                initAttr();
                $('.spec-group-add2').hide();
                $('.ipt-attr-key').val('')
                $('.ipt-attr-val').val('')
            }else{
                layer.msg(res.msg)
            }
        }, 'json')
    }

    function initAttr(){

        var spec_html = "";
        if(spec_val.length > 0){
            spec_html = '<div class="spec-group-item">\n' +
                '                                            <div class="spec-group-name">\n' +
                '                                                <span>'+ spec_key +'</span>\n' +
                '                                                <i class="spec-group-delete iconfont icon-shanchu1" title="点击删除" onclick="delAttr()"></i>\n' +
                '                                            </div>\n' +
                '                                            <div class="spec-list am-cf">\n';

            $.each(spec_val, function(i, n){
                spec_html += '                                                <div class="spec-item am-fl">\n' +
                    '                                                    <span>'+ n +'</span>\n' +
                    '                                                    <i data-idx="'+ i +'" class="spec-item-delete iconfont icon-shanchu1" title="点击删除" onclick="delAttrVal.call(this)"></i>\n' +
                    '                                                </div>\n'
            });
            spec_html +=
                '                                                <div class="spec-item-add am-cf am-fl">\n' +
                '                                                    <input type="text" class="ipt-specItem am-fl am-field-valid ipt-new-spec-val">\n' +
                '                                                    <button type="button"\n' +
                '                                                            class="am-btn am-fl" onclick="addSpecVal()">添加\n' +
                '                                                    </button>\n' +
                '                                                </div>\n' +
                '                                            </div>\n' +
                '                                        </div>';
        }

        $('.spec-attr-wrap').html(spec_html);

    }

    function showAddAttrWrap(){
        $('.spec-group-button2').hide()
        $('.spec-group-add2').show()
    }

    function addSpecVal(){
        var iptNewSpecVal = $('.ipt-new-spec-val');
        var specVal = iptNewSpecVal.val();
        if(!$.trim(specVal))return layer.msg('请输入属性值');
        $.post('index.php?s=/store/goods.spec/addSpecValue', {spec_id:spec_id, spec_value:specVal}, function(res){
            if(res.code === 1){
                spec_val_id.push(res.data.spec_value_id);
                spec_val.push(specVal)
                initAttr();
            }else{
                layer.msg(res.msg)
            }
        }, 'json')
    }

    function delAttrVal(){
        var idx = $(this).data('idx');
        spec_val.splice(idx, 1)
        spec_val_id.splice(idx, 1)
        initAttr();
    }

    function delAttr(){
         spec_id = 0;
         spec_val_id = [];
         spec_key = "";
         spec_val = [];
        initAttr();
        $('.spec-group-add2').show();
    }

</script>
