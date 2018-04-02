<?php

namespace App\Http\Controllers;

use App\BaseModels\BaseUserModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use Illuminate\Http\Request;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use App\Models\UserAuthModel;


class LoginController extends AppController
{
    // login API is used to login user.
    public function login(Request $request)
    {
        $loginData = $request->input();
        $returnableUserData = null;
        $userData['email'] = AppUtility::trimContent($loginData['email']);
        $userData['password'] = isset($loginData['password']) ? $loginData['password'] : null;
        $result = null;
        $error = null;
        $error = $this->validateLoginParams($userData['email'], $userData['password']);
        if (!empty($error)) {
            return $this->returnableResponseData($userData, $error);
        }
        try {
            $userModelObj = new UserModel();
            $user['email'] = $userData['email'];
            $getUserFromDbCount = $userModelObj->getUserCount($user);
            if ($getUserFromDbCount == 1) {
                $email = $userData['email'];
                $userDbPassword = $userModelObj->getUserDetailsByEmail($email);
                if (!empty($userDbPassword)) {
                    $userData['id'] = isset($userDbPassword['id']) ? $userDbPassword['id'] : null;
                    $result = password_verify($userData['password'], $userDbPassword['password']);
                    $userRoleModelObj = new UserRoleModel();
                    $userRole = $userRoleModelObj->getUserRole($userData['id']);
                    if ($result) {
                        $userAuthModelObj = new UserAuthModel();
                            $userAuthModelObj->saveAuthToken($userData);
                            $userModelObj = new BaseUserModel();
                            $userName = $userModelObj::where('id',$userData['id'])->select('users.name')->first();
                            $returnableUserData = array('message' => ApiConstant::LOGGED_IN_SUCCESSFULLY,
                                'auth_token' => $userAuthModelObj['auth_token'],
                                'id_user' => $userAuthModelObj['id_user'],
                                'user_role_id' => $userRole['id_role'],
                                'user_name' => $userName,
                            );
                    } else {
                        $error = ApiConstant::INVALID_USERNAME_PASSWORD;
                    }
                }
            } else {
                $error = ApiConstant::EMAIL_NOT_REGISTERED;
            }
        } catch (\Exception $e) {
            return $this->returnableResponseData($userData, ApiConstant::EXCEPTION_OCCURED, ApiConstant::ERROR_LOGIN);
        }
        return $this->returnableResponseData($returnableUserData, $error);
    }

    // login API is used to validate login parameters.
    public function validateLoginParams($email, $password)
    {
        $error = false;
        if (AppUtility::isNotSetOrEmpty($email)) {
            $error = ApiConstant::EMPTY_EMAIL;
        } elseif (!AppUtility::check_email_address($email)) {
            $error = ApiConstant::EMAIL_NOT_VALID;
        } elseif (AppUtility::isNotSetOrEmpty($password)) {
            $error = ApiConstant::EMPTY_PASSWORD;
        }
        return $error;
    }

    // login API is used to logout user.
    public function logout(Request $request)
    {
        $headerInfo = $request->header();
        $authorization = $headerInfo['authorization'][0] ?? '';
        $auth_token = explode(' ', $authorization);
        $auth_token = $auth_token[1] ?? '';
        $user = new UserAuthModel();
        $authenticatedUser = $user->getUserByAuthToken($auth_token);
        $error = null;
        $userData = array();
        $authenticatedUserId = $authenticatedUser['id_user'];
        if (!empty($authenticatedUser['id_user'])) {
            try {
                if (!empty($auth_token)) {
                    $userAuthModelObj = new UserAuthModel();
                    $userDbAuthToken = $userAuthModelObj->deleteToken($auth_token, $authenticatedUserId);
                    if ($userDbAuthToken == 1) {
                        $userData = array('code' => ApiConstant::HTTP_RESPONSE_CODE_SUCCESS,
                            'message' => ApiConstant::LOGGED_OUT_SUCCESSFULLY,
                        );
                    } else {
                        $error = ApiConstant::ERROR_LOGOUT;
                    }
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($userData, $error);
    }

}

