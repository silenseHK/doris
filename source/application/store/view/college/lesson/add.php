<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<link rel="stylesheet" href="assets/store/css/element.css">
<link rel="stylesheet" href="assets/common/plugins/layui/css/layui.css">

<style>

    body > .el-container {
        margin-bottom: 40px;
    }

    .el-row {
        margin-bottom: 20px;
        &:last-child {
             margin-bottom: 0;
         }
    }

    .el-row .el-col:first-child{
        text-align: right;
    }

    .el-tag--plain{
        border:none;
    }

</style>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加讲师</div>
                            </div>

                            <el-container>
                                <el-container>
                                    <el-main>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">课程名称 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-input
                                                        placeholder="请输入课程名称"
                                                        v-model="title"
                                                        clearable>
                                                </el-input>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">封面图 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <div v-if="img" class="demo-image__preview">
                                                    <el-image
                                                            style="width: 100px; height: 100px"
                                                            :src="img"
                                                            :preview-src-list="img_list">
                                                    </el-image>
                                                </div>
                                                <el-col style="margin-left: 0; padding-left:0; text-align: left;" :span="8" id="cover-wrap">
                                                    <el-button type="primary" @click="uploadImg" size="medium">上传<i class="el-icon-upload el-icon--right"></i></el-button>
                                                </el-col>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">课程简介</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-input type="textarea" v-model="desc"></el-input>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">讲师 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-select @change="chooseLecturer" style="width:100%" v-model="lecturer" placeholder="请选择讲师">
                                                    <el-option v-for="item in lecturer_list" :label="item.name" :value="item.lecturer_id" :key="item.lecturer_id"></el-option>
                                                </el-select>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">课程分类 *</el-tag>
                                            </el-col>
                                            <el-col :span="4">
                                                <el-select v-model="first_cate" @change="chooseFirstCate" placeholder="请选择">
                                                    <el-option
                                                            v-for="(item,key) in lesson_cate_list"
                                                            :key="item.lesson_cate_id"
                                                            :label="item.title"
                                                            :value="item.lesson_cate_id">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                            <el-col :span="4">
                                                <el-select v-model="cate" @change="chooseCate" placeholder="请选择">
                                                    <el-option
                                                            v-for="item in lesson_cate_list[cate_index].child"
                                                            :key="item.lesson_cate_id"
                                                            :label="item.title"
                                                            :value="item.lesson_cate_id">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">课程规模 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="lesson_size" @change="chooseLessonSize">
                                                    <el-radio :label="10">单课</el-radio>
                                                    <el-radio :label="20">系列课</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">课程类型 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="lesson_type">
                                                    <el-radio :label="10">视频</el-radio>
                                                    <el-radio v-show="lesson_size==10" :label="20">直播</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">是否公开 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="is_public" @change="chooseIsPublic">
                                                    <el-radio :label="0">不公开</el-radio>
                                                    <el-radio :label="1">公开</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20" v-show="!is_public">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">大咖私享</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="is_private">
                                                    <el-radio :label="0">否</el-radio>
                                                    <el-radio :label="1">是</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20" v-show="!is_public">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">级别可见</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="is_grade">
                                                    <el-radio :label="0">否</el-radio>
                                                    <el-radio :label="1">是</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20" v-show="!is_public && is_grade">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">可见等级</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-select style="width: 100%" v-model="grade" multiple placeholder="请选择">
                                                    <el-option
                                                            v-for="item in grade_list"
                                                            :key="item.grade_id"
                                                            :label="item.name"
                                                            :value="item.grade_id">
                                                    </el-option>
                                                </el-select>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">系列课节数 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-input-number size="small" v-model="total_size" min="1" :max="totalSizeMax"></el-input-number>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">详情描述</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <textarea id="container"></textarea>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">状态 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="status">
                                                    <el-radio :label="0">下线</el-radio>
                                                    <el-radio :label="1">上线</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">是否推荐 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-radio-group v-model="is_recom">
                                                    <el-radio :label="0">不推荐</el-radio>
                                                    <el-radio :label="1">推荐</el-radio>
                                                </el-radio-group>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">排序 *</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-input-number size="small" v-model="sort" min="1" max="99999"></el-input-number>
                                                <el-tag  effect="plain" type="warning">越小越靠前</el-tag>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20" v-show="lesson_size==10 && lesson_type==10">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">视频</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-input
                                                        placeholder=""
                                                        v-model="video_url"
                                                        clearable>
                                                </el-input>
                                                <el-link v-show="video_url" :href="video_url" target="_blank">预览视频</el-link>
                                                <el-button id="test5" type="primary" size="medium">{{video_url?"重新上传":"上传"}}<i class="el-icon-upload el-icon--right"></i></el-button>
                                            </el-col>
                                        </el-row>

                                        <el-row :gutter="20" v-show="lesson_size==10 && lesson_type==20">
                                            <el-col :span="2">
                                                <el-tag effect="plain" type="info">直播间</el-tag>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-select @change="chooseLiveRoom" style="width:100%" v-model="live_room" placeholder="请选择直播间">
                                                    <el-option v-for="item in live_room_list" :label="item.room_name" :value="item.id" :key="item.id"></el-option>
                                                </el-select>
                                            </el-col>
                                        </el-row>

                                        <el-row>
                                            <el-col :span="2"><el-tag effect="plain"></el-tag></el-col>
                                            <el-col :span="8">
                                                <el-button type="primary" @click="submit">提交</el-button>
                                            </el-col>
                                        </el-row>

                                    </el-main>
                                </el-container>
                                <el-container>
                                    <el-main></el-main>
                                </el-container>
                            </el-container>


                        </fieldset>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/store/js/element.js"></script>
