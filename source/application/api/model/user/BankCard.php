<?php


namespace app\api\model\user;

use app\api\validate\user\BackCardValidate;
use app\common\model\user\BankCard as BankCardModel;
use think\db\Query;
use think\Exception;

class BankCard extends BankCardModel
{

    private $user;

    private $valid;

    protected $hidden = ['create_time', 'update_time', 'delete_time', 'user_id', 'wxapp_id'];

    public function __construct($user)
    {
        parent::__construct($user);
        $this->user = $user;
        $this->valid = new BackCardValidate();
    }

    /**
     * 获取银行卡列表
     * @param $post
     * @return false|\PDOStatement|string|\think\Collection
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($post){
        ##验证
        $res = $this->valid->scene('lists')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        $filter = [
            'user_id' => $this->user['user_id']
        ];
        return $this->where($filter)->order('is_default','desc')->with(['bank'=>function(Query $query){
            $query->field('bank_name,bank_id');
        }])->select();
    }

    /**
     * 获取银行信息
     * @return \think\model\relation\BelongsTo
     */
    public function bank(){
        return $this->belongsTo('app\api\model\Bank','bank_id','bank_id');
    }

    /**
     * 添加用户银行卡
     * @param $post
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function add($post){
        return $this->editOrAdd(__METHOD__, $post);
    }

    /**
     * 修改用户银行卡
     * @param $post
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function edit($post){
        return $this->editOrAdd(__METHOD__, $post);
    }

    /**
     * 执行添加或者修改
     * @param $scene
     * @param $post
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function editOrAdd($scene, $post){
        ##验证
        $res = $this->valid->scene($scene)->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##接收参数
        $data = [
            'card_number' => str_filter($post['card_number']),
            'user_id' => $this->user['user_id'],
            'bank_address' => str_filter($post['bank_address']),
            'bank_id' => intval($post['bank_id']),
            'is_default' => intval($post['is_default'])
        ];
        $this->startTrans();
        try{
            if($data['is_default']){
                $this->where(['user_id'=>$this->user['user_id'], 'is_default'=>1])->setField('is_default', 0);
            }
            if($scene == 'add'){
                $res = $this->isUpdate(false)->allowField(true)->save($data);
            }else{
                unset($data['user_id']);
                $cardId = intval($post['card_id']);
                $res = $this->save($data, [
                    'card_id' => $cardId,
                    'user_id' => $this->user['user_id']
                ]);
            }

            if($res === false)throw new Exception('添加失败');
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $e->getMessage();
        }
    }

    /**
     * 删除用户银行卡
     * @param $post
     * @return int
     * @throws Exception
     */
    public function del($post){
        ##验证
        $res = $this->valid->scene('del')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##接收参数
        $cardId = intval($post['card_id']);
        ##删除
        return $this->where(['card_id'=>$cardId, 'user_id'=>intval($this->user['user_id'])])->setField('delete_time',time());
    }

}