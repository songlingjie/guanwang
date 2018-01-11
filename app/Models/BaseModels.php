<?php
namespace App\Models;

abstract class BaseModels{

    const pageSize = 10;//分页默认值
    protected $userId = null;

    public function setUserId($user_id)
    {
        $this->userId = $user_id;
    }
}