<script src="assets/common/plugins/layui/layui.all.js"></script>
<script>
    $(function () {

        var App = new Vue({
            el: '#my-form',
            data: {
                lecturer_list: <?= $lecturer_list ?>,
                lesson_cate_list: <?= json_encode($lesson_cate_list) ?>,
                first_cate_id: 0,
                first_cate: '',
                cate_id: 0,
                cate_index: 0,
                cate: '',
                totalSizeMax: 100,
                title: '', //课程名称,
                desc: '', //描述
                img: '',
                img_list: [],
                img_id: 0,
                lecturer_id: 0,
                lecturer: '',
                live_room_list: <?= json_encode($live_room_list) ?>,
                live_room: '',
                live_room_id: 0,
                grade_list: <?= $grade_list ?>,
                grade: [],
                lesson_type: 10,
                lesson_size: 10,
                total_size: 1,
                status: 1,
                is_recom: 1,
                is_public: 0,
                is_private: 0,
                is_grade: 0,
                sort: 9999,
                video_url: '',
            },
            methods:{
                delImg: function(){
                    this.img = '';
                    this.img_id = 0;
                },
                uploadImg: function(){
                    $('#cover-wrap').selectImages({
                        multiple: false,
                        done: function (data) {
                            App.img_id = data[0]['file_id']
                            App.img = data[0]['file_path']
                            App.img_list.push(data[0]['file_path'])
                        }
                    });
                },
                chooseLecturer: function(e){
                    this.lecturer_id = e;
                },
                chooseFirstCate: function(e){
                    this.first_cate_id = e;
                    this.initCateIndex();
                },
                chooseCate: function(e){
                    this.cate_id = e;
                },
                initCateIndex: function(){
                    let that = this
                    let cate_index = 0;
                    this.lesson_cate_list.forEach(function(v,k){
                        if(v.lesson_cate_id == that.first_cate_id){
                            cate_index = k
                        }
                    })
                    this.cate_index = cate_index
                    this.cate = this.lesson_cate_list[cate_index]['child'][0]['title']
                    this.cate_id = this.lesson_cate_list[cate_index]['child'][0]['lesson_cate_id']
                },
                chooseLessonSize: function(e){
                    if(e==20){
                        this.lesson_type = 10;
                    }
                    this.initTotalSizeMax();
                },
                chooseIsPublic: function(e){
                    if(e){
                        this.is_private = 0;
                        this.is_grade = 0;
                        this.garde = [];
                    }
                },
                initTotalSizeMax: function(e){
                    if(this.lesson_size == 10){this.totalSizeMax=1; this.total_size = 1}else{this.totalSizeMax=50;}
                },
                chooseLiveRoom: function(e){
                    this.live_room_id = e;
                },
                submit: function(){
                    let [title, cover, desc, lecturer_id, is_public, is_private, is_grade, cate_id, lesson_type, lesson_size, total_size, status, sort, is_recom, video_url, live_room_id, grade]
                        =
                        [this.title, parseInt(this.img_id), this.desc, parseInt(this.lecturer_id),parseInt(this.is_public), parseInt(this.is_private), parseInt(this.is_grade), parseInt(this.cate_id), parseInt(this.lesson_type), parseInt(this.lesson_size), parseInt(this.total_size), parseInt(this.status), parseInt(this.sort), parseInt(this.is_recom), this.video_url, parseInt(this.live_room_id), this.grade];
                    //数据判断
                    if(!title || !cover || !lecturer_id || !cate_id){
                        layer.msg('请将必填数据补充完整');
                        return false;
                    }
                    if(lesson_type == 10 && !video_url && lesson_size == 10){
                        layer.msg('请上传视频');
                        return false;
                    }
                    if(lesson_type == 20 && !live_room_id && lesson_size == 10){
                        layer.msg('请选择直播间');
                        return false;
                    }
                    if(!is_public && !is_private && !is_grade){
                        layer.msg('请至少选择一种私享类型');
                        return false;
                    }
                    if(is_grade && !grade.length){
                        layer.msg('请至少选择一种可见会员等级');
                        return false;
                    }
                    let content = editor.getContent();
                    $.post("<?= url('college.lesson/add') ?>",{title, cover, desc, lecturer_id, is_public, is_private, is_grade, cate_id, lesson_type, lesson_size, total_size, status, sort, is_recom, video_url, live_room_id, grade, content}, function(res){
                        if(res.code == 1){
                            setTimeout(function(){
                                location.reload();
                            },1000)
                        }
                        layer.msg(res.msg)
                    }, 'json')
                }
            },
            mounted: function(){
                if(!this.first_cate_id){
                    this.first_cate_id = this.lesson_cate_list[0]['lesson_cate_id'];
                    this.first_cate = this.lesson_cate_list[0]['title'];
                }
                this.initCateIndex();
                this.initTotalSizeMax();
            }
        })

        // 富文本编辑器
        var editor = UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        // 选择图片：分销中心首页
        $('.j-image').selectImages({
            multiple: false,
            done: function (data) {
                App.img_id = data[0]['file_id']
                App.img = data[0]['file_path']
            }
        });

        layui.use('upload', function() {
            var $ = layui.jquery
                , upload = layui.upload;
            upload.render({
                elem: '#test5'
                , url: "<?= url('upload/video')?>" //改成您自己的上传接口
                , accept: 'video' //视频
                ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                    layer.load(); //上传loading
                }
                , done: function (res) {
                    layer.closeAll();
                    layer.msg('上传成功');
                    App.video_url = res.data.url;
                }
            });
        })

    });
</script>
