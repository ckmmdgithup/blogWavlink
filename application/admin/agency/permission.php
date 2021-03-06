<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/5/31
 * Time: 8:59
 * agency 的解释
 * 我们每一次对逻辑的操作都非常复杂，如果在model层去操作逻辑的话会导致一个问题，就是代码冗长无比，不便于阅读
 * 现在我的思想是 控制器调用agency 里的操作逻辑，由agency里的方法去和model层沟通 操作数据库
 * 上述语义
 * 1、控制器调用agency的方法
 * 2、agency逻辑去调用model层的增删改查
 * 3、返回成功还是失败
 * 4、由控制器来抛出异常：这里理解为错误异常还是正确的异常
 */

namespace app\admin\agency;


use app\admin\validate\permission as permissionValidate;
use app\common\models\Permission as permissionModel;
use app\common\models\PermissionGroup;
use app\common\validate\number;
use think\Exception;

/**
 * Class permission
 * @package app\admin\agency
 */
class permission extends base
{

    public function initialize()
    {
        $this->validate = new permissionValidate();
        $this->model = new permissionModel();
        $this->success = "保存成功！";
        $this->failed = "保存失败!";
    }

    public function getDataById($data)
    {
        $validate = new number();
        if ($validate->check($data)) {
            try {
                return ['status' => true, 'message' => "ok", 'data' => $this->model->get($data['id'])->toArray()];
            } catch (Exception $exception) {
                return ['status' => false, 'message' => $exception->getMessage()];
            }
        } else {
            return ['status' => false, 'message' => $validate->getError()];
        }
    }

    /**
     * @param $data
     */
    public function saveData($data)
    {
        if (isset($data['id'])) {
            //更新
            if ($this->validate->scene('edit')->check($data)) {
                try {
                    return $this->model->allowField(true)->save($data, ['id' => $data['id']]) ?
                        ['status' => true, 'message' => $this->success] : ['status' => false, 'message' => $this->failed];
                } catch (Exception $exception) {
                    return ['status' => false, 'message' => $exception->getMessage()];
                }
            } else {
                return ['status' => false, 'message' => $this->validate->getError()];
            }
        } else {
            //保存
            if ($this->validate->scene('add')->check($data)) {
                try {
                    return $this->model->allowField(true)->save($data) ?
                        ['status' => true, 'message' => $this->success] : ['status' => false, 'message' => $this->failed];
                } catch (Exception $e) {
                    return ['status' => false, 'message' => $exception->getMessage()];
                }
            } else {
                return ['status' => false, 'message' => $this->validate->getError()];
            }
        }
    }

    public function getAll()
    {
        try {
            return ['status' => true, 'message' => 'ok', 'data' => $this->model->order(['gid' => 'asc'])->paginate()];
        } catch (Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }


    public function savePermission($data, $id)
    {
        $permissions = [];
        foreach ($data as $k => $v) {
            $permissions[] = $k;
        }
        //操作中间表
        try {
            return $this->model->roles()->saveAll($permissions) ?
                ['status' => true, 'message' => $this->success,] : ['status' => false, 'message' => $this->failed];
        } catch (Exception $exception) {
            return [
                'status' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function get_group()
    {
        try {
            $data = (new PermissionGroup())->all()->toArray();
            return $data;
        } catch (Exception $exception) {
            return "hello";
        }
    }

    public function getDataByGroupId($gid)
    {
        try {
            return ['status' => true, 'message' => 'ok', 'data' => $this->model->where(['gid' => $gid])->order(['gid' => 'asc', 'id' => 'asc'])->paginate()];
        } catch (Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }
}