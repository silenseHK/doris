<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">营养师管理团队</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">选择团队 </label>
                                <div class="am-u-sm-9">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="team_user_ids[]" <?php if(in_array(0, $check_ids)):?>checked<?php endif;?>
                                               value="0" data-am-ucheck>
                                        <div>
                                            <p>公司直属团队</p>
                                        </div>
                                    </label>
                                    <?php foreach($team_list as $key => $v):?>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="team_user_ids[]" <?php if(in_array($v['user_id'], $check_ids)):?>checked<?php endif;?>
                                               value="<?=$v['user_id']?>" data-am-ucheck>
                                        <div>
                                            <p><?= $v['nickName'] ?></p>
                                            <p><?= $v['mobile'] ?></p>
                                            <img style="width:80px;" src="<?= $v['avatarUrl'] ?>" />
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <input type="hidden" name="store_user_id" value="<?= $store_user_id ?>" />

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">

                                    <button type="submit" class="j-submit am-btn am-btn-secondary"> 提交
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
{{include file="layouts/_template/file_library" /}}
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
