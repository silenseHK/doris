<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>
    .button-new-tag {
        margin-left: 10px;
        height: 32px;
        line-height: 30px;
        padding-top: 0;
        padding-bottom: 0;
    }
    .input-new-tag {
        width: 90px;
        margin-left: 10px;
        vertical-align: bottom;
    }
    .el-tag + .el-tag {
        margin-left: 10px;
    }
</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">首页部分布局</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table" v-cloak>
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-row>
                                        <el-divider content-position="left">优质模块</el-divider>
                                    </el-row>
                                    <el-header>
                                        <el-row :gutter="10" type="flex" align="middle">
                                            <el-col :span="1">
                                                商品：
                                            </el-col>
                                            <el-col :span="20">
                                                <el-tag v-for="(item, key) in productTabs" :key="key" size="medium" closable effect="plain" type="warning" @close="delTab(1, key)" @click="editTab(1, key)">{{item}}</el-tag>
                                                <el-input
                                                        class="input-new-tag"
                                                        v-if="inputVisible1"
                                                        v-model="inputValue"
                                                        ref="saveTagInput"
                                                        size="small"
                                                        @keyup.enter.native="handleInputConfirm(1)"
                                                        @blur="handleInputConfirm(1)"
                                                >
                                                </el-input>
                                                <el-input
                                                        class="input-new-tag"
                                                        v-if="inputEditVisible1"
                                                        v-model="inputEditValue"
                                                        ref="saveTagInput"
                                                        size="small"
                                                        @keyup.enter.native="handleInputEditConfirm(1)"
                                                        @blur="handleInputEditConfirm(1)"
                                                >
                                                </el-input>
                                                <el-button v-else class="button-new-tag" size="small" @click="showInput(1)">+ 商品</el-button>
                                            </el-col>
                                        </el-row>
                                        <el-row style="margin-top: 10px;" :gutter="10" type="flex" align="middle">
                                            <el-col :span="1">
                                                属性：
                                            </el-col>
                                            <el-col :span="20">
                                                <el-tag v-for="(item, key) in attrTabs" :key="key" size="medium" closable effect="plain" type="warning" @close="delTab(2, key)" @click="editTab(2, key)">{{item}}</el-tag>
                                                <el-input
                                                        class="input-new-tag"
                                                        v-if="inputVisible2"
                                                        v-model="inputValue"
                                                        ref="saveTagInput"
                                                        size="small"
                                                        @keyup.enter.native="handleInputConfirm(2)"
                                                        @blur="handleInputConfirm(2)"
                                                >
                                                </el-input>
                                                <el-input
                                                        class="input-new-tag"
                                                        v-if="inputEditVisible2"
                                                        v-model="inputEditValue"
                                                        ref="saveTagInput"
                                                        size="small"
                                                        @keyup.enter.native="handleInputEditConfirm(2)"
                                                        @blur="handleInputEditConfirm(2)"
                                                >
                                                </el-input>
                                                <el-button v-else class="button-new-tag" size="small" @click="showInput(2)">+ 属性</el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-main>
                                        <el-row v-show="productTabs.length > 0 && attrTabs.length > 0 ">
                                            <el-col :span="1">
                                                属性值：
                                            </el-col>
                                            <el-col :span="2">
                                                <el-row>
                                                    <el-col :span="24" style="border:1px solid #ddd;text-align: center;padding:8px 0; height:37px; overflow: hidden;">
                                                        <span>商品/属性</span>
                                                    </el-col>
                                                </el-row>
                                                <el-row v-for="(it, k) in productTabs" :key="k">
                                                    <el-col :span="24" style="border:1px solid #ddd;text-align: center;padding:8px 0;height:90px; overflow: hidden;">
                                                        <span>{{it}}</span>
                                                    </el-col>
                                                </el-row>
                                            </el-col>
                                            <el-col :span="18">
                                                <el-row type="flex" align="middle">
                                                    <el-col v-for="(item, key) in attrTabs" :key="key" :span="4" style="border:1px solid #ddd;text-align: center;padding:8px 0;height:37px; overflow: hidden;" >
                                                        {{item}}
                                                    </el-col>
                                                </el-row>
                                                <!-- 表格 -->
                                                <el-row>
                                                    <el-row v-for="(it, k) in productTabs" :key="k">
                                                        <el-col v-for="(item, key) in attrTabs" :key="key" :span="4" style="border:1px solid #ddd;text-align: center;padding:8px 0;height:90px; overflow: hidden;">
                                                            <el-input v-model="table[k][key]" type="textarea"
                                                                      :rows="3" placeholder="请输入内容"></el-input>
                                                        </el-col>
                                                    </el-row>
                                                </el-row>
                                            </el-col>
                                        </el-row>
                                        <el-row style="margin-top:10px;">
                                            <el-col :span="1" style="height:1px;">

                                            </el-col>
                                            <el-col :span="2">
                                                <el-button type="primary" plain @click="submit">确定修改</el-button>
                                            </el-col>
                                        </el-row>
                                    </el-main>
                                </el-container>
                                <el-container>
                                    <el-row>
                                        <el-divider content-position="left">印象模块</el-divider>
                                    </el-row>
                                    <el-header style="height:auto;">
                                        <el-form label-position="top" label-width="80px" :model="impression_form">
                                            <el-row :gutter="10" >
                                                <el-col :span="4">
                                                    <el-form-item label="内容">
                                                        <el-input type="textarea" maxlength="100"
                                                                  show-word-limit v-model="impression_form.content"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="2">
                                                    <el-form-item label="作者">
                                                        <el-input v-model="impression_form.author"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="2">
                                                    <el-form-item label="排序">
                                                        <el-input v-model="impression_form.sort"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="2">
                                                    <el-form-item label="--">
                                                        <el-button type="primary" plain size="medium" @click="addImpression">添加印象</el-button>
                                                    </el-form-item>
                                                </el-col>
                                            </el-row>
                                        </el-form>
                                    </el-header>
                                    <el-main>
                                        <el-table
                                                :data="tableData"
                                                height="250"
                                                border
                                                style="width: 100%">
                                            <el-table-column
                                                    prop="impression_id"
                                                    label="ID"
                                                    width="180">
                                            </el-table-column>
                                            <el-table-column
                                                    prop="author"
                                                    label="作者">
                                            </el-table-column>
                                            <el-table-column
                                                    prop="content"
                                                    label="详情">
                                            </el-table-column>
                                            <el-table-column
                                                    label="排序">
                                                <template slot-scope="scope">
                                                    <el-input style="width:150px;" type="number" min="1" placeholder="请输入内容" v-model="scope.row.sort">
                                                        <template slot="append">
                                                            <el-button type="success" icon="el-icon-check" @click="editSort(scope)" circle></el-button>
                                                        </template>
                                                    </el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    label="操作">
                                                <template slot-scope="scope">
                                                    <el-button type="primary" plain size="medium" @click="delImpression(scope)">删除</el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </el-main>
                                </el-container>
                                <el-container>
                                    <el-row>
                                        <el-divider content-position="left">营养师模块</el-divider>
                                    </el-row>
                                    <el-header style="height:auto">
                                        <el-button type="primary" plain size="medium" @click="showAddDietitian">添加营养师</el-button>
                                    </el-header>
                                    <el-main>
                                        <el-row :gutter="20">
                                            <el-col v-for="(item, key) in dietitianData" :span="3" :key="key">
                                                <el-card :body-style="{ padding: '0px' }" style="position: relative;">
                                                    <el-image
                                                            style="width: 100%; height:260px;"
                                                            :src="item.image.file_path"
                                                            fit="contain"></el-image>
                                                    <div style="padding: 14px;">
                                                        <span>{{item.name}}</span>
                                                        <div style="margin-top:10px;">
                                                            <el-link type="info">{{item.title}}</el-link>
                                                        </div>
                                                        <div style="margin-top:10px;">
                                                            <el-link v-for="(it, k) in item.description" :key="k" type="success">{{it}}</el-link>
                                                        </div>
                                                        <div style="margin-top:10px;">
                                                            <el-input style="width:150px;" type="number" min="1" placeholder="请输入内容" v-model="item.sort">
                                                                <template slot="append">
                                                                    <el-button type="success" icon="el-icon-check" @click="editDietitianSort(item)" circle></el-button>
                                                                </template>
                                                            </el-input>
                                                        </div>
                                                    </div>
                                                    <el-row :gutter="10" style="position:absolute;right:10px;top:0;" type="flex" align="middle" justify="end">
                                                        <el-col :span="10">
                                                            <el-button type="warning" icon="el-icon-edit" size="mini" circle @click="showDietitianWrap(item, key)"></el-button>
                                                        </el-col>
                                                        <el-col :span="10">
                                                            <el-button type="warning" icon="el-icon-delete" size="mini" circle @click="delDietitian(key)"></el-button>
                                                        </el-col>
                                                    </el-row>
                                                </el-card>
                                            </el-col>
                                        </el-row>
                                    </el-main>
                                </el-container>
                                <el-containner>
                                    <el-row>
                                        <el-divider content-position="left">词条模块</el-divider>
                                    </el-row>
                                    <el-header style="height:auto">
                                        <el-form label-position="top" label-width="80px" :model="entry_form">
                                            <el-row :gutter="10" >
                                                <el-col :span="2">
                                                    <el-form-item label="关键词">
                                                        <el-input v-model="entry_form.keywords"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="2">
                                                    <el-form-item label="英文别称">
                                                        <el-input v-model="entry_form.alias"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="4">
                                                    <el-form-item label="内容">
                                                        <el-input type="textarea" maxlength="255"
                                                                  show-word-limit v-model="entry_form.content"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="2">
                                                    <el-form-item label="排序">
                                                        <el-input v-model="entry_form.sort"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="2">
                                                    <el-form-item label="--">
                                                        <el-button type="primary" plain size="medium" @click="addEntry">添加词条</el-button>
                                                    </el-form-item>
                                                </el-col>
                                            </el-row>
                                        </el-form>
                                    </el-header>
                                    <el-main>
                                        <el-table
                                                :data="entryData"
                                                height="400"
                                                border
                                                style="width: 100%">
                                            <el-table-column
                                                    prop="entry_id"
                                                    label="ID"
                                                    width="180">
                                            </el-table-column>
                                            <el-table-column
                                                    prop="keywords"
                                                    label="词条">
                                            </el-table-column>
                                            <el-table-column
                                                    prop="alias"
                                                    label="英语别名">
                                            </el-table-column>
                                            <el-table-column
                                                    prop="content"
                                                    label="词条详情">
                                            </el-table-column>
                                            <el-table-column
                                                    label="排序">
                                                <template slot-scope="scope">
                                                    <el-input style="width:150px;" type="number" min="1" placeholder="请输入内容" v-model="scope.row.sort">
                                                        <template slot="append">
                                                            <el-button type="success" icon="el-icon-check" @click="editEntrySort(scope)" circle></el-button>
                                                        </template>
                                                    </el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    label="操作">
                                                <template slot-scope="scope">
                                                    <el-button type="primary" plain size="medium" @click="delEntry(scope)">删除</el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </el-main>
                                </el-containner>
                            </el-col>
                        </el-row>

                        <el-drawer
                            title="添加营养师"
                            :visible.sync="show_dietitian"
                            direction="rtl">
                            <el-form label-position="top" label-width="100px" :model="dietitian_form" style="padding:15px;">
                                <el-form-item label="姓名">
                                    <el-input v-model="dietitian_form.name"></el-input>
                                </el-form-item>
                                <el-form-item label="职称">
                                    <el-input v-model="dietitian_form.title"></el-input>
                                </el-form-item>
                                <el-form-item label="描述">
                                    <el-tag
                                            :key="tag"
                                            v-for="tag in dietitian_form.description"
                                            closable
                                            :disable-transitions="false"
                                            @close="handleCloseDietitian(tag)">
                                        {{tag}}
                                    </el-tag>
                                    <el-input
                                            class="input-new-tag"
                                            v-if="inputVisibleDietitian"
                                            v-model="inputValueDietitian"
                                            ref="saveTagInput"
                                            size="small"
                                            @keyup.enter.native="handleInputConfirmDietitian"
                                            @blur="handleInputConfirmDietitian"
                                    >
                                    </el-input>
                                    <el-button v-else class="button-new-tag" size="small" @click="showInputDietitian">+ 描述</el-button>
                                </el-form-item>
                                <el-form-item label="图片">
                                    <el-row :gutter="10" align="middle" type="flex">
                                        <el-col :span="6" v-show="dietitian_form.image_id > 0">
                                            <el-image
                                                    style="width: 100px; height: 100px"
                                                    :src="dietitian_form.image"
                                                    fit="contain"></el-image>
                                        </el-col>
                                        <el-col :span="6" id="cover-wrap">
                                            <el-button type="primary" plain size="medium" @click="uploadImg(1)">上传图片</el-button>
                                        </el-col>
                                    </el-row>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-button type="primary" plain size="medium" @click="addDietitian">提交</el-button>
                                </el-form-item>
                            </el-form>
                        </el-drawer>

                        <el-drawer
                                title="编辑营养师"
                                :visible.sync="edit_dietitian"
                                direction="rtl">
                            <el-form label-position="top" label-width="100px" :model="edit_dietitian_data" style="padding:15px;">
                                <el-form-item label="姓名">
                                    <el-input v-model="edit_dietitian_data.name"></el-input>
                                </el-form-item>
                                <el-form-item label="职称">
                                    <el-input v-model="edit_dietitian_data.title"></el-input>
                                </el-form-item>
                                <el-form-item label="描述">
                                    <el-tag
                                            :key="tag"
                                            v-for="tag in edit_dietitian_data.description"
                                            closable
                                            :disable-transitions="false"
                                            @close="handleCloseDietitian2(tag)">
                                        {{tag}}
                                    </el-tag>
                                    <el-input
                                            class="input-new-tag"
                                            v-if="inputVisibleDietitian2"
                                            v-model="inputValueDietitian2"
                                            ref="saveTagInput"
                                            size="small"
                                            @keyup.enter.native="handleInputConfirmDietitian2"
                                            @blur="handleInputConfirmDietitian2"
                                    >
                                    </el-input>
                                    <el-button v-else class="button-new-tag" size="small" @click="showInputDietitian2">+ 描述</el-button>
                                </el-form-item>
                                <el-form-item label="图片">
                                    <el-row :gutter="10" align="middle" type="flex">
                                        <el-col :span="6" v-show="edit_dietitian_data.image_id > 0">
                                            <el-image
                                                    style="width: 100px; height: 100px"
                                                    :src="edit_dietitian_data.image"
                                                    fit="contain"></el-image>
                                        </el-col>
                                        <el-col :span="6" id="cover-wrap">
                                            <el-button type="primary" plain size="medium" @click="uploadImg(2)">上传图片</el-button>
                                        </el-col>
                                    </el-row>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-button type="primary" plain size="medium" @click="editDietitian">提交</el-button>
                                </el-form-item>
                            </el-form>
                        </el-drawer>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>
