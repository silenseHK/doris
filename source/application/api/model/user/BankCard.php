<?php


namespace app\api\model\user;

use app\api\validate\user\BackCardValidate;
use app\common\model\user\BankCard as BankCardModel;
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
     * @return \think\Paginator
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getList($post){
        ##验证
        $res = $this->valid->scene('lists')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##接收参数
        $page = isset($post['page']) ? intval($post['page']) : 1;
        $size = isset($post['size']) ? intval($post['size']) : 6;
        $filter = [
            'user_id' => $this->user['user_id']
        ];
        return $this->where($filter)->order('is_default','desc')->paginate($size,true,['page'=>$page]);
    }

    /**
     * 添加用户银行卡
     * @param $post
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function add($post){
        ##验证
        $res = $this->valid->scene('add')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##接收参数
        $data = [
            'card_number' => str_filter($post['card_number']),
            'user_id' => $this->user['user_id'],
            'bank_address' => str_filter($post['bank_address']),
            'bank_name' => str_filter($post['bank_name']),
            'is_default' => intval($post['is_default'])
        ];
        $this->startTrans();
        try{
            if($data['is_default']){
                $this->where(['user_id'=>$this->user['user_id'], 'is_default'=>1])->setField('is_default', 0);
            }
            $res = $this->isUpdate(false)->allowField(true)->save($data);
            if($res === false)throw new Exception('添加失败');
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $e->getMessage();
        }
    }

    public function edit($post){
        ##验证
        $res = $this->valid->scene('edit')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
    }

}