<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">财务设置</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :lg="16">
                                <el-container>
                                    <el-main style="padding-left:0; padding-right: 0;">
                                        <el-tabs type="border-card">
                                            <el-tab-pane label="提现设置">
                                                <el-form :label-position="labelPosition" label-width="100px" :model="formLabelAlign">
                                                    <el-form-item style="max-width:300px" label="提现手续费">
                                                        <el-input @blur="checkServiceCharge" placeholder="请输入手续费" v-model="formLabelAlign.withdraw.service_charge">
                                                            <template slot="append">%</template>
                                                        </el-input>
                                                    </el-form-item>

                                                    <el-form-item style="max-width:300px" label="是否开启手续费">
                                                        <el-switch
                                                                v-model="formLabelAlign.withdraw.service_charge_status"
                                                                active-text="开启"
                                                                inactive-text="关闭"
                                                                :active-value=10
                                                                :inactive-value=20>
                                                        </el-switch>
                                                    </el-form-item>

                                                    <el-form-item style="max-width:300px" label="">
                                                        <el-button type="primary" @click="editWithdrawSetting" plain>提交修改</el-button>
                                                    </el-form-item>
                                                </el-form>
                                            </el-tab-pane>
                                        </el-tabs>
                                    </el-main>

                                </el-container>
                            </el-col>
                        </el-row>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>
<script>
    var App;
    $(function () {

        App = new Vue({
            el: '#my-table',
            data: {
                labelPosition: 'top',
                formLabelAlign: {
                    withdraw:{
                        service_charge: <?= $withdraw_setting['service_charge'] ?>,
                        service_charge_status: <?= $withdraw_setting['service_charge_status'] ?>,
                    }
                }
            },
            methods:{
                editWithdrawSetting(){
                    let that = this
                    let {service_charge, service_charge_status} = this.formLabelAlign.withdraw
                    if(service_charge < 0){
                        that.$message({
                            showClose: true,
                            message: '请输入正确的手续费',
                            type: 'warning'
                        });
                        return false;
                    }
                    $.post("<?= url('finance.setting/edit') ?>", {service_charge, service_charge_status}, (res)=>{
                        if(res.code == 1){
                            that.$message({
                                message: '操作成功',
                                type: 'success'
                            });
                        }else{
                            that.$message({
                                message: res.msg,
                                type: 'error'
                            });
                        }
                    }, 'json')
                },
                checkServiceCharge(){
                    let {service_charge} = this.formLabelAlign.withdraw
                    let that = this
                    service_charge = $.trim(service_charge)
                    service_charge = parseFloat(service_charge)
                    if(isNaN(service_charge) || service_charge < 0){
                        that.$message({
                            showClose: true,
                            message: '请输入正确的手续费',
                            type: 'warning'
                        });
                        this.formLabelAlign.withdraw.service_charge = 0;
                    }else{
                        service_charge = service_charge.toFixed(2)
                        this.formLabelAlign.withdraw.service_charge = service_charge
                    }
                }
            },
            computed:{

            },
            created: function(){

            }
        });


    });
</script>

