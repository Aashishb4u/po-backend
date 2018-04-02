<?php

namespace App\Http\Controllers;


use App\Helpers\ApiConstant;
use App\Models\UnApprovedCandidates;
use Illuminate\Http\Request;
use App\Models\RejectedCandidateModel;
use App\Models\UserRoleModel;

class RejectedCandidateController extends AppController
{
    public function viewRejectedCandidate(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $inputData = $request->input();
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? '';
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            $userModelObj = new RejectedCandidateModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $response = $userModelObj->viewRejectedCandidate($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteRejectedCandidate(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $recruiter = new RejectedCandidateModel();
                $response = $recruiter->deleteRejectedCandidate($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteMultipleRejectedCandidates(Request $request)
    {
        $user = $request->input();
        $response = null;
        $error = null;
        $idList = $user['id_list'];
        $RejectedCandidateModel = new RejectedCandidateModel();
        try {
            foreach ($idList as $key => $candidateId) {
                $response = $RejectedCandidateModel->deleteRejectedCandidate($candidateId);
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function moveCandidateToUnApproved(Request $request)
    {
        $user = $request->input();
        $response = null;
        $error = null;
        $status = $user['status'];
        $emailList = $user['id_list'];
        try {
            if ($status == ApiConstant::UNAPPROVED_STATUS)
                foreach ($emailList as $key => $userId) {
                    $rejectedCandidateObj = new RejectedCandidateModel();
                    $user = $rejectedCandidateObj->getCandidateDetailsById($userId);
                    $user['status'] = $status;
                    $candidateObj = new UnApprovedCandidates();
                    $candidate = $candidateObj->saveUnApprovedCandidateDetails($user);
                    if ($candidate) {
                        $rejectedCandidateObj = new RejectedCandidateModel();
                        $response = $rejectedCandidateObj->deleteRejectedCandidate($userId);
                    }
                }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

}
