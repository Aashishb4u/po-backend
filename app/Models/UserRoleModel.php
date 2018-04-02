<?php

namespace App\Models;

use App\BaseModels\BaseUserRoleModel;

class UserRoleModel extends BaseUserRoleModel
{
    public function getUserRole($userDataId)
    {
        $result = BaseUserRoleModel::where('id_user', $userDataId)->first();
        return $result;
    }

    public function insertUserRoleId($id_user, $role)
    {
        $result = $this::insert(['id_user' => $id_user, 'id_role' => $role]);
        return $result;
    }

    public function deleteUserRole($id)
    {
        $result = $this::where('id_user', $id)->delete();
        return $result;
    }
}
