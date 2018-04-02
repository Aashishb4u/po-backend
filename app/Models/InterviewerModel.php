<?php

namespace App\Models;

use App\BaseModels\BaseUserModel;
use App\BaseModels\BaseUserRoleModel;

class InterviewerModel extends BaseUserModel
{
    public function viewInterviewer()
    {
        $response = BaseUserRoleModel::where('user_roles.id_role', '4')
            ->join('users', 'user_roles.id_user', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->select('*')
            ->get();
        return $response;
    }
}
