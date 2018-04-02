<?php

namespace App\Http\Controllers;

use App\Models\AttachmentModel;
use App\Models\CandidateModel;
use App\Models\CandidateSourceModel;
use App\Models\Emails;
use App\Models\ExpectedJoinersModel;
use App\Models\TemplateModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\StaticReplacementModel;
use Illuminate\Http\Request;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use File;
use App\Models\TagModel;
use App\Models\CandidateTagModel;
use Prophecy\Prediction\PredictionInterface;
use Illuminate\Contracts\Filesystem\Cloud;

class CandidateController extends AppController
{
    public function signUp(Request $request)
    {
        $response = null;
        $error = null;
        $hash = 1;
        $message = null;
        $userData = $request->input();
        $userModelObj = new UserModel();
        $templateModelObj = new TemplateModel();
        $email = AppUtility::check_email_address($userData['email']);
        if ($email) {
            $user['email'] = $userData['email'];
            $user['phone_no'] = $userData['phone_no'];
            $user['first_name'] = $userData['first_name'];
            $user['last_name'] = $userData['last_name'];
            $user['dob'] = $userData['dob'];
            $user['subjects'] = $userData['subjects'];
            $user['name'] = $user['first_name'] . " " . $user['last_name'];
            $user['password'] = $userData['password'];
            $user['role'] = 3;
            $user['status'] = 'Active';

        } else {
            $error = ApiConstant::EMAIL_NOT_VALID;
        }
        try {
            if (!empty($user)) {
                $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($user);
                if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                    $error = $isUserAlreadyExist;
                } else {
                    $userDetails = $userModelObj->saveUserDetails($user);
                    if ($userDetails == ApiConstant::DATA_NOT_SAVED) {
                        $error = $userDetails;
                    } else {
                        $user['qualification'] = '';
                        $user['experience'] = '';
                        $user['position'] = '';
                        $user['resume'] = '';
                        $user['certificate'] = '';
                        $user['cover_letter'] = '';
                        $user['id'] = $userDetails['id'];
                        $userModelObj = new CandidateModel();
                        $candidateDetails = $userModelObj->candidateApply($user);
                      if($candidateDetails)
                      {
                          $sourceData['source_id'] = 1;
                          $sourceData['candidate_id'] = $user['id'];
                          $candidateSourceModelObj = new CandidateSourceModel();
                          $candidateSource = $candidateSourceModelObj->saveCandidateSource($sourceData);
                      }
                        $userRoleModelObj = new UserRoleModel();
                        $userRole = $userRoleModelObj->insertUserRoleId($userDetails['id'], $user['role']);
                        if ($userRole) {
                            $templateId = 27;
                            $values['USER_EMAIL'] = $user['email'];
                            $values['USER_NAME'] = $user['name'];
                           $templateData = $templateModelObj->getTemplateById($templateId);
                           $renderTemplate = AppUtility::renderEmail($templateData['content']);
                           $renderTemplateData = AppUtility::renderTemplate($renderTemplate,$values) ;
                            $subject = $templateData['subject'];
                            $body = $renderTemplateData;
                            $result = AppUtility::sendEmail($subject, $body, $user['email'], $hash);
                            if ($result == 1) {
                                $loginuser = new LoginController();
                                $loginuserob = $loginuser->login($request);
                                if ($loginuserob) {
                                    return $loginuserob;
                                } else {
                                    $error = ApiConstant::ERROR_LOGIN;
                                }
                            }
                            $response = array("message" => ApiConstant::SUCCESSFULLY_ADD);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error,$message);
    }

    public function getCandidateProfileById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $candidate = new CandidateModel();
            $response = $candidate->viewCandidateProfileById($authenticatedUser);
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function candidateApplyDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $hash = 1;
        $response = null;
        $message = null;
        $templateModelObj = new TemplateModel();
        $userData = $request->input();
        $user['qualification'] = $userData['qualification'];
        $user['experience'] = $userData['experience'];
        $user['position'] = $userData['position'];
        $user['resume'] = $userData['resume'];
        $user['certificate'] = $userData['certificate'];
        $user['cover_letter'] = $userData['cover_letter'];
        $user['id'] = $authenticatedUser;
        $userModelObj = new UserModel();
        $candidate = $userModelObj->getUserDetails($authenticatedUser);
        try {
            if (!empty($authenticatedUser)) {
                if (!empty($user)) {
                    $userModelObj = new CandidateModel();
                    $candidateDetails = $userModelObj->candidateApply($user);
                    if($candidateDetails ==1)
                    {
                        $candidateObj = new AttachmentModel();
                        $user['email'] = $candidate['email'];
                        $user['name'] = $candidate['name'];
                        $userDetails = $candidateObj->saveResume($user);
                        if ($userDetails == 1) {
                            $templateId = 28;
                            $values['USER_EMAIL'] = $user['email'];
                            $values['USER_NAME'] = $user['name'];
                            $templateData = $templateModelObj->getTemplateById($templateId);
                            $renderTemplate = AppUtility::renderEmail($templateData['content']);
                            $renderTemplateData = AppUtility::renderTemplate($renderTemplate,$values);
                            $subject = $templateData['subject'];
                            $body = $renderTemplateData;
                            $result = AppUtility::sendEmail($subject, $body, $candidate['email'], $hash);
                            if ($result == 1) {
                                $response = array("message" => ApiConstant::APPLY_SUCCESSFULLY);
                            }
                        }

                    } else {
                        $error = ApiConstant::APPLY_FAILED;
                    }
                }
            } else {
                $error = ApiConstant::AUTHENTICATION_FAILED;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function uploadResume(Request $request)
    {
        $error = null;
        $response = null;
        $userData = $request->input();
        $filePath = null;
        $baseUrl = public_path('resume/');
        $file = $request->file('file');
                try {
            if ($file != null) {
                $fileExtension = $file->getClientOriginalExtension();
                if ($fileExtension == 'doc' || $fileExtension == 'pdf' || $fileExtension == 'docx' || $fileExtension == 'odt') {
                    $fileName = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(10, 15));
                    $resumePath = $baseUrl . $fileName . '.' . $fileExtension;
                    $file->move($baseUrl, $fileName . '.' . $fileExtension);
                    chmod($baseUrl . $fileName . '.' . $fileExtension, 0777);
                    $resumename = $fileName . '.' . $fileExtension;
                   // $command = 'export HOME=/tmp && /usr/bin/libreoffice --headless --invisible --convert-to pdf --outdir /var/www/tudip-recruitment-prod/public/resume /var/www/tudip-recruitment-prod/public/resume/'.$resumename;
                    //$success = shell_exec($command);
                    if ($resumePath) {
                        $response = $resumename;
                    }
                } else {
                    $error = ApiConstant::RESUME_FILE_FORMAT_NOT_SUPPORTED;
                }
            } else {
                $error = ApiConstant::FILE_NOT_FOUND;
            }
        } catch (\Exception $e) {

            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addCandidate(Request $request)
    {
        $response = null;
        $hash = 1;
        $error = null;
        $userData = $request->input();
        $userModelObj = new UserModel();
        $message = null;
        $email = AppUtility::check_email_address($userData['email']);
        if ($email) {
            $user['email'] = $userData['email'];
            $user['phone_no'] = $userData['phone_no'];
            $user['first_name'] = $userData['first_name'];
            $user['last_name'] = $userData['last_name'];
            $user['status'] = $userData['status'];
            $user['dob'] = $userData['dob']?? '';
            $user['subjects'] = $userData['subjects']?? '';
            $user['name'] = $user['first_name'] . " " . $user['last_name'];
            $user['password'] = substr(str_shuffle("1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(6, 8));;
            $user['role'] = 3;
            $user['qualification'] = $userData['qualification'] ?? '';
            $user['experience'] = $userData['experience'] ?? '';
            $user['position'] = $userData['position'] ?? '';
            $user['cover_letter'] = '';
            $user['certificate'] = '';
            $user['tag'] = $userData['tag'];
        } else {
            $error = ApiConstant::EMAIL_NOT_VALID;
        }
        try {
            if (!empty($user)) {
                $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($user);
                if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                    $error = $isUserAlreadyExist;
                } else {
                    $userDetails = $userModelObj->saveUserDetails($user);
                    foreach ($user['tag'] as $tag)
                    {
                        $trimTag = trim($tag);
                        $tagModelObj = new TagModel();
                        $tagResponse = $tagModelObj->isTagExist($trimTag);
                        if( $tagResponse != ApiConstant::DATA_NOT_SAVED ){
                            $UpdateData['id_tag']= $tagResponse['id'];
                            $UpdateData['id'] = $userDetails['id'];
                            $candidateTagModelObj  = new CandidateTagModel();
                            $candidateTagResponse = $candidateTagModelObj->saveCandidateTag($UpdateData);
                            if($candidateTagResponse)
                                $response = array("message" => ApiConstant::CANDIDATE_UPDATED_SUCCESSFULLY);
                        }
                    }
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
                                $templateId = 30;
                                $values['USER_EMAIL'] = $user['email'];
                                $values['USER_NAME'] = $user['name'];
                                $values['USER_PASSWORD'] = $user['password'];
                                $templateModel = new TemplateModel();
                                $templateData = $templateModel->getTemplateById($templateId);
                                $renderTemplate = AppUtility::renderEmail($templateData['content']);
                                $renderTemplateData = AppUtility::renderTemplate($renderTemplate,$values) ;
                                $subject = $templateData['subject'];
                                $body = $renderTemplateData;
                                $result = AppUtility::sendEmail($subject, $body, $user['email'], $hash);
                                if ($result == 1) {
                                    $response = array("message" => ApiConstant::APPLY_SUCCESSFULLY);
                                }
                            }
                            else {
                                $error = ApiConstant::APPLY_FAILED;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function showCandidateLogs(Request $request)
    {
        $inputData = $request->input();
        $error = null;
        $response = array();

        try {
            $candidateModelObj = new CandidateModel();
            $candidateData= $candidateModelObj->getCandidateByUserId($inputData);
            $response[0] = $candidateModelObj->getCandidateLogs($inputData);
            $response[1] = $candidateData['created_at']->toDateString();
          $response[2] = $candidateData['name'];
        } catch (\Exception $e) {
            print_r($e->getMessage());
                $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewCandidateEmailLogs(Request $request)
    {
        $inputData = $request->input();
        $error = null;
        try {
            $emailModelObj = new Emails();
            $candidateLogs = $emailModelObj->viewCandidateEmailLogs($inputData);
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($candidateLogs, $error);
    }

}