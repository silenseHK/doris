<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">用户列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">

                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">

                            <div class="am-u-sm-12 am-u-md-12 am-u-sm-push-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <?php if (checkPrivilege('user/fileTransferStock')): ?>
                                            <div class="am-btn-group am-btn-group-xs">
                                                <a class="am-btn am-btn-default am-btn-success j-transfer-stock"
                                                   href="javascript:;">批量迁移库存
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $grade = $request->get('grade'); ?>
                                        <select name="grade"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择会员等级'}">
                                            <option value=""></option>
                                            <option value="1">全部</option>
                                            <?php foreach ($gradeList as $item): ?>
                                                <option value="<?= $item['grade_id'] ?>"
                                                    <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $gender = $request->get('gender'); ?>
                                        <select name="gender"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择性别'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $gender === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $gender === '1' ? 'selected' : '' ?>>男
                                            </option>
                                            <option value="2"
                                                <?= $gender === '2' ? 'selected' : '' ?>>女
                                            </option>
                                            <option value="0"
                                                <?= $gender === '0' ? 'selected' : '' ?>>未知
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               autocomplete="off"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               autocomplete="off"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_id"
                                                   placeholder="请输入用户ID"
                                                   value="<?= $request->get('user_id') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="mobile"
                                                   placeholder="请输入用户手机号"
                                                   value="<?= $request->get('mobile') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="nickName"
                                                   placeholder="请输入微信昵称"
                                                   value="<?= $request->get('nickName') ?>">
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
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>用户余额</th>
                                <th>会员等级</th>
                                <th>实际消费金额</th>
                                <th>性别</th>
                                <th>国家</th>
                                <th>省份</th>
                                <th>城市</th>
                                <th>手机号</th>
                                <th>邀请人</th>
                                <th>注册时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="wrap-tbody" data-cur-user-id="0">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['user_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle"><?= $item['nickName'] ?></td>
                                    <td class="am-text-middle"><?= $item['balance'] ?></td>
                                    <td class="am-text-middle">
                                        <?= !empty($item['grade']) ? $item['grade']['name'] : '--' ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['expend_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['gender'] ?></td>
                                    <td class="am-text-middle"><?= $item['country'] ?: '--' ?></td>
                                    <td class="am-text-middle"><?= $item['province'] ?: '--' ?></td>
                                    <td class="am-text-middle"><?= $item['city'] ?: '--' ?></td>
                                    <td class="am-text-middle"><?= $item['mobile_hide'] ?: '--' ?></td>
                                    <td class="am-text-middle">
                                        <?php if(empty($item['invitation_user'])):?>
                                            无
                                        <?php else:?>
                                            <?= $item['invitation_user']['nickName'] ?>(id:<?= $item['invitation_user']['user_id'] ?>)
                                        <?php endif?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user/recharge')): ?>
                                                <a class="j-recharge tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   title="用户充值"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   data-balance="<?= $item['balance'] ?>"
                                                   data-points="<?= $item['points'] ?>"
                                                   onclick="fillIptUserId.call(this)"
                                                >
                                                    <i class="iconfont icon-qiandai"></i>
                                                    充值
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/grade')): ?>
                                                <a class="j-grade tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   title="修改会员等级">
                                                    <i class="iconfont icon-grade-o"></i>
                                                    会员等级
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/delete')): ?>
                                                <a class="j-delete tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>" title="删除用户">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <div class="j-opSelect operation-select am-dropdown">
                                                <button type="button"
                                                        class="am-dropdown-toggle am-btn am-btn-sm am-btn-secondary">
                                                    <span>更多</span>
                                                    <span class="am-icon-caret-down"></span>
                                                </button>
                                                <ul class="am-dropdown-content" data-id="<?= $item['user_id'] ?>">
                                                    <?php if (checkPrivilege('user.order/index')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target=""
                                                               href="<?= url('user.order/index', ['user_id' => $item['user_id']]) ?>">明细</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (!checkPrivilege('user.recharge/order')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target=""
                                                               href="<?= url('user.recharge/order', ['user_id' => $item['user_id']]) ?>">充值记录</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.balance/log')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target=""
                                                               href="<?= url('user.balance/log', ['user_id' => $item['user_id']]) ?>">余额明细</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.goods/goodsstock')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target=""
                                                               href="<?= url('user.goods/goodsstock', ['user_id' => $item['user_id']]) ?>">库存信息</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.team/teamlists')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target=""
                                                               href="<?= url('user.team/teamlists', ['user_id' => $item['user_id']]) ?>">团队成员</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.team/bestrategy')): ?>
                                                        <li>
                                                            <a data-id="<?= $item['user_id'] ?>" class="am-dropdown-item j-rebate" target=""
                                                               href="javascript:void(0);">成为战略董事</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.team/freeze')): ?>
                                                        <li>
                                                            <a data-id="<?= $item['user_id'] ?>" class="am-dropdown-item j-team-freeze" target=""
                                                               href="javascript:void(0);">冻结团队</a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="14" class="am-text-center">暂无记录</td>
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
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<!-- 模板：修改会员等级 -->
<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        会员等级
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="grade[grade_id]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择会员等级'}">
                            <?php foreach ($gradeList as $item): ?>
                                <option value="<?= $item['grade_id'] ?>"
                                    <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 管理员备注 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="grade[remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<!-- 模板：修改会员等级 -->
<script id="tpl-transfer-stock" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="" enctype="multipart/form-data">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">
                        补充库存商品
                    </label>
                    <div class="am-u-sm-8 am-u-end wrap-goods">
                        <select id="doc-select-1" name="transfer_stock[goods_id]" onchange="goodsSku2.call(this)">
                            <option value="0">请选择商品</option>
                            <?php if(!empty($goodsList)){ ?>
                                <?php foreach($goodsList as $goods){ ?>
                                    <option value="<?= $goods['goods_id'] ?>"><?= $goods['goods_name'] ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <input class="ipt-goods-sku-id" type="hidden" name="transfer_stock[goods_sku_id]" value="0">
                    </div>
                </div>

                <div class="am-form-group wrap-attr">
                    <label class="am-u-sm-3 am-form-label">
                        规格
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select class="doc-select-attr" name="transfer_stock[goods_sku_id2]" onchange="chooseGoods2.call(this)">
                            <option value="0">请选择规格</option>
                        </select>
                    </div>
                </div>

                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        迁移文件
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input style="position: absolute;left:0px; z-index:999;opacity: 0;" type="file" name="transfer_stock_file">
                        <a class="am-btn am-btn-default am-btn-success" style="position: absolute;left:0px;z-index:998;"
                           href="javascript:;">选择文件
                        </a>
                    </div>
                </div>

                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 管理员备注 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <textarea rows="2" name="transfer_stock[remark]" placeholder="请输入管理员备注"
                                  class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<!-- 商品列表 -->
<script id="tpl-goods-list-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.image }}" title="{{ $value.goods_name }}" target="_blank">
            <img src="{{ $value.image }}">
        </a>
        <input type="hidden" name="setting[condition][become__buy_goods_ids][]" value="{{ $value.goods_id }}">
        <i class="iconfont icon-shanchu file-item-delete" data-name="商品"></i>
    </div>
    {{ /each }}
</script>
<!-- 模板：用户充值 -->
<script id="tpl-recharge" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">

                <ul class="am-tabs-nav am-nav am-nav-tabs">
                    <li class="am-active"><a href="#tab1">充值余额</a></li>
                    <li><a href="#tab2">补充库存</a></li>
                    <li><a href="#tab3">活动补充库存</a></li>
                    <li><a href="#tab4">补充库存[迁移代理]</a></li>
                </ul>

                <div class="am-tabs-bd am-padding-xs">

                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前余额
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static">{{ balance }}</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]" value="dec" data-am-ucheck>
                                    减少
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]" value="final" data-am-ucheck>
                                    最终金额
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                变更金额
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的金额" name="recharge[balance][money]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[balance][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="am-tab-panel am-padding-0" id="tab2">

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                补充库存商品
                            </label>
                            <div class="am-u-sm-8 am-u-end wrap-goods">
                                <select id="doc-select-1" name="recharge[points][goods_id]" onchange="goodsSku.call(this)">
                                    <option value="0">请选择商品</option>
                                    <?php if(!empty($goodsList)){ ?>
                                        <?php foreach($goodsList as $goods){ ?>
                                    <option value="<?= $goods['goods_id'] ?>"><?= $goods['goods_name'] ?></option>
                                            <?php } ?>
                                    <?php } ?>
                                </select>
                                <input class="ipt-goods-sku-id" type="hidden" name="recharge[points][goods_sku_id]" value="0">
                            </div>
                        </div>

                        <div class="am-form-group wrap-attr">
                            <label class="am-u-sm-3 am-form-label">
                                规格
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <select class="doc-select-attr" name="recharge[points][goods_sku_id2]" onchange="chooseGoods.call(this)">
                                    <option value="0">请选择规格</option>
                                </select>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前库存
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static stock-wrap">0</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
<!--                                <label class="am-radio-inline">-->
<!--                                    <input type="radio" name="recharge[points][mode]" value="dec" data-am-ucheck>-->
<!--                                    减少-->
<!--                                </label>-->
<!--                                <label class="am-radio-inline">-->
<!--                                    <input type="radio" name="recharge[points][mode]" value="final" data-am-ucheck>-->
<!--                                    最终库存-->
<!--                                </label>-->
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值数量
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="recharge[points][value]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[points][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="am-tab-panel am-padding-0" id="tab3">

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label form-require">
                                充值后会员等级
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <select name="recharge[grade][grade_id]"
                                        data-am-selected="{btnSize: 'sm', placeholder: '请选择会员等级'}">
                                    <?php foreach ($gradeList as $item): ?>
                                        <option value="<?= $item['grade_id'] ?>"
                                            <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                补充库存商品
                            </label>
                            <div class="am-u-sm-8 am-u-end wrap-goods">
                                <select id="doc-select-1" name="recharge[grade][goods_id]" onchange="goodsSku.call(this)">
                                    <option value="0">请选择商品</option>
                                    <?php if(!empty($goodsList)){ ?>
                                        <?php foreach($goodsList as $goods){ ?>
                                            <option value="<?= $goods['goods_id'] ?>"><?= $goods['goods_name'] ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <input class="ipt-goods-sku-id" type="hidden" name="recharge[grade][goods_sku_id]" value="0">
                            </div>
                        </div>

                        <div class="am-form-group wrap-attr">
                            <label class="am-u-sm-3 am-form-label">
                                规格
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <select class="doc-select-attr" name="recharge[grade][goods_sku_id2]" onchange="chooseGoods.call(this)">
                                    <option value="0">请选择规格</option>
                                </select>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前库存
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static stock-wrap">0</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[grade][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <!--                                <label class="am-radio-inline">-->
                                <!--                                    <input type="radio" name="recharge[points][mode]" value="dec" data-am-ucheck>-->
                                <!--                                    减少-->
                                <!--                                </label>-->
                                <!--                                <label class="am-radio-inline">-->
                                <!--                                    <input type="radio" name="recharge[points][mode]" value="final" data-am-ucheck>-->
                                <!--                                    最终库存-->
                                <!--                                </label>-->
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值数量
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="recharge[grade][value]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[grade][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="am-tab-panel am-padding-0" id="tab4">

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                补充库存商品
                            </label>
                            <div class="am-u-sm-8 am-u-end wrap-goods">
                                <select id="doc-select-1" name="recharge[transfer][goods_id]" onchange="goodsSku.call(this)">
                                    <option value="0">请选择商品</option>
                                    <?php if(!empty($goodsList)){ ?>
                                        <?php foreach($goodsList as $goods){ ?>
                                            <option value="<?= $goods['goods_id'] ?>"><?= $goods['goods_name'] ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <input class="ipt-goods-sku-id" type="hidden" name="recharge[transfer][goods_sku_id]" value="0">
                            </div>
                        </div>

                        <div class="am-form-group wrap-attr">
                            <label class="am-u-sm-3 am-form-label">
                                规格
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <select class="doc-select-attr" name="recharge[transfer][goods_sku_id2]" onchange="chooseGoods.call(this)">
                                    <option value="0">请选择规格</option>
                                </select>
                            </div>
                        </div>

                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前库存
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static stock-wrap">0</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[transfer][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <!--                                <label class="am-radio-inline">-->
                                <!--                                    <input type="radio" name="recharge[points][mode]" value="dec" data-am-ucheck>-->
                                <!--                                    减少-->
                                <!--                                </label>-->
                                <!--                                <label class="am-radio-inline">-->
                                <!--                                    <input type="radio" name="recharge[points][mode]" value="final" data-am-ucheck>-->
                                <!--                                    最终库存-->
                                <!--                                </label>-->
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值数量
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="recharge[transfer][value]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[transfer][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>

<script id="tpl-rebate" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">
                        商品
                    </label>
                    <div class="am-u-sm-8 am-u-end wrap-goods">
                        <select id="doc-select-1" name="goods_id" onchange="goodsSku.call(this)">
                            <option value="0">请选择商品</option>
                            <?php if(!empty($goodsList)){ ?>
                                <?php foreach($goodsList as $goods){ ?>
                                    <option value="<?= $goods['goods_id'] ?>"><?= $goods['goods_name'] ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <input class="ipt-goods-sku-id" type="hidden" name="goods_sku_id" value="0">
                    </div>
                </div>

                <div class="am-form-group wrap-attr">
                    <label class="am-u-sm-3 am-form-label">
                        规格
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select class="doc-select-attr" name="goods_sku_id2" onchange="chooseGoods.call(this)">
                            <option value="0">请选择规格</option>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 商品数量 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="num" placeholder="请输入商品数量"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 备注 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="remark" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script src="assets/common/js/ddsort.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script src="assets/common/js/vue.min.js"></script>
<script>
    $(function () {

        /**
         * 账户充值
         */
        $('.j-recharge').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '用户充值'
                , area: '560px'
                , content: template('tpl-recharge', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/recharge') ?>',
                        data: {
                            user_id: data.id,
                            source: $tabs.data('amui.tabs').activeIndex
                        }
                    });
                    return true;
                }
            });
        });

        /**
         * 修改会员等级
         */
        $('.j-grade').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '修改会员等级'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/grade') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });

        $('.j-transfer-stock').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '修改会员等级'
                , area: '460px'
                , content: template('tpl-transfer-stock', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/fileTransferStock') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });

        /**
         * 返利
         */
        $('.j-rebate').on('click', function () {
            var data = $(this).data();
            $('#wrap-tbody').attr('data-cur-user-id', data.id)
            $.showModal({
                title: '成为战略董事'
                , area: '460px'
                , content: template('tpl-rebate', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user.team/bestrategy') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });

        $('.j-team-freeze').on('click', function () {
            var data = $(this).data();

            layer.confirm('确定冻结该用户的团队吗？', function (index) {
                $.post('<?= url('user.team/freeze') ?>', data, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

        /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.dropdown();
        $dropdown.on('click', 'li a', function () {
            var $this = $(this);
            var id = $this.parent().parent().data('id');
            var type = $this.data('type');
            if (type === 'delete') {
                layer.confirm('删除后不可恢复，确定要删除吗？', function (index) {
                    $.post("index.php?s=/store/apps.dealer.user/delete", {dealer_id: id}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url)
                            : $.show_error(result.msg);
                    });
                    layer.close(index);
                });
            }
            $dropdown.dropdown('close');
        });

        // 删除元素
        var url = "<?= url('user/delete') ?>";
        $('.j-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');



    });

    function chooseGoods(goods_sku_id){
        goods_sku_id = goods_sku_id||$(this).val()
        var user_id = $('#wrap-tbody').data('cur-user-id');
        $('input.ipt-goods-sku-id').val(goods_sku_id)
        if(goods_sku_id){
            $.post('index.php?s=/store/user/getUserGoodsStock', {goods_sku_id, user_id}, function(res){
                if(res.code == 1){
                    $('.stock-wrap').text(res.data)
                }else{
                    layer.msg(res.msg)
                }
            }, 'json')
        }
    }

    function chooseGoods2(goods_sku_id){
        goods_sku_id = goods_sku_id||$(this).val()
        $('input.ipt-goods-sku-id').val(goods_sku_id)
    }

    function goodsSku(){
        var goods_id = $(this).val()
        if(goods_id){
            var ipt = $('input.ipt-goods-sku-id');
            $.post('index.php?s=/store/goods/getGoodsSpec', {goods_id}, function(res){
                if(res.code == 1){
                    if(res.data.spec_id == 0){
                        var html_ = '';
                        $.each(res.data.list, function(i,v){
                            html_ += '<option value="'+v.goods_sku_id+'">'+v.attr+'</option>';
                        })
                        $('.wrap-attr').show();
                        $(".doc-select-attr").html(html_);
                        ipt.val(res.data.list[0]['goods_sku_id']);
                        chooseGoods(res.data.list[0]['goods_sku_id']);
                    }else{
                        $('.wrap-attr').hide();
                        ipt.val(res.data.spec_id)
                        chooseGoods(res.data.spec_id);
                    }
                }else{
                    layer.msg(res.msg)
                }
            }, 'json')
        }
    }

    function goodsSku2(){
        var goods_id = $(this).val()
        if(goods_id){
            var ipt = $('input.ipt-goods-sku-id');
            $.post('index.php?s=/store/goods/getGoodsSpec', {goods_id}, function(res){
                if(res.code == 1){
                    if(res.data.spec_id == 0){
                        var html_ = '';
                        $.each(res.data.list, function(i,v){
                            html_ += '<option value="'+v.goods_sku_id+'">'+v.attr+'</option>';
                        })
                        $('.wrap-attr').show();
                        $(".doc-select-attr").html(html_);
                        ipt.val(res.data.list[0]['goods_sku_id']);
                        chooseGoods2(res.data.list[0]['goods_sku_id']);
                    }else{
                        $('.wrap-attr').hide();
                        ipt.val(res.data.spec_id)
                        chooseGoods2(res.data.spec_id);
                    }
                }else{
                    layer.msg(res.msg)
                }
            }, 'json')
        }
    }

    /**
     * 将选中的user_id保存到tbody
     */
    function fillIptUserId(){
        $('#wrap-tbody').attr('data-cur-user-id', $(this).data('id'))
    }

</script>