<script>
    var App;
    $(function () {

        App = new Vue({
            el: '#my-table',
            data: {
                inputEditVisible1: false,
                inputEditVisible2: false,
                inputEditValue: '',
                inputEditIndex: 0,
                inputVisible1: false,
                inputValue: '',
                inputVisible2: false,
                inputVisibleDietitian: false,
                inputVisibleDietitian2: false,
                inputValueDietitian: '',
                inputValueDietitian2: '',
                productTabs: <?= json_encode($spec['product']) ?>,
                attrTabs: <?= json_encode($spec['attr']) ?>,
                table: <?= json_encode($spec['table']) ?>,
                input:'',
                impression_form: {
                    content: '',
                    author: '',
                    sort: 9999
                },
                tableData: <?= json_encode($impression) ?>,
                show_dietitian: false,
                dietitian_form: {
                    title: '',
                    name: '',
                    description: [],
                    image: '',
                    image_id: 0
                },
                dietitianData: <?= json_encode($dietitian) ?>,
                edit_dietitian_data: {},
                edit_dietitian: false,
                entry_form: {
                    keywords: '',
                    alias: '',
                    content: '',
                    sort: 9999
                },
                entryData: <?= json_encode($entry) ?>,
            },
            methods:{
                delTab(flag, index){
                    let field = flag == 1? 'productTabs' : 'attrTabs';
                    this[field].splice(index, 1);
                    this.initTable();
                },
                showInput(flag){
                    let field = 'inputVisible' + flag;
                    this[field] = true;
                },
                handleClose(tag) {
                    this.dynamicTags.splice(this.dynamicTags.indexOf(tag), 1);
                },
                handleCloseDietitian(tag){
                    this.dietitian_form.description.splice(this.dietitian_form.description.indexOf(tag), 1);
                },
                handleCloseDietitian2(tag){
                    this.edit_dietitian_data.description.splice(this.edit_dietitian_data.description.indexOf(tag), 1);
                },
                showInputDietitian(){
                    this.inputVisibleDietitian = true;
                },
                showInputDietitian2(){
                    this.inputVisibleDietitian2 = true;
                },
                handleInputConfirm(flag) {
                    let inputValue = this.inputValue;
                    if (inputValue) {
                        if(flag == 1)
                            this.productTabs.push(inputValue);
                        else
                            this.attrTabs.push(inputValue);
                    }
                    this.initTable();
                    let field = 'inputVisible' + flag;
                    this[field] = false;
                    this.inputValue = '';
                },

                editTab(flag, index){
                    this.inputEditIndex = index;
                    let field = 'inputEditVisible' + flag;
                    let inputValue;
                    this[field] = true;
                    if(flag == 1){
                        inputValue = this.productTabs[index]
                    }else{
                        inputValue = this.attrTabs[index]
                    }
                    this.inputEditValue = inputValue;
                },

                handleInputEditConfirm(flag){
                    let inputValue = this.inputEditValue;
                    let field = 'inputEditVisible' + flag;
                    this[field] = false;
                    this.inputEditValue = '';

                    console.log(inputValue, this.inputEditIndex)
                    if(inputValue){
                        if(flag == 1)
                            this.productTabs[this.inputEditIndex] = inputValue;
                        else
                            this.attrTabs[this.inputEditIndex] = inputValue;
                    }
                },

                handleInputConfirmDietitian(){
                    let val = this.inputValueDietitian;
                    if(val)
                        this.dietitian_form.description.push(val);
                    this.inputVisibleDietitian = false;
                    this.inputValueDietitian = '';
                },

                handleInputConfirmDietitian2(){
                    let val = this.inputValueDietitian2;
                    if(val)
                        this.edit_dietitian_data.description.push(val);
                    this.inputVisibleDietitian2 = false;
                    this.inputValueDietitian2 = '';
                },

                initTable(){
                    let [product, attr, table] = [this.productTabs, this.attrTabs, this.table];
                    let new_table = [];
                    product.forEach((v, k)=>{
                        new_table[k] = [];
                        attr.forEach((vv, kk)=>{
                            new_table[k][kk] = '';
                            if(table.length > k){
                                if(table[k].length >= kk){
                                    new_table[k][kk] = table[k][kk];
                                }
                            }
                        })
                    })
                    this.table = new_table;
                    // console.log(new_table);
                },

                submit(){
                    let [product, attr, table] = [this.productTabs, this.attrTabs, this.table];
                    if(product.length <= 0 || attr.length<= 0){
                        this.$message({
                            showClose: true,
                            message: '请将数据补充完整',
                            type: 'error'
                        });
                        return false;
                    }
                    table.forEach((v, k)=>{
                        v.forEach((vv, kk)=>{
                            if($.trim(vv) == ''){
                                this.$message({
                                    showClose: true,
                                    message: '请将属性值补充完整',
                                    type: 'error'
                                });
                                return false;
                            }
                        })
                    })
                    let that = this;
                    $.post("<?= url('wxapp.page/homeQuality') ?>", {product, attr, table}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                addImpression(){
                    let data = this.impression_form;
                    if($.trim(data.content) == ''){
                        this.$message({
                            showClose: true,
                            message: '请填写印象内容',
                            type: 'error'
                        });
                        return false;
                    }
                    if($.trim(data.author) == ''){
                        this.$message({
                            showClose: true,
                            message: '请填写印象作者',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('wxapp.page/addImpression') ?>", data, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            data['impression_id'] = res.data;
                            that.tableData.push(data);
                            that.impression_form = {
                                author : '',
                                content : '',
                                sort : 9999
                            };
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json');
                },

                addEntry(){
                    let data = this.entry_form;
                    if($.trim(data.keywords) == ''){
                        this.$message({
                            showClose: true,
                            message: '请填写词条',
                            type: 'error'
                        });
                        return false;
                    }
                    if($.trim(data.content) == ''){
                        this.$message({
                            showClose: true,
                            message: '请填写词条内容',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('wxapp.page/addEntry') ?>", data, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            data['entry_id'] = res.data;
                            that.entryData.push(data);
                            that.entry_form = {
                                keywords: '',
                                alias: '',
                                content: '',
                                sort: 9999
                            };
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json');
                },

                delImpression(scope){
                    let that = this;
                    $.post("<?= url('wxapp.page/delImpression') ?>", {impression_id:scope.row.impression_id}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            that.tableData.splice(scope.$index, 1);
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                editSort(scope){
                    let that = this;
                    let {sort, impression_id} = scope.row;
                    $.post("<?= url('wxapp.page/editSort') ?>", {sort, impression_id}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                editEntrySort(scope){
                    let that = this;
                    let {sort, entry_id} = scope.row;
                    $.post("<?= url('wxapp.page/editEntrySort') ?>", {sort, entry_id}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                delEntry(scope){
                    let that = this;
                    $.post("<?= url('wxapp.page/delEntry') ?>", {entry_id:scope.row.entry_id}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            that.entryData.splice(scope.$index, 1);
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                uploadImg: function(flag){
                    let that = this;
                    $('#cover-wrap').selectImages({
                        multiple: false,
                        done: function (data) {
                            let field = flag == 1? 'dietitian_form' : 'edit_dietitian_data';
                            that[field].image_id = data[0]['file_id']
                            that[field].image = data[0]['file_path']
                        }
                    });
                },

                addDietitian(){
                    let that = this;
                    let data = this.dietitian_form;
                    if(!$.trim(data.name) || !$.trim(data.title) || !parseInt(data.image_id) || data.description.length <= 0){
                        this.$message({
                            showClose: true,
                            message: '请将营养师数据补充完整',
                            type: 'error'
                        });
                        return false;
                    }
                    $.post("<?= url('wxapp.page/addDietitian') ?>" , data, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            that.dietitian_form = {
                                name: '',
                                title: '',
                                description: [],
                                image_id: 0,
                                image: ''
                            }
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                editDietitian(){
                    let that = this;
                    let data = this.edit_dietitian_data;
                    if(!$.trim(data.name) || !$.trim(data.title) || !parseInt(data.image_id) || data.description.length <= 0){
                        this.$message({
                            showClose: true,
                            message: '请将营养师数据补充完整',
                            type: 'error'
                        });
                        return false;
                    }
                    $.post("<?= url('wxapp.page/editDietitian') ?>" , data, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            that.dietitianData[data.index].name = data.name;
                            that.dietitianData[data.index].title = data.title;
                            that.dietitianData[data.index].description = data.description;
                            that.dietitianData[data.index].image_id = data.image_id;
                            that.dietitianData[data.index].image.file_path = data.image;
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                showAddDietitian(){
                    this.show_dietitian = true;
                },

                editDietitianSort(item){
                    let {sort, dietitian_id} = item;
                    let that = this;
                    $.post("<?= url('wxapp.page/editDietitianSort') ?>", {sort, dietitian_id}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },

                showDietitianWrap(item, index){
                    let edit_dietitian_data = JSON.parse(JSON.stringify(item));
                    edit_dietitian_data.image = edit_dietitian_data.image.file_path
                    edit_dietitian_data.index = index
                    this.edit_dietitian_data = edit_dietitian_data;
                    this.edit_dietitian = true;
                },

                delDietitian(index){
                    let dietitian_id = this.dietitianData[index].dietitian_id;
                    let that = this;
                    $.post("<?= url('wxapp.page/delDietitian') ?>", {dietitian_id}, function(res){
                        let type = 'error';
                        if(res.code==1){
                            type = 'success';
                            that.dietitianData.splice(index,1)
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type
                        });
                    }, 'json')
                },
            },
            computed:{

            },
            created: function(){
                this.initTable();
            }
        });

    });

</script>

