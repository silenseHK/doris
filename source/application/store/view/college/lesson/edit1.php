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
                                <el-form ref="ruleForm" label-width="100px" class="demo-ruleForm" style="width:500px;">

                                    <el-form-item label="课时标题" prop="title">
                                        <el-input v-model="title" maxlength="50" show-word-limit></el-input>
                                    </el-form-item>

                                    <el-form-item label="封面" prop="img">
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
                                    </el-form-item>

                                    <el-form-item label="描述" prop="desc">
                                        <el-input
                                                type="textarea"
                                                autosize
                                                placeholder="请输入内容"
                                                maxlength="255"
                                                show-word-limit
                                                v-model="desc">
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item label="讲师" prop="lecturer">
                                        <el-select @change="chooseLecturer" style="width:100%" v-model="lecturer" placeholder="请选择讲师">
                                            <el-option v-for="item in lecturer_list" :label="item.name" :value="item.lecturer_id" :key="item.lecturer_id"></el-option>
                                        </el-select>
                                    </el-form-item>

                                    <el-form-item label="课程分类" prop="cate">
                                        <el-select v-model="first_cate" @change="chooseFirstCate" placeholder="请选择">
                                            <el-option
                                                    v-for="(item,key) in lesson_cate_list"
                                                    :key="item.lesson_cate_id"
                                                    :label="item.title"
                                                    :value="item.lesson_cate_id">
                                            </el-option>
                                        </el-select>
                                        <el-select v-model="cate" @change="chooseCate" placeholder="请选择">
                                            <el-option
                                                    v-for="item in lesson_cate_list[cate_index].child"
                                                    :key="item.lesson_cate_id"
                                                    :label="item.title"
                                                    :value="item.lesson_cate_id">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>

                                    <el-form-item label="课程规模" prop="lesson_size">
                                        <el-radio-group v-model="lesson_size" @change="chooseLessonSize">
                                            <el-radio :label="10">单课</el-radio>
                                            <el-radio :label="20">系列课</el-radio>
                                        </el-radio-group>
                                    </el-form-item>

                                    <el-form-item label="课程类型" prop="lesson_type">
                                        <el-radio-group v-model="lesson_type">
                                            <el-radio :label="10">视频</el-radio>
                                            <el-radio v-show="lesson_size==10" :label="20">直播</el-radio>
                                        </el-radio-group>
                                    </el-form-item>

                                    <el-form-item label="是否公开" prop="is_public">
                                        <el-switch @change="chooseIsPublic" v-model="is_public" active-value="1" inactive-value="0" active-text="是" inactive-text="否"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="大咖私享" v-show="is_public==0">
                                        <el-switch v-model="is_private" active-value="1" inactive-value="0" active-text="是" inactive-text="否"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="级别可见" prop="is_grade" v-show="is_public==0">
                                        <el-switch v-model="is_grade" active-value="1" inactive-value="0" active-text="是" inactive-text="否"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="可见等级" prop="grade" v-show="is_grade==1">
                                        <el-select style="width: 100%" v-model="grade" multiple placeholder="请选择">
                                            <el-option
                                                    v-for="item in grade_list"
                                                    :key="item.grade_id"
                                                    :label="item.name"
                                                    :value="item.grade_id">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>

                                    <el-form-item label="系列课节数" prop="total_size">
                                        <el-input-number size="small" v-model="total_size" min="1" :max="totalSizeMax"></el-input-number>
                                    </el-form-item>

                                    <el-form-item label="详情描述">
                                        <textarea id="container">{{content}}</textarea>
                                    </el-form-item>

                                    <el-form-item label="状态" prop="status">
                                        <el-switch v-model="status" active-value="1" inactive-value="0" active-text="上架" inactive-text="下架"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="是否推荐" prop="is_recom">
                                        <el-switch v-model="is_recom" active-value="1" inactive-value="0" active-text="推荐" inactive-text="不推荐"></el-switch>
                                    </el-form-item>

                                    <el-form-item label="排序">
                                        <el-input-number size="small" v-model="sort" min="1" max="99999"></el-input-number>
                                        <el-tag  effect="plain" type="warning">越小越靠前</el-tag>
                                    </el-form-item>

                                    <el-form-item label="视频" v-show="lesson_size==10 && lesson_type==10">
                                        <el-input
                                                placeholder=""
                                                v-model="video_url"
                                                clearable>
                                        </el-input>
                                        <el-link v-show="video_url" :href="video_url" target="_blank">预览视频</el-link>
                                        <el-button id="test5" type="primary" size="medium">{{video_url?"重新上传":"上传"}}<i class="el-icon-upload el-icon--right"></i></el-button>
                                    </el-form-item>

                                    <el-form-item label="直播间" v-show="lesson_size==10 && lesson_type==20">
                                        <el-select @change="chooseLiveRoom" style="width:100%" v-model="live_room" placeholder="请选择直播间">
                                            <el-option v-for="item in live_room_list" :label="item.room_name" :value="item.id" :key="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>

                                    <el-form-item>
                                        <el-button type="primary" @click="submit">提交</el-button>
                                    </el-form-item>

                                </el-form>
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
                first_cate_id: <?= $lesson_info['first_cate']['lesson_cate_id'] ?>,
                first_cate: "<?= $lesson_info['first_cate']['title'] ?>",
                cate_id: <?= $lesson_info['cate_id'] ?>,
                cate_index: 0,
                cate: "<?= $lesson_info['cate']['title'] ?>",
                totalSizeMax: 100,
                title: "<?= $lesson_info['title'] ?>", //课程名称,
                desc: "<?= $lesson_info['desc'] ?>", //描述
                img: "<?= $lesson_info['image']['file_path'] ?>",
                img_list: ["<?= $lesson_info['image']['file_path'] ?>"],
                img_id: <?= $lesson_info['cover'] ?>,
                lecturer_id: <?= $lesson_info['lecturer_id'] ?>,
                lecturer: "<?= $lesson_info['lecturer']['name'] ?>",
                live_room_list: <?= json_encode($live_room_list) ?>,
                live_room: "<?= isset($lesson_info['class'])?(isset($lesson_info['class']['live_room']['room_name'])?$lesson_info['class']['live_room']['room_name']:''):'' ?>",
                live_room_id: <?= isset($lesson_info['class'])?$lesson_info['class']['live_room_id']:0 ?>,
                grade_list: <?= $grade_list ?>,
                grade: [],
                lesson_type: <?= $lesson_info['lesson_type']['value'] ?>,
                lesson_size: <?= $lesson_info['lesson_size']['value'] ?>,
                total_size: <?= $lesson_info['total_size'] ?>,
                status: "<?= $lesson_info['status'] ?>",
                is_recom: "<?= $lesson_info['is_recom'] ?>",
                is_public: "<?= $lesson_info['is_public'] ?>",
                is_private: "<?= $lesson_info['is_private'] ?>",
                is_grade: "<?= $lesson_info['is_grade'] ?>",
                sort: <?= $lesson_info['sort'] ?>,
                video_url: "<?= isset($lesson_info['class'])?$lesson_info['class']['video_url']:'' ?>",
                limit_grade: <?= json_encode($lesson_info['limit_grade']) ?>,
                content: '<?= $lesson_info['content'] ?>',
                lesson_id: <?= $lesson_info['lesson_id'] ?>
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
                    let [title, cover, desc, lecturer_id, is_public, is_private, is_grade, cate_id, lesson_type, lesson_size, total_size, status, sort, is_recom, video_url, live_room_id, grade, lesson_id]
                        =
                        [this.title, parseInt(this.img_id), this.desc, parseInt(this.lecturer_id),parseInt(this.is_public), parseInt(this.is_private), parseInt(this.is_grade), parseInt(this.cate_id), parseInt(this.lesson_type), parseInt(this.lesson_size), parseInt(this.total_size), parseInt(this.status), parseInt(this.sort), parseInt(this.is_recom), this.video_url, parseInt(this.live_room_id), this.grade, parseInt(this.lesson_id)];
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
                    $.post("<?= url('college.lesson/edit') ?>",{title, cover, desc, lecturer_id, is_public, is_private, is_grade, cate_id, lesson_type, lesson_size, total_size, status, sort, is_recom, video_url, live_room_id, grade, content, lesson_id}, function(res){
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
                let that = this;
                if(!this.first_cate_id){
                    this.first_cate_id = this.lesson_cate_list[0]['lesson_cate_id'];
                    this.first_cate = this.lesson_cate_list[0]['title'];
                }
                this.initCateIndex();
                this.initTotalSizeMax();
                this.limit_grade.forEach(function(v,k){
                    that.grade.push(v.grade_id);
                })
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
