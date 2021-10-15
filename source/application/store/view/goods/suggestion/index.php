<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">推荐套餐</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12" id="my-table">
                        <el-row :gutter="20">
                            <el-col :span="24">
                                <el-container>
                                    <el-header>
                                        <el-row :gutter="10">
                                            <el-col :span="2">
                                                <el-button type="primary" @click="addSuggestion()">添加套餐</el-button>
                                            </el-col>
                                        </el-row>
                                    </el-header>
                                    <el-row type="flex">
                                        <el-col :xl="24" :lg="24">
                                    <el-main>
                                        <template>
                                            <el-table
                                                    :data="list"
                                                    >
                                                <el-table-column
                                                        prop="suggestion_id"
                                                        label="ID"
                                                        width="60">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="title"
                                                        label="套餐名">
                                                </el-table-column>
                                                <el-table-column
                                                        width="200"
                                                        label="描述">
                                                    <template slot-scope="scope">
                                                        <el-popover
                                                                placement="top-start"
                                                                width="200"
                                                                trigger="hover"
                                                                :content="scope.row.description">
                                                            <el-link slot="reference" :underline="false" style="height:60px; line-height:20px; width: 200px; overflow-y: hidden;">{{scope.row.description}}</el-link>
                                                        </el-popover>

                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="套餐图">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 80px; height: 80px"
                                                                :src="scope.row.image?scope.row.image.file_path:''"
                                                                :preview-src-list="scope.row.image?[scope.row.image.file_path]:[]"
                                                                fit="fit">
                                                        </el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="商品图">
                                                    <template slot-scope="scope">
                                                        <el-image
                                                                style="width: 80px; height: 80px"
                                                                :src="scope.row.spec.image?scope.row.spec.image.file_path:''"
                                                                fit="fit">
                                                        </el-image>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="goods.goods_name"
                                                        label="商品名">
                                                </el-table-column>
                                                <el-table-column
                                                        label="商品规格">
                                                    <template slot-scope="scope">
                                                        <el-link v-for="(item, key) in scope.row.spec.sku_list">{{item.spec_name}} : {{item.spec_value}}</el-link>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="num"
                                                        label="数量">
                                                </el-table-column>
                                                <el-table-column
                                                        label="使用周期">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" align="middle">
                                                            <span>{{scope.row.min_cycle}}</span>
                                                            <span style="margin:0 10px;">-</span>
                                                            <span>{{scope.row.max_cycle}}</span>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="BMI区间">
                                                    <template slot-scope="scope">
                                                        <el-row type="flex" align="middle">
                                                            <span>{{scope.row.min_bmi}}</span>
                                                            <span style="margin:0 10px;">-</span>
                                                            <span>{{scope.row.max_bmi}}</span>
                                                        </el-row>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        prop="sort"
                                                        label="排序">
                                                </el-table-column>
                                                <el-table-column
                                                        prop="create_time"
                                                        label="创建时间">
                                                </el-table-column>
                                                <el-table-column
                                                        width="140"
                                                        label="状态">
                                                    <template slot-scope="scope">
                                                        <el-switch
                                                            v-model="scope.row.status"
                                                            active-value="1"
                                                            inactive-value="2"
                                                            active-text="显示"
                                                            inactive-text="隐藏"
                                                            @change="editStatus(scope)"
                                                        >
                                                        </el-switch>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column
                                                        label="操作">
                                                    <template slot-scope="scope">
                                                        <el-button type="text" size="small">
                                                            <el-link type="primary" :underline="false" :underline="false" @click="edit(scope)" target="_self">编辑</el-link>
                                                        </el-button>
                                                        <el-button type="text" size="small">
                                                            <el-link class="item-delete" type="primary" :underline="false" @click="del(scope)">删除</el-link>
                                                        </el-button>
                                                    </template>
                                                </el-table-column>
                                            </el-table>
                                        </template>

                                        <div class="am-u-lg-12 am-cf">
                                            <div class="am-fr" v-html="page"></div>
                                            <div class="am-fr pagination-total am-margin-right">
                                                <div class="am-vertical-align-middle">总记录：{{total}}</div>
                                            </div>
                                        </div>
                                    </el-main>
                                        </el-col>
                                    </el-row>
                                </el-container>
                            </el-col>
                        </el-row>

                        <el-drawer
                                :visible.sync="drawer"
                                :with-header="false">
                            <el-container>
                                <el-main>
                                    <el-form ref="form" label-width="100px" size="mini">
                                        <el-form-item label="套餐名">
                                            <el-input v-model="title" placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                        <el-form-item label="选择商品">
                                            <el-cascader :options="goods_options" :show-all-levels="false" @change="chooseGoods"></el-cascader>
                                        </el-form-item>
                                        <el-form-item label="描述">
                                            <el-input
                                                    type="textarea"
                                                    placeholder="请输入内容"
                                                    v-model="description"
                                                    maxlength="255"
                                                    show-word-limit
                                                    autosize
                                            >
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="商品数量">
                                            <el-input-number v-model="num" :min="1" :max="1000" label=""></el-input-number>
                                        </el-form-item>
                                        <el-form-item label="展示图片">
                                            <div v-if="image_id" class="demo-image__preview">
                                                <el-image
                                                        style="width: 100px; height: 100px"
                                                        :src="image"
                                                        :preview-src-list="[image]">
                                                </el-image>
                                            </div>
                                            <el-col style="margin-left: 0; padding-left:0; text-align: left;" :span="8" id="cover-wrap">
                                                <el-button type="primary" @click="uploadImg(1)" size="medium">上传<i class="el-icon-upload el-icon--right"></i></el-button>
                                            </el-col>
                                        </el-form-item>
                                        <el-form-item label="使用周期">
                                            <el-row type="flex" align="middle">
                                                <el-input-number v-model="min_cycle" :min="1" :max="1000" label=""></el-input-number>
                                                <span style="margin:0 10px;">-</span>
                                                <el-input-number v-model="max_cycle" :min="1" :max="1000" label=""></el-input-number>
                                                <span style="margin-left:10px;">天</span>
                                            </el-row>
                                        </el-form-item>
                                        <el-form-item label="BMI区间">
                                            <el-row type="flex" align="middle">
                                                <el-input-number v-model="min_bmi" :min="0" :max="1000" label=""></el-input-number>
                                                <span style="margin:0 10px;">-</span>
                                                <el-input-number v-model="max_bmi" :min="1" :max="1000" label=""></el-input-number>
                                            </el-row>
                                        </el-form-item>
                                        <el-form-item label="排序">
                                            <el-input-number v-model="sort" :min="1" :max="1000" label=""></el-input-number>
                                        </el-form-item>
                                        <el-form-item label="显示状态">
                                            <el-switch
                                                    v-model="status"
                                                    active-value="1"
                                                    inactive-value="2"
                                                    active-text="显示"
                                                    inactive-text="隐藏">
                                            </el-switch>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="primary" @click="doAddSuggestion()">提交</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-main>
                            </el-container>
                        </el-drawer>

                        <el-drawer
                                :visible.sync="editDrawer"
                                :with-header="false">
                            <el-container>
                                <el-main>
                                    <el-form ref="form" label-width="100px" size="mini">
                                        <el-form-item label="套餐名">
                                            <el-input v-model="edit_title" placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                        <el-form-item label="选择商品">
                                            <el-cascader v-model="edit_goods_row" :options="goods_options" :show-all-levels="false" @change="editChooseGoods"></el-cascader>
                                        </el-form-item>
                                        <el-form-item label="描述">
                                            <el-input
                                                    type="textarea"
                                                    placeholder="请输入内容"
                                                    v-model="edit_description"
                                                    maxlength="255"
                                                    show-word-limit
                                                    autosize
                                            >
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="商品数量">
                                            <el-input-number v-model="edit_num" :min="1" :max="1000" label=""></el-input-number>
                                        </el-form-item>
                                        <el-form-item label="展示图片">
                                            <div v-if="edit_image_id" class="demo-image__preview">
                                                <el-image
                                                        style="width: 100px; height: 100px"
                                                        :src="edit_image"
                                                        :preview-src-list="[edit_image]">
                                                </el-image>
                                            </div>
                                            <el-col style="margin-left: 0; padding-left:0; text-align: left;" :span="8" id="cover-wrap">
                                                <el-button type="primary" @click="uploadImg(2)" size="medium">上传<i class="el-icon-upload el-icon--right"></i></el-button>
                                            </el-col>
                                        </el-form-item>
                                        <el-form-item label="使用周期">
                                            <el-row type="flex" align="middle">
                                                <el-input-number v-model="edit_min_cycle" :min="1" :max="1000" label=""></el-input-number>
                                                <span style="margin:0 10px;">-</span>
                                                <el-input-number v-model="edit_max_cycle" :min="1" :max="1000" label=""></el-input-number>
                                                <span style="margin-left:10px;">天</span>
                                            </el-row>
                                        </el-form-item>
                                        <el-form-item label="BMI区间">
                                            <el-row type="flex" align="middle">
                                                <el-input-number v-model="edit_min_bmi" :min="0" :max="1000" label=""></el-input-number>
                                                <span style="margin:0 10px;">-</span>
                                                <el-input-number v-model="edit_max_bmi" :min="1" :max="1000" label=""></el-input-number>
                                            </el-row>
                                        </el-form-item>
                                        <el-form-item label="排序">
                                            <el-input-number v-model="edit_sort" :min="1" :max="1000" label=""></el-input-number>
                                        </el-form-item>
                                        <el-form-item label="显示状态">
                                            <el-switch
                                                    v-model="edit_status"
                                                    active-value="1"
                                                    inactive-value="2"
                                                    active-text="显示"
                                                    inactive-text="隐藏">
                                            </el-switch>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="primary" @click="doEditSuggestion">提交</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-main>
                            </el-container>
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
                page:'',
                cur_page: 1,
                list:[],
                total: 0,
                drawer: false,
                editDrawer: false,
                editData: {},
                idx: 0,
                goods_options: [
                    {
                        label: '一级',
                        value: 10001,
                        children: [
                            {
                                label: '二级',
                                value: 10001,
                            }
                        ]
                    }
                ],
                title: '',
                goods_sku_id: 0,
                goods_id: 0,
                description: '',
                min_cycle: 1,
                max_cycle: 1,
                min_bmi: 1,
                max_bmi: 1,
                num: 1,
                sort: 1,
                status: "1",
                edit_title: '',
                edit_sort: 1,
                edit_num: 1,
                edit_status: '1',
                edit_goods_sku_id: 0,
                edit_goods_id: 0,
                edit_image_id: 0,
                edit_image: '',
                suggestion_idx: 0,
                edit_goods_row: [0,0],
                edit_description: '',
                edit_min_cycle: 1,
                edit_min_bmi: 1,
                edit_max_cycle: 1,
                edit_max_bmi: 1,
                image_id: 0,
                image: ''
            },
            methods:{
                doAddSuggestion: function(){
                    let [title, sort, goods_id, goods_sku_id, num, status, image_id, description, min_cycle, max_cycle, min_bmi, max_bmi] = [this.title, this.sort, this.goods_id, this.goods_sku_id, this.num, this.status, this.image_id, this.description, this.min_cycle, this.max_cycle, this.min_bmi, this.max_bmi];
                    if(!title || !sort || !goods_sku_id || !num || !image_id || !description){
                        this.$message({
                            showClose: true,
                            message: '请将数据补充完整',
                            type: 'error'
                        });
                        return false;
                    }
                    if(max_cycle <= min_cycle){
                        this.$message({
                            showClose: true,
                            message: '使用周期填写错误',
                            type: 'error'
                        });
                        return false;
                    }
                    if(max_bmi <= min_bmi){
                        this.$message({
                            showClose: true,
                            message: 'BMI区间填写错误',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('goods.suggestion/add') ?>", {title, sort, goods_id, goods_sku_id, num, status, image_id, description, max_cycle, min_cycle, max_bmi, min_bmi}, function(res){
                        let type = 'error'
                        if(res.code == 1){
                            type = 'success';
                            that.drawer = false;
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type: type
                        });
                    }, 'json')
                },

                addSuggestion: function(){
                    this.drawer = true;
                },

                doEditSuggestion: function(){
                    let [suggestion_id, title, sort, goods_id, goods_sku_id, num, status, image_id, description, min_cycle, max_cycle, min_bmi, max_bmi] = [this.list[this.suggestion_idx].suggestion_id, this.edit_title, this.edit_sort, this.edit_goods_id, this.edit_goods_sku_id, this.edit_num, this.edit_status, this.edit_image_id, this.edit_description, this.edit_min_cycle, this.edit_max_cycle, this.edit_min_bmi, this.edit_max_bmi];
                    if(!title || !sort || !goods_sku_id || !num || !image_id || !description){
                        this.$message({
                            showClose: true,
                            message: '请将数据补充完整',
                            type: 'error'
                        });
                        return false;
                    }
                    if(max_cycle <= min_cycle){
                        this.$message({
                            showClose: true,
                            message: '使用周期填写错误',
                            type: 'error'
                        });
                        return false;
                    }
                    if(max_bmi <= min_bmi){
                        this.$message({
                            showClose: true,
                            message: 'BMI区间填写错误',
                            type: 'error'
                        });
                        return false;
                    }
                    let that = this;
                    $.post("<?= url('goods.suggestion/edit') ?>", {suggestion_id, title, sort, goods_id, goods_sku_id, num, status, image_id, description, max_cycle, min_cycle, max_bmi, min_bmi}, function(res){
                        let type = 'error'
                        if(res.code == 1){
                            type = 'success';
                            that.drawer = false;
                            let detail = that.list[that.suggestion_idx]
                            detail.title = title;
                            detail.num = num;
                            detail.status = status;
                            detail.goods_id = goods_id;
                            detail.goods_sku_id = goods_sku_id;
                            detail.sort = sort;
                            detail.description = description;
                            detail.max_cycle = max_cycle;
                            detail.min_cycle = min_cycle;
                            detail.max_bmi = max_bmi;
                            detail.min_bmi = min_bmi;
                        }
                        that.$message({
                            showClose: true,
                            message: res.msg,
                            type: type
                        });
                        that.editDrawer = false;
                    }, 'json')
                },

                edit: function(row){
                    let data = this.list[row.$index];
                    this.edit_title = data.title;
                    this.edit_sort = data.sort;
                    this.edit_status = data.status;
                    this.edit_goods_id = data.goods_id;
                    this.edit_goods_sku_id = data.goods_sku_id;
                    this.edit_num = data.num;
                    this.edit_description = data.description;
                    this.edit_min_cycle = data.min_cycle;
                    this.edit_max_cycle = data.max_cycle;
                    this.edit_min_bmi = data.min_bmi;
                    this.edit_max_bmi = data.max_bmi;
                    this.suggestion_idx = row.$index;
                    this.edit_goods_row = [data.goods_id, data.goods_sku_id]
                    this.edit_image_id = data.image_id;
                    this.edit_image = data.image? data.image.file_path : '';
                    this.editDrawer = true;
                },

                del: function(row){
                    let that = this;
                    let suggestion_id = this.list[row.$index].suggestion_id
                    this.$confirm('确定删除吗？', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        $.post("<?= url('goods.suggestion/del') ?>", {suggestion_id}, function(res){
                            let type = 'error'
                            if(res.code == 1){
                                that.list.splice(row.$index,1);
                                type = 'success';
                                that.editDrawer = false;
                            }
                            that.$message({
                                showClose: true,
                                message: res.msg,
                                type: type
                            });
                        }, 'json')
                    }).catch(() => {

                    });
                },

                chooseGoods: function(e){
                    this.goods_id = e[0];
                    this.goods_sku_id = e[1];
                },

                editChooseGoods: function(e){
                    this.edit_goods_id = e[0];
                    this.edit_goods_sku_id = e[1];
                },

                goodsList: function(){
                    let that = this;
                    $.post("<?= url('user.stock/goodsSkuList') ?>", {}, function(res){
                        that.goods_options = res.data;
                    }, 'json');
                },

                getSuggestionList: function(){
                    let that = this;
                    $.post("<?= url('goods.suggestion/suggestionList') ?>", {}, function(res){
                        let list = res.data.list;
                        list.forEach((v, k)=>{
                            list[k].status = `${v.status}`
                        })
                        that.page = res.data.page;
                        that.list = list;
                        that.total = res.data.total;
                    }, 'json');
                },

                editStatus: function(scope){
                    let detail = this.list[scope.$index];
                    let [value, suggestion_id] = [detail.status, detail.suggestion_id];
                    this.editField(suggestion_id, 'status', value)
                },

                editField: function(suggestion_id, field, value){
                    let that = this;
                    $.post("<?= url('goods.suggestion/editField') ?>", {suggestion_id, field, value}, function(res){
                        if(res.code == 1){
                            that.$message({
                                message: res.msg,
                                type: 'success'
                            })
                        }else{
                            that.$message({
                                showClose: true,
                                message: res.msg,
                                type: 'error'
                            })
                        }
                    }, 'json')
                },

                uploadImg: function(flag){
                    $('#cover-wrap').selectImages({
                        multiple: false,
                        done: function (data) {
                            if(flag ==1){
                                App.image_id = data[0]['file_id']
                                App.image = data[0]['file_path']
                            }else{
                                App.edit_image_id = data[0]['file_id']
                                App.edit_image = data[0]['file_path']
                            }
                        }
                    });
                },
            },
            computed:{

            },
            created: function(){
                this.goodsList();
                this.getSuggestionList();
            }
        });


    });

    function getSuggestionList(page=1){
        App.cur_page = page;
        App.getSuggestionList();
    }
</script>

