<?php
/**
 * 后台菜单配置
 *    'home' => [
 *       'name' => '首页',                // 菜单名称
 *       'icon' => 'icon-home',          // 图标 (class)
 *       'index' => 'index/index',         // 链接
 *     ],
 */
return [
//    'index' => [
//        'name' => '首页',
//        'icon' => 'icon-home',
//        'index' => 'index/index',
//    ],
//    'store' => [
//        'name' => '管理员',
//        'icon' => 'icon-guanliyuan',
//        'index' => 'store.user/index',
//        'submenu' => [
//            [
//                'name' => '管理员列表',
//                'index' => 'store.user/index',
//                'uris' => [
//                    'store.user/index',
//                    'store.user/add',
//                    'store.user/edit',
//                    'store.user/delete',
//                ],
//            ],
//            [
//                'name' => '角色管理',
//                'index' => 'store.role/index',
//                'uris' => [
//                    'store.role/index',
//                    'store.role/add',
//                    'store.role/edit',
//                    'store.role/delete',
//                ],
//            ],
//            [
//                'name' => '操作日志',
//                'index' => 'store.handle/index',
//                'uris' => [
//                    'store.handle/index'
//                ],
//            ],
//        ]
//    ],
//    'goods' => [
//        'name' => '商品管理',
//        'icon' => 'icon-goods',
//        'index' => 'goods/index',
//        'submenu' => [
//            [
//                'name' => '商品列表',
//                'index' => 'goods/index',
//                'uris' => [
//                    'goods/index',
//                    'goods/add',
//                    'goods/edit',
//                    'goods/copy'
//                ],
//            ],
//            [
//                'name' => '商品分类',
//                'index' => 'goods.category/index',
//                'uris' => [
//                    'goods.category/index',
//                    'goods.category/add',
//                    'goods.category/edit',
//                ],
//            ],
//            [
//                'name' => '商品评价',
//                'index' => 'goods.comment/index',
//                'uris' => [
//                    'goods.comment/index',
//                    'goods.comment/detail',
//                ],
//            ],
//            [
//                'name' => '推荐套餐',
//                'index' => 'goods.suggestion/index',
//                'uris' => [
//                    'goods.suggestion/index',
//                    'goods.suggestion/add',
//                    'goods.suggestion/edit',
//                ],
//            ]
//        ],
//    ],
//    'order' => [
//        'name' => '订单管理',
//        'icon' => 'icon-order',
//        'index' => 'order/warehouse',
//        'submenu' => [
////            [
////                'name' => '消费订单',
////                'active' => false,
////                'submenu' => [
////                    [
////                        'name' => '全部订单',
////                        'index' => 'order/all_list',
////                    ],
////                    [
////                        'name' => '待审核',
////                        'index' => 'order/exam_list',
////                    ],
////                    [
////                        'name' => '待发货',
////                        'index' => 'order/delivery_list',
////                    ],
////                    [
////                        'name' => '待收货',
////                        'index' => 'order/receipt_list',
////                    ],
////                    [
////                        'name' => '待付款',
////                        'index' => 'order/pay_list',
////                    ],
////                    [
////                        'name' => '已完成',
////                        'index' => 'order/complete_list',
////
////                    ],
////                    [
////                        'name' => '已取消',
////                        'index' => 'order/cancel_list',
////                    ],
////                    [
////                        'name' => '售后管理',
////                        'index' => 'order.refund/index',
////                        'uris' => [
////                            'order.refund/index',
////                            'order.refund/detail',
////                        ]
////                    ],
////                ]
////            ],
//            [
//                'name' => '消费订单',
//                'index' => 'order/order_list',
//            ],
//            [
//                'name' => '补货订单',
//                'index' => 'order/order_stock',
//            ],
//            [
//                'name' => '提货发货',
//                'index' => 'order/order_delivery',
//            ],
//            [
//                'name' => '仓库信息',
//                'index' => 'order/warehouse',
//            ],
//            [
//                'name' => '运费明细',
//                'index' => 'order/freight',
//            ],
////            [
////                'name' => '发货管理',
////                'active' => false,
////                'submenu' => [
////                    [
////                        'name' => '发货列表',
////                        'index' => 'order.express/lists',
////                    ],
////                    [
////                        'name' => '发货统计',
////                        'index' => 'order.express/statistics',
////                    ]
////                ]
////            ],
//        ]
//    ],
//    'user' => [
//        'name' => '用户管理',
//        'icon' => 'icon-user',
//        'index' => 'user/index',
//        'submenu' => [
//            [
//                'name' => '用户列表',
//                'index' => 'user/index',
//            ],
//            [
//                'name' => '会员等级',
//                'active' => false,
//                'submenu' => [
//                    [
//                        'name' => '等级管理',
//                        'index' => 'user.grade/index',
//                        'uris' => [
//                            'user.grade/index',
//                            'user.grade/add',
//                            'user.grade/edit',
//                            'user.grade/delete',
//                        ]
//                    ],
//                    [
//                        'name' => '等级记录',
//                        'index' => 'user.grade/log',
//                    ],
//                ]
//            ],
//            [
//                'name' => '余额记录',
//                'active' => false,
//                'submenu' => [
////                    [
////                        'name' => '充值记录',
////                        'index' => 'user.recharge/order',
////                    ],
//                    [
//                        'name' => '余额明细',
//                        'index' => 'user.balance/log',
//                    ],
//                ]
//            ],
//            [
//                'name' => '团队管理',
//                'active' => false,
//                'submenu' => [
//                    [
//                        'name' => '转换团队',
//                        'index' => 'user.team/exchange',
//                    ],
//                    [
//                        'name' => '月度业绩',
//                        'index' => 'user.team/monthachievement',
//                    ],
//                    [
//                        'name' => '年度业绩',
//                        'index' => 'user.team/yearachievement',
//                    ],
////                    [
////                        'name' => '团队转换记录',
////                        'index' => 'user.team/exchange_log',
////                    ],
////                    [
////                        'name' => '团队管理奖',
////                        'index' => 'user.team/manage_reward',
////                    ],
////                    [
////                        'name' => '营养师管理团队',
////                        'index' => 'user.dietician/lists',
////                    ],
////                    [
////                        'name' => '代理中心',
////                        'index' => 'user.agent/index',
////                    ],
////                    [
////                        'name' => '招商管理',
////                        'index' => 'user.salesperson/index',
////                    ],
//                ]
//            ],
////            [
////                'name' => '库存转移',
////                'index' => 'user.stock/exchange',
////            ],
////            [
////                'name' => '迁移管理',
////                'index' => 'user.stock/transfer',
////            ],
//        ]
//    ],
//    'shop' => [
//        'name' => '门店管理',
//        'icon' => 'icon-shop',
//        'index' => 'shop/index',
//        'submenu' => [
//            [
//                'name' => '门店管理',
//                'active' => true,
//                'index' => 'shop/index',
//                'submenu' => [
//                    [
//                        'name' => '门店列表',
//                        'index' => 'shop/index',
//                        'uris' => [
//                            'shop/index',
//                            'shop/add',
//                            'shop/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '店员管理',
//                        'index' => 'shop.clerk/index',
//                        'uris' => [
//                            'shop.clerk/index',
//                            'shop.clerk/add',
//                            'shop.clerk/edit',
//                        ]
//                    ],
//                ]
//            ],
//            [
//                'name' => '订单核销记录',
//                'index' => 'shop.order/index',
//            ]
//        ]
//    ],
//    'content' => [
//        'name' => '内容管理',
//        'icon' => 'icon-wenzhang',
//        'index' => 'content.article/index',
//        'submenu' => [
//            [
//                'name' => '文章管理',
//                'active' => false,
//                'submenu' => [
//                    [
//                        'name' => '文章列表',
//                        'index' => 'content.article/index',
//                        'uris' => [
//                            'content.article/index',
//                            'content.article/add',
//                            'content.article/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '文章分类',
//                        'index' => 'content.article.category/index',
//                        'uris' => [
//                            'content.article.category/index',
//                            'content.article.category/add',
//                            'content.article.category/edit',
//                        ]
//                    ],
//                ]
//            ],
//            [
//                'name' => '文件库管理',
//                'submenu' => [
//                    [
//                        'name' => '文件分组',
//                        'index' => 'content.files.group/index',
//                        'uris' => [
//                            'content.files.group/index',
//                            'content.files.group/add',
//                            'content.files.group/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '文件列表',
//                        'index' => 'content.files/index'
//                    ],
//                    [
//                        'name' => '回收站',
//                        'index' => 'content.files/recycle',
//                    ],
//                ]
//            ],
////            [
////                'name' => '问卷管理',
////                'submenu' => [
////                    [
////                        'name' => '问卷列表',
////                        'index' => 'content.questionnaire/index',
////                        'uris' => [
////                            'content.questionnaire/index',
////                            'content.questionnaire/add',
////                            'content.questionnaire/edit',
////                        ]
////                    ],
////                    [
////                        'name' => '问题列表',
////                        'index' => 'content.questionnaire.question/index',
////                    ],
////                    [
////                        'name' => '问题分类',
////                        'index' => 'content.questionnaire.question/cateIndex',
////                    ],
////                    [
////                        'name' => '配餐列表',
////                        'index' => 'content.food_group/index',
////                    ],
////                    [
////                        'name' => '配餐列表',
////                        'index' => 'content.food_group/test',
////                    ],
////                ]
////            ],
//            [
//                'name' => '百问百答',
//                'submenu' => [
//                    [
//                        'name' => '问答列表',
//                        'index' => 'content.online_questions/index',
//                    ],
//                    [
//                        'name' => '分类列表',
//                        'index' => 'content.online_questions/cateindex',
//                    ]
//                ]
//            ],
////            [
////                'name' => '二维码管理',
////                'submenu' => [
////                    [
////                        'name' => '二维码列表',
////                        'index' => 'content.qrcode/index',
////                    ]
////                ]
////            ],
//        ]
//    ],
//    'finance' => [
//        'name' => '财务管理',
//        'icon' => 'am-icon-money',
//        'index' => 'finance.dealer.withdraw/index',
//        'submenu' => [
//            [
//                'name' => '提现申请',
//                'active' => true,
//                'submenu' => [
//                    [
//                        'name' => '提现申请',
//                        'index' => 'finance.dealer.withdraw/index',
//                    ],
//                ]
//            ],
//            [
//                'name' => '订单入账',
//                'index' => 'finance.income.index/index'
//            ],
//            [
//                'name' => '财务设置',
//                'index' => 'finance.setting/index'
//            ],
//        ]
//    ],
////    'market' => [
////        'name' => '营销管理',
////        'icon' => 'icon-marketing',
////        'index' => 'market.coupon/index',
////        'submenu' => [
////            [
////                'name' => '优惠券',
//////                'active' => true,
////                'submenu' => [
////                    [
////                        'name' => '优惠券列表',
////                        'index' => 'market.coupon/index',
////                        'uris' => [
////                            'market.coupon/index',
////                            'market.coupon/add',
////                            'market.coupon/edit',
////                        ]
////                    ],
////                    [
////                        'name' => '领取记录',
////                        'index' => 'market.coupon/receive'
////                    ],
////                ]
////            ],
//////            [
//////                'name' => '用户充值',
//////                'submenu' => [
//////                    [
//////                        'name' => '充值套餐',
//////                        'index' => 'market.recharge.plan/index',
//////                        'uris' => [
//////                            'market.recharge.plan/index',
//////                            'market.recharge.plan/add',
//////                            'market.recharge.plan/edit',
//////                        ]
//////                    ],
//////                    [
//////                        'name' => '充值设置',
//////                        'index' => 'market.recharge/setting'
//////                    ],
//////                ]
//////            ],
//////            [
//////                'name' => '积分管理',
//////                'submenu' => [
//////                    [
//////                        'name' => '积分设置',
//////                        'index' => 'market.points/setting'
//////                    ],
//////                    [
//////                        'name' => '积分明细',
//////                        'index' => 'market.points/log'
//////                    ],
//////                ]
//////            ],
////            [
////                'name' => '消息推送',
////                'submenu' => [
////                    [
////                        'name' => '发送消息',
////                        'index' => 'market.push/send',
////                    ],
////                    [
////                        'name' => '活跃用户',
////                        'index' => 'market.push/user',
////                    ],
//////                    [
//////                        'name' => '发送日志',
//////                        'index' => 'market.push/log',
//////                    ],
////                ]
////            ],
////            [
////                'name' => '满额包邮',
////                'index' => 'market.basic/full_free',
////            ],
////            [
////                'name' => '体验装',
//////                'active' => true,
////                'submenu' => [
////                    [
////                        'name' => '订单',
////                        'index' => 'market.experience/orders',
////                    ],
////                    [
////                        'name' => '排行',
////                        'index' => 'market.experience/rank'
////                    ],
////                ]
////            ],
////        ],
////    ],
//    'wxapp' => [
//        'name' => '小程序',
//        'icon' => 'icon-wxapp',
//        'color' => '#36b313',
//        'index' => 'wxapp/setting',
//        'submenu' => [
//            [
//                'name' => '小程序设置',
//                'index' => 'wxapp/setting',
//            ],
//            [
//                'name' => '页面管理',
//                'active' => true,
//                'submenu' => [
//                    [
//                        'name' => '页面设计',
//                        'index' => 'wxapp.page/index',
//                        'uris' => [
//                            'wxapp.page/index',
//                            'wxapp.page/add',
//                            'wxapp.page/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '分类模板',
//                        'index' => 'wxapp.page/category'
//                    ],
//                    [
//                        'name' => '页面链接',
//                        'index' => 'wxapp.page/links'
//                    ],
////                    [
////                        'name' => '首页属性',
////                        'index' => 'wxapp.page/home'
////                    ]
//                ]
//            ],
//            [
//                'name' => '帮助中心',
//                'index' => 'wxapp.help/index',
//                'uris' => [
//                    'wxapp.help/index',
//                    'wxapp.help/add',
//                    'wxapp.help/edit'
//                ]
//            ],
//            [
//                'name' => '系统消息',
//                'index' => 'wxapp.system_msg/index',
//                'uris' => [
//                    'wxapp.system_msg/index',
//                    'wxapp.system_msg/add',
//                    'wxapp.system_msg/edit'
//                ]
//            ],
//        ],
//    ],
//    'apps' => [
//        'name' => '应用中心',
//        'icon' => 'icon-application',
//        'is_svg' => true,   // 多色图标
//        'index' => 'apps.dealer.apply/index',
//        'submenu' => [
//            [
//                'name' => '分销中心',
//                'submenu' => [
////                    [
////                        'name' => '入驻申请',
////                        'index' => 'apps.dealer.apply/index',
////                    ],
////                    [
////                        'name' => '分销商用户',
////                        'index' => 'apps.dealer.user/index',
////                        'uris' => [
////                            'apps.dealer.user/index',
////                            'apps.dealer.user/fans',
////                        ]
////                    ],
////                    [
////                        'name' => '分销订单',
////                        'index' => 'apps.dealer.order/index',
////                    ],
//                    [
//                        'name' => '分销设置',
//                        'index' => 'apps.dealer.setting/index',
//                    ],
//                    [
//                        'name' => '分销海报',
//                        'index' => 'apps.dealer.setting/qrcode',
//                    ],
//                ]
//            ],
////            [
////                'name' => '拼团管理',
////                'submenu' => [
////                    [
////                        'name' => '商品分类',
////                        'index' => 'apps.sharing.category/index',
////                        'uris' => [
////                            'apps.sharing.category/index',
////                            'apps.sharing.category/add',
////                            'apps.sharing.category/edit',
////                        ]
////                    ],
////                    [
////                        'name' => '商品列表',
////                        'index' => 'apps.sharing.goods/index',
////                        'uris' => [
////                            'apps.sharing.goods/index',
////                            'apps.sharing.goods/add',
////                            'apps.sharing.goods/edit',
////                            'apps.sharing.goods/copy',
////                            'apps.sharing.goods/copy_master',
////                        ]
////                    ],
////                    [
////                        'name' => '拼单管理',
////                        'index' => 'apps.sharing.active/index',
////                        'uris' => [
////                            'apps.sharing.active/index',
////                            'apps.sharing.active/users',
////                        ]
////                    ],
////                    [
////                        'name' => '订单管理',
////                        'index' => 'apps.sharing.order/index',
////                        'uris' => [
////                            'apps.sharing.order/index',
////                            'apps.sharing.order/detail',
////                            'apps.sharing.order.operate/batchdelivery'
////                        ]
////                    ],
////                    [
////                        'name' => '售后管理',
////                        'index' => 'apps.sharing.order.refund/index',
////                        'uris' => [
////                            'apps.sharing.order.refund/index',
////                            'apps.sharing.order.refund/detail',
////                        ]
////                    ],
////                    [
////                        'name' => '商品评价',
////                        'index' => 'apps.sharing.comment/index',
////                        'uris' => [
////                            'apps.sharing.comment/index',
////                            'apps.sharing.comment/detail',
////                        ],
////                    ],
////                    [
////                        'name' => '拼团设置',
////                        'index' => 'apps.sharing.setting/index'
////                    ]
////                ]
////            ],
////            [
////                'name' => '砍价活动',
////                'index' => 'apps.bargain.active/index',
////                'submenu' => [
////                    [
////                        'name' => '活动列表',
////                        'index' => 'apps.bargain.active/index',
////                        'uris' => [
////                            'apps.bargain.active/index',
////                            'apps.bargain.active/add',
////                            'apps.bargain.active/edit',
////                            'apps.bargain.active/delete',
////                        ],
////                    ],
////                    [
////                        'name' => '砍价记录',
////                        'index' => 'apps.bargain.task/index',
////                        'uris' => [
////                            'apps.bargain.task/index',
////                            'apps.bargain.task/add',
////                            'apps.bargain.task/edit',
////                            'apps.bargain.task/delete',
////                            'apps.bargain.task/help',
////                        ],
////                    ],
////                    [
////                        'name' => '砍价设置',
////                        'index' => 'apps.bargain.setting/index',
////                    ]
////                ]
////            ],
////            [
////                'name' => '好物圈',
////                'index' => 'apps.wow.order/index',
////                'submenu' => [
////                    [
////                        'name' => '商品收藏',
////                        'index' => 'apps.wow.shoping/index',
////                    ],
////                    [
////                        'name' => '订单信息',
////                        'index' => 'apps.wow.order/index',
////                    ],
////                    [
////                        'name' => '基础设置',
////                        'index' => 'apps.wow.setting/index',
////                    ]
////                ]
////            ],
//            [
//                'name' => '小程序直播',
//                'index' => 'apps.live.room/index',
//                'submenu' => [
//                    [
//                        'name' => '直播间管理',
//                        'index' => 'apps.live.room/index',
//                    ],
//                ]
//            ],
//        ]
//    ],
//    'setting' => [
//        'name' => '设置',
//        'icon' => 'icon-setting',
//        'index' => 'setting/store',
//        'submenu' => [
//            [
//                'name' => '商城设置',
//                'index' => 'setting/store',
//            ],
//            [
//                'name' => '交易设置',
//                'index' => 'setting/trade',
//            ],
//            [
//                'name' => '运费模板',
//                'index' => 'setting.delivery/index',
//                'uris' => [
//                    'setting.delivery/index',
//                    'setting.delivery/add',
//                    'setting.delivery/edit',
//                ],
//            ],
//            [
//                'name' => '物流公司',
//                'index' => 'setting.express/index',
//                'uris' => [
//                    'setting.express/index',
//                    'setting.express/add',
//                    'setting.express/edit',
//                ],
//            ],
//            [
//                'name' => '短信通知',
//                'index' => 'setting/sms'
//            ],
//            [
//                'name' => '模板消息',
//                'index' => 'setting/tplmsg',
//                'uris' => [
//                    'setting/tplmsg',
//                    'setting.help/tplmsg'
//
//                ],
//            ],
//            [
//                'name' => '订阅消息',
//                'index' => 'setting/submsg',
//                'uris' => [
//                    'setting/submsg',
//                ],
//            ],
//            [
//                'name' => '退货地址',
//                'index' => 'setting.address/index',
//                'uris' => [
//                    'setting.address/index',
//                    'setting.address/add',
//                    'setting.address/edit',
//                ],
//            ],
//            [
//                'name' => '上传设置',
//                'index' => 'setting/storage',
//            ],
//            [
//                'name' => '小票打印机',
//                'submenu' => [
//                    [
//                        'name' => '打印机管理',
//                        'index' => 'setting.printer/index',
//                        'uris' => [
//                            'setting.printer/index',
//                            'setting.printer/add',
//                            'setting.printer/edit'
//                        ]
//                    ],
//                    [
//                        'name' => '打印设置',
//                        'index' => 'setting/printer'
//                    ]
//                ]
//            ],
//            [
//                'name' => '其他',
//                'submenu' => [
//                    [
//                        'name' => '清理缓存',
//                        'index' => 'setting.cache/clear'
//                    ]
//                ]
//            ]
//        ],
//    ],
//    'college' => [
//        'name' => '商学院',
//        'icon' => 'icon-guanliyuan',
//        'index' => 'college.lesson/cateindex',
//        'submenu' => [
//            [
//                'name' => '课程分类',
//                'index' => 'college.lesson/cateindex',
//                'uris' => [
//                    'college.lesson/cateindex',
//                    'college.lesson/cateadd',
//                    'college.lesson/cateedit',
//                    'college.lesson/catedelete',
//                ],
//            ],
//            [
//                'name' => '讲师管理',
//                'index' => 'college.lecturer/index',
//                'uris' => [
//                    'college.lecturer/index',
//                    'college.lecturer/add',
//                    'college.lecturer/edit',
//                    'college.lecturer/delete',
//                ],
//            ],
//            [
//                'name' => '课程管理',
//                'index' => 'college.lesson/index',
//                'uris' => [
//                    'college.lesson/index',
//                    'college.lesson/add',
//                    'college.lesson/edit',
//                    'college.lesson/delete',
//                ],
//            ],
//        ]
//    ],
//    'operate' => [
//        'name' => '运营管理',
//        'icon' => 'icon-guanliyuan',
//        'index' => 'operate.index/userSaleData',
//        'submenu' => [
//            [
//                'name' => '用户数据',
//                'index' => 'operate.index/userSaleData',
//                'uris' => [
//                    'operate.index/userSaleData'
//                ],
//            ]
//        ]
//    ],
    'project' => [
        'name' => '系统配置',
        'icon' => 'icon-guanliyuan',
        'index' => 'project.staff/lists',
        'submenu' => [
            [
                'name' => '分公司管理',
                'index' => 'project.company/lists',
                'uris' => [
                    'project.company/lists'
                ],
            ],
            [
                'name' => '部门管理',
                'index' => 'project.department/lists',
                'uris' => [
                    'project.department/lists'
                ],
            ],
            [
                'name' => '员工管理',
                'index' => 'project.staff/lists',
                'uris' => [
                    'project.staff/lists',
                ],
            ],
            [
                'name' => '角色管理',
                'index' => 'project.role/lists',
                'uris' => [
                    'project.role/lists',
                ],
            ],
            [
                'name' => '问题分类',
                'index' => 'project.matter/cate',
                'uris' => [
                    'project.role/lists',
                ],
            ],
        ]
    ],
];
