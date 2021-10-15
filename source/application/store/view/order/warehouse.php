<link rel="stylesheet" href="assets/store/css/theme-chalk.css">
<div id="app" v-cloak class="page-statistics-data row-content am-cf">
    <!-- 数据概况 -->
    

    <!-- 近七日交易走势 -->
<!--    <div class="row">-->
<!--        <div class="am-u-sm-12 am-margin-bottom">-->
<!--            <div class="widget am-cf">-->
<!--                <div class="widget-head">-->
<!--                    <div class="widget-title">近七日交易走势</div>-->
<!--                </div>-->
<!--                <div class="widget-body am-cf">-->
<!--                    <div id="echarts-trade" class="widget-echarts"></div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
    <!-- 排行榜 -->
    <div class="row">
        <div class="am-u-sm-6 am-margin-bottom">
            <div class="widget-ranking widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">商品库存</div>
                </div>
                <div class="widget-body am-cf">
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-center" width="20%">商品名称</th>
                            <th class="am-text-center" width="30%">商品图</th>
                            <th class="am-text-left" width="20%">规格</th>
                            <th class="am-text-center" width="15%">历史库存</th>
                            <th class="am-text-center" width="15%">当前库存</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in goodsRanking">
                            <td class="am-text-middle am-text-center">{{ item.goods_name }}</td>
                            <td class="am-text-middle am-text-center">
                                <div class="ranking-img">
                                    <img v-if="item.image" :src="item.image.file_path" alt="">
                                </div>
                            </td>
                            <td class="am-text-middle">

                                <p v-for="it in item.sku_list" class="ranking-item-title am-text-truncate">
                                    {{it.spec_name}} : {{it.spec_value}}
                                </p>

                            </td>
                            <td class="am-text-middle am-text-center">{{ item.total_stock_num }}</td>
                            <td class="am-text-middle am-text-center">{{ item.stock_num }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="am-u-sm-6 am-margin-bottom">
            <div class="widget-ranking widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">待发货</div>
                </div>
                <div class="widget-body am-cf">
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-center" width="20%">商品名称</th>
                            <th class="am-text-center" width="30%">商品图</th>
                            <th class="am-text-left" width="20%">规格</th>
                            <th class="am-text-center" width="15%">待发货数</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in userExpendRanking">
                            <td class="am-text-middle am-text-center">{{ item.goods_name }}</td>
                            <td class="am-text-middle am-text-center">
                                <div class="ranking-img">
                                    <img :src="item.image" alt="">
                                </div>
                            </td>
                            <td class="am-text-middle">

                                <p v-for="it in item.spec_attr" class="ranking-item-title am-text-truncate">
                                    {{it.spec_name}} : {{it.spec_value}}
                                </p>

                            </td>
                            <td class="am-text-middle am-text-center">{{ item.goods_num }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/vue.min.js?v=1.1.35"></script>
<script src="assets/store/js/element-ui@2.13.1.js"></script>

<script type="text/javascript">

    new Vue({
        el: '#app',
        data: {
            // 数据概况
            survey: {
                loading: false,
                dateValue: [],
                values: <?= json_encode($nums) ?>,
                timeValues: <?= json_encode($time_nums) ?>
            },
            // 商品销售榜
            goodsRanking: <?= json_encode($spec_list) ?>,
            // 用户消费榜
            userExpendRanking: <?= json_encode($deliver_list) ?>,
            // 快捷时间选择
            pickerOptions: {
                shortcuts: [{
                    text: '最近一周',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近三个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                        picker.$emit('pick', [start, end]);
                    }
                }]
            },
        },

        mounted() {
            // 近七日交易走势

        },

        methods: {

            // 监听事件：日期选择快捷导航
            onFastDate: function (days) {
                var startDate, endDate;
                // 清空日期
                if (days === 0) {
                    this.survey.dateValue = [];
                } else {
                    startDate = $.getDay(-days);
                    endDate = $.getDay(0);
                    this.survey.dateValue = [startDate, endDate];
                }
                // api: 获取数据概况
                this.__getApiData__survey(startDate, endDate);
            },

            // 监听事件：日期选择框改变
            onChangeDate: function (e) {
                // api: 获取数据概况
                if(e)this.__getApiData__survey(e[0], e[1]);
            },

            // 获取数据概况
            __getApiData__survey: function (startDate, endDate) {
                var app = this;
                // 请求api数据
                app.survey.loading = true;
                // api地址
                var url = '<?= url('order/gettimewarehouseinfo') ?>';
                $.post(url, {
                        start_time: startDate,
                        end_time: endDate
                    }, function (result) {
                        // app.survey.values = result.data;
                        app.survey.loading = false;
                        app.survey.timeValues = result.data;
                    },
                );
            },

            /**
             * 近七日交易走势
             * @type {HTMLElement}
             */
            // drawLine() {
            //     var dom = document.getElementById('echarts-trade');
            //     echarts.init(dom, 'walden').setOption({
            //         tooltip: {
            //             trigger: 'axis'
            //         },
            //         legend: {
            //             data: ['成交量', '成交额']
            //         },
            //         toolbox: {
            //             show: true,
            //             showTitle: false,
            //             feature: {
            //                 mark: {show: true},
            //                 magicType: {show: true, type: ['line', 'bar']}
            //             }
            //         },
            //         calculable: true,
            //         xAxis: {
            //             type: 'category',
            //             boundaryGap: false,
            //             data: '2010-01'
            //         },
            //         yAxis: {
            //             type: 'value'
            //         },
            //         series: [
            //             {
            //                 name: '成交额',
            //                 type: 'line',
            //                 data: '2010-01'
            //             },
            //             {
            //                 name: '成交量',
            //                 type: 'line',
            //                 data: '2010-01'
            //             }
            //         ]
            //     }, true);
            // }
            navigate:function(data){
                
            }
        }

    });

</script>