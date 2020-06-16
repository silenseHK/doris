<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑课程分类</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="title"
                                           value="<?= $info['title'] ?>" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">父级分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="pid"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择', maxHeight: 400}">
                                        <option value=""></option>
                                        <option value="0" <?php if($info['pid'] == 0):?>selected<?php endif; ?>>顶级分类</option>
                                        <?php if (isset($cate_list)): foreach ($cate_list as $item): ?>
                                            <option value="<?= $item['lesson_cate_id'] ?>" <?php if($info['pid'] == $item['lesson_cate_id']):?>selected<?php endif; ?> ><?= $item['title'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="sort"
                                           value="<?= $info['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">显示状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="is_show" value="1" data-am-ucheck <?= $info['is_show']==1?"checked":"" ?>>
                                        显示
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="is_show" value="0" data-am-ucheck <?= $info['is_show']==0?"checked":"" ?>>
                                        隐藏
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="lesson_cate_id" value="<?= $info['lesson_cate_id'] ?>">

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

<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
