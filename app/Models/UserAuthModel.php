<?php

namespace App\Models;

use App\BaseModels\BaseUserAuthModel;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;


class UserAuthModel extends BaseUserAuthModel
{
    public function saveAuthToken($userData)
    {

        try {
            if (!empty($userData)) {
                $auth_token = bcrypt($userData['password'] . $userData['email']);
                $this->id_user = $userData['id'];
                $this->auth_token = $auth_token;
                if ($this->save()) {
                    return $this->auth_token;
                } else {
                    return ApiConstant::DATA_NOT_SAVED;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception(AppUtility::getMessageForErrorCode(ApiConstant::DATA_NOT_SAVED), ApiConstant::DATA_NOT_SAVED);
        }

    }

    public function getUserByAuthToken($auth_token)
    {
        $user = $this::where('auth_token', $auth_token)->first();
        return $user;
    }

    public function deleteToken($auth_token, $authenticatedUserId)
    {
        $user = $this::where('auth_token', $auth_token)->where('id_user', $authenticatedUserId)->delete();
        return $user;
    }
}
