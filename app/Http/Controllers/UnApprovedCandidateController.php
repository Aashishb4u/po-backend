<?php

namespace App\Http\Controllers;

use App\BaseModels\BaseRejectedCandidateModel;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use App\Models\AttachmentModel;
use App\Models\CandidateFeedbackModel;
use App\Models\CandidateModel;
use App\Models\CandidateSourceModel;
use App\Models\RejectedCandidateModel;
use App\Models\UnApprovedCandidates;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use Illuminate\Http\Request;
use App\Models\TemplateModel;

class UnApprovedCandidateController extends AppController
{
    public function moveCandidate(Request $request)
    {
        $user = $request->input();
        $response = null;
        $error = null;
        $authenticatedUser = $request->user->id_user;
        $status = $user['status'];
        $emailList = $user['emailList'];
        try {
            foreach ($emailList as $key => $userEmail) {
                $candidateObj = new UnApprovedCandidates();
                $user = $candidateObj->getCandidateDetailsByMail($userEmail);
                if (!empty($user) && $user != ApiConstant::DATA_NOT_FOUND) {
                    $user['email'] = $userEmail;
                    $user['phone_no'] = '';
                    $user['status'] = $status;
                    $user['password'] = substr(str_shuffle("1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(6, 8));;
                    $user['role'] = 3;
                    $user['qualification'] = '';
                    $user['experience'] = '';
                    $user['position'] = '';
                    $user['certificate'] = '';
                    $user['cover_letter'] = '';
                    $hash = null;
                    if ($status == 'Active') {
                        $userModelObj = new UserModel();
                        $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($user);
                        if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                            $error = $isUserAlreadyExist;
                        } else {
                            $userDetails = $userModelObj->saveUserDetails($user);
                            if ($userDetails == ApiConstant::DATA_NOT_SAVED) {
                                $error = $userDetails;
                            } else {
                                $userRoleModelObj = new UserRoleModel();
                                $userRole = $userRoleModelObj->insertUserRoleId($userDetails['id'], $user['role']);
                                if ($userRole) {
                                    $user['id'] = $userDetails['id'];
                                    $userModelObj = new CandidateModel();
                                    $userDetails = $userModelObj->candidateApply($user);
                                    if ($userDetails == 1) {
                                        if ($userDetails == 1) {
                                            $sourceData['source_id'] = $user['source_id'];
                                            $sourceData['candidate_id'] = $user['id'];
                                            $sourceData['source_info'] = $user['source_info'];
                                            $candidateSourceModelObj = new CandidateSourceModel();
                                            $candidateSourceInfo = $candidateSourceModelObj->saveCandidateSource($sourceData);
                                            if($candidateSourceInfo)
                                            {
                                                if (!empty($user['written_feedback']))
                                                {
                                                    $feedbackData['feedback'] = $user['written_feedback'];
                                                    $feedbackData['candidate_id'] = $user['id'];
                                                    $feedbackData['recruiter_id'] = $authenticatedUser;
                                                    $feedbackData['feedback_type'] = 1;
                                                    $feedbackModelObj = new CandidateFeedbackModel();
                                                    $feedbackDetails = $feedbackModelObj->saveCandidateFeedback($feedbackData);
                                                }
                                            }
                                            $templateId = 30;
                                            $values['USER_EMAIL'] = $user['email'];
                                            $values['USER_NAME'] = $user['name'];
                                            $values['USER_PASSWORD'] = $user['password'];
                                            $templateModel = new TemplateModel();
                                            $templateData = $templateModel->getTemplateById($templateId);
                                            $renderTemplate = AppUtility::renderEmail($templateData['content']);
                                            $renderTemplateData = AppUtility::renderTemplate($renderTemplate, $values);
                                            $subject = $templateData['subject'];
                                            $body = $renderTemplateData;
                                            $result = AppUtility::sendEmail($subject, $body, $user['email'], $hash);
                                            if ($result == 1) {
                                                $dataDeleted = $candidateObj->deleteByEmail($user['email']);
                                                if ($dataDeleted) {
                                                    $response = array("message" => ApiConstant::APPLY_SUCCESSFULLY);
                                                }
                                            }
                                        }
                                    } else {
                                        $error = ApiConstant::APPLY_FAILED;
                                    }
                                }
                            }
                        }
                    } else {
                        $rejectCandidateObj = new RejectedCandidateModel();
                        $result = $rejectCandidateObj->saveUserDetails($user);
                        if ($result != ApiConstant::DATA_NOT_SAVED) {
                            $dataDeleted = $candidateObj->deleteByEmail($user['email']);
                            if ($dataDeleted) {
                                $response = array("message" => ApiConstant::DELETED_SUCCESSFULLY);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewUnApprovedCandidate(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new UnApprovedCandidates();
            $userRoleModelObj = new UserRoleModel();
            $inputData = $request->input();
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? '';
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $response = $userModelObj->viewUnApprovedCandidate($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewAttachments(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $candidateEmail = $request->input();
        $userRoleModelObj = new UserRoleModel();
        $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
        try {
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $userAttachmentsModelObj = new AttachmentModel();
                $response = $userAttachmentsModelObj->viewAttachments($candidateEmail);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteUnApprovedCandidate(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $recruiter = new UnApprovedCandidates();
                $response = $recruiter->deleteUnApprovedCandidate($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

}
