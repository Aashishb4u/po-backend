<?php

namespace App\Http\Controllers;

use App\Helpers\ApiConstant;
use App\Models\FeedbackModel;
use App\Models\UserRoleModel;
use App\Models\WrittenRoundFeedbackModel;
use Illuminate\Http\Request;

class FeedbackController extends AppController
{

    public function saveFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $data = $request->input();
        $error = null;
        $response = null;
        $feedbackData = array();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $feedbackData['email'] = $data['email']?? '';
                $feedbackData['mathematics'] = $data['mathematics'];
                $feedbackData['mathematics_nots'] = $data['mathematics_nots'];
                $feedbackData['basic_science'] = $data['basic_science'];
                $feedbackData['basic_science_nots'] = $data['basic_science_nots'];
                $feedbackData['communication'] = $data['communication'];
                $feedbackData['communication_nots'] = $data['communication_nots'];
                $feedbackData['logical_thinking'] = $data['logical_thinking'];
                $feedbackData['logical_thinking_nots'] = $data['logical_thinking_nots'];
                $feedbackData['problem_solving_capability'] = $data['problem_solving_capability'];
                $feedbackData['problem_solving_capability_nots'] = $data['problem_solving_capability_nots'];
                $feedbackData['ds_and_algo'] = $data['ds_and_algo'];
                $feedbackData['ds_and_algo_nots'] = $data['ds_and_algo_nots'];
                $feedbackData['programming'] = $data['programming'];
                $feedbackData['programming_nots'] = $data['programming_nots'];
                $feedbackData['database'] = $data['database'];
                $feedbackData['database_nots'] = $data['database_nots'];
                $feedbackData['learning_ability'] = $data['learning_ability'];
                $feedbackData['learning_ability_nots'] = $data['learning_ability_nots'];
                $feedbackData['can_work_with_us'] = $data['can_work_with_us'];
                $feedbackData['can_work_with_us_nots'] = $data['can_work_with_us_nots'];
                $feedbackData['feedback'] = $data['feedback'];
                $feedbackData['id_interviewer'] = $authenticatedUser;
                $feedbackData['id_candidate'] = $data['id_candidate'];
                $feedbackModelObj = new FeedbackModel();
                $userData = $feedbackModelObj->saveFeedbackData($feedbackData);
                if ($userData) {
                    $response = array("message" => ApiConstant::ADDED_FEEDBACK);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        print_r($response);
        return $this->returnableResponseData($response, $error);
    }

    public function viewAllFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $response = null;
        $data = $request->input();
        $id_candidate = $data['id_candidate'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $feedbackModelObj = new FeedbackModel();
                $response = $feedbackModelObj->viewAllFeedback($id_candidate);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);

    }

    public function saveWrittenRoundFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $data = $request->input();
        $response = null;
        $error = null;
        $feedbackData = array();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $feedbackData['email'] = $data['email'];
                $feedbackData['mathematics'] = $data['mathematics'];
                $feedbackData['english'] = $data['english'];
                $feedbackData['data_structure'] = $data['data_structure'];
                $feedbackData['c_programming'] = $data['c_programming'];
                $feedbackData['java'] = $data['java'];
                $feedbackData['data_base'] = $data['data_base'];
                $feedbackData['total'] = $data['total'];
                $feedbackData['feedback'] = $data['feedback'];
                $feedbackModelObj = new WrittenRoundFeedbackModel();
                $result = $feedbackModelObj->saveWrittenRoundFeedback($feedbackData);
                if ($result) {
                    $response = array("message" => ApiConstant::ADDED_FEEDBACK);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewWrittenRoundFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $response = null;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $feedbackObj = new WrittenRoundFeedbackModel();
                $response = $feedbackObj->viewWrittenRoundFeedback();
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function editWrittenRoundFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $data = $request->input();
        $response = null;
        $error = null;
        $feedbackData = array();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $feedbackData['email'] = $data['email'];
                $feedbackData['mathematics'] = $data['mathematics'];
                $feedbackData['english'] = $data['english'];
                $feedbackData['data_structure'] = $data['data_structure'];
                $feedbackData['c_programming'] = $data['c_programming'];
                $feedbackData['java'] = $data['java'];
                $feedbackData['data_base'] = $data['data_base'];
                $feedbackData['total'] = $data['total'];
                $feedbackData['feedback'] = $data['feedback'];
                $feedbackModelObj = new WrittenRoundFeedbackModel();
                $result = $feedbackModelObj->editWrittenRoundFeedback($feedbackData);
                if ($result) {
                    $response = array("message" => ApiConstant::UPDATED_FEEDBACK);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getWrittenRoundFeedbackById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $response = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $feedbackObj = new WrittenRoundFeedbackModel();
                $response = $feedbackObj->getWrittenRoundFeedbackById($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

}
