<?php

namespace App\Http\Controllers;
//namespace App\Http\Controllers\WebHookController;

use App\BaseModels\BaseEmailModel;
use App\BaseModels\BaseVendorTagModel;
use App\BaseModels\BaseVendorItemsModel;
use App\BaseModels\BaseUserModel;
use App\Helpers\ApiConstant;
use App\Models\CandidateFeedbackModel;
use App\Models\CandidateModel;
use App\Models\CandidateTemplateSend;
use App\Models\Emails;
use App\Models\ExpectedJoinersModel;
use App\Models\InterviewerModel;
use App\Models\SenderModel;
use App\Models\SettingModel;
use App\Models\TagModel;
use App\Models\CandidateTagModel;
use App\Models\TemplateModel;
use App\Models\TpoConsultancyInstituteModel;
use App\Models\TpoTouchLogsModel;
use App\Models\UnApprovedCandidates;
use App\Models\UserRoleModel;
use App\Models\VendorModel;
use App\Models\VendorTagModel;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Helpers\AppUtility;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use IronMQ\IronMQ;
use DateTime;
use App\Models\RoundModel;
use Mailgun\Mailgun;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\SettingController;
use App\Models\ExpectedJoinersTemplateSendModel;
use function PHPSTORM_META\elementType;


class UserController extends AppController
{
    public function createUser(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $userData = $request->input();
        $userModelObj = new UserModel();
        $email = AppUtility::check_email_address($userData['email']);
        if ($email) {
            $user['email'] = $userData['email'];
            $user['phone_no'] = $userData['phone_no'];
            $user['first_name'] = $userData['first_name'];
            $user['last_name'] = $userData['last_name'];
            $user['name'] = $user['first_name'] . " " . $user['last_name'];
            $user['password'] = $userData['password'];
            $user['role'] = $userData['role'];
            $user['status'] = $userData['status'];
            $user['id'] = $authenticatedUser;//id of user who loged in
        } else {
            $error = ApiConstant::EMAIL_NOT_VALID;
        }
        try {
            if (!empty($user)) {
                $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($user);
                if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                    $error = $isUserAlreadyExist;
                } else {
                    $userRoleModelObj = new UserRoleModel();
                    $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                    if ($userRole->id_role == 1) {
                        $userDetails = $userModelObj->saveUserDetails($user);
                        if ($userDetails == ApiConstant::DATA_NOT_SAVED) {
                            $error = $userDetails;
                        } else {
                            $userRole = $userRoleModelObj->insertUserRoleId($userDetails['id'], $user['role']);
                            if ($userRole) {
                                if ($user['role'] == "2") {
                                    $userData = array("message" => ApiConstant::RECRUITER_CREATED_SUCCESSFULLY);
                                }
                                if ($user['role'] == "4") {
                                    $userData = array("message" => ApiConstant::INTERVIEWER_CREATED_SUCCESSFULLY);
                                }
                            }
                        }
                    } else {
                        $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                    }
                }
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($userData, $error);
    }

    public function editUser(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $UpdateData = array();
        $userModelObj = new UserModel();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1) {
                $data = $request->input();
                $UpdateData['id'] = $data['id'];
                $email = AppUtility::check_email_address($data['email']);
                $user = $userModelObj->getUserDetails($UpdateData['id']);
                if ($email) {
                    $UpdateData['name'] = $data['name'];
                    $UpdateData['email'] = $data['email'];
                    $UpdateData['phone_no'] = $data['phone_no'];
                    $UpdateData['status'] = $data['status'];
                    if ($user->email != $UpdateData['email']) {
                        $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($UpdateData);
                        if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                            $error = $isUserAlreadyExist;
                        }
                    }

                    if ($user->id) {
                        if (empty($data['password'])) {
                            $UpdateData['password'] = $user->password;
                        } else {
                            $UpdateData['password'] = bcrypt($data['password']);
                        }
                        if (empty($error)) {
                            $result = $userModelObj->editUser($UpdateData);
                            if ($result == 1) {
                                $userRoleModelObj = new UserRoleModel();
                                $userRole = $userRoleModelObj->getUserRole($UpdateData['id']);
                                if ($userRole->id_role == 2) {
                                    $response = array("message" => ApiConstant::RECRUITER_UPDATED_SUCCESSFULLY);
                                }
                                if ($userRole->id_role == 4) {
                                    $response = array("message" => ApiConstant::INTERVIEWER_UPDATED_SUCCESSFULLY);
                                }
                            } else {
                                $error = ApiConstant::DATA_NOT_SAVED;
                            }
                        }
                    } else {
                        $error = ApiConstant::ID_NOT_FOUND;
                    }
                } else {
                    $error = ApiConstant::EMAIL_NOT_VALID;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewUser(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $user['id'] = $data['id'] ?? null;
            $recruiter = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1) {
                if (empty($user['id'])) {
                    $response = $recruiter->viewUser();
                } else {
                    $response = $recruiter->viewUserById($user['id']);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewInterviewer(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $user['id'] = $data['id'] ?? null;
            $recruiter = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                if (empty($user['id'])) {
                    $interviewerObj = new InterviewerModel();
                    $response = $interviewerObj->viewInterviewer();

                } else {
                    $response = $recruiter->viewUserById($user['id']);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getUserDetail(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new UserModel();
            $response = $userModelObj->getUserDetails($authenticatedUser);
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteUser(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $id = $request->id;
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1) {
                $recruiter = new UserModel();
                $result = $recruiter->deleteUser($id);
                if ($result == 1) {
                    $userRoleModelObj = new UserRoleModel();
                    $userRole = $userRoleModelObj->getUserRole($id);
                    if ($userRole->id_role == 2) {
                        $response = array("message" => ApiConstant::RECRUITER_DELETED_SUCCESSFULLY);
                    }
                    if ($userRole->id_role == 4) {
                        $response = array("message" => ApiConstant::INTERVIEWER_DELETED_SUCCESSFULLY);
                    }
                } else {
                    $error = ApiConstant::DATA_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }


    public function editVendor(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $tagarray = array();
        $error = null;
        $UpdateData = array();
        $userModelObj = new UserModel();
        $vendorModelObj = new VendorModel();
        $data = $request->input();
        $UpdateData['update_status'] = $data['update_status'];
        $UpdateData['email'] = $data['email'];
        $user = UserModel::find($authenticatedUser);
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                if ($data['id']) {
                    $email = AppUtility::check_email_address($data['email']);
                    $existingUser = $userModelObj->isUserValidWithEmail($data);
                    if ($email) {
                        if ($existingUser) {
                            $UpdateData['id'] = $data['id'];
                            $user = $userModelObj->getUserDetails($UpdateData['id']);
                            switch ($UpdateData['update_status']) {
                                case 'contact' : {
                                    $UpdateData['vendor_name'] = $data['vendor_name'];
                                    $UpdateData['company_name'] = $data['company_name'];
                                    $UpdateData['contact_number'] = $data['contact_number'];
                                    $UpdateData['alternate_email'] = $data['alternate_email'];
                                    $UpdateData['alternate_contact_number'] = $data['alternate_contact_number'];
                                    $UpdateData['address_one'] = $data['address_one'];
                                    $UpdateData['address_two'] = $data['address_two'];
                                    $UpdateData['city'] = $data['city'];
                                    $UpdateData['pin_code'] = $data['pin_code'];
                                    $UpdateData['state'] = $data['state'];
                                    $UpdateData['delete_tag'] = $data['delete_tag'];
                                    $UpdateData['tags'] = $data['tags'];

                                    if ($user->id) {

                                        $result = $userModelObj->editVendor($UpdateData);

                                        if (!empty($UpdateData['delete_tag'])) {
                                            foreach ($UpdateData['delete_tag'] as $deleteTag) {
                                                $UpdateData['id'] = $data['id'];
                                                $UpdateData['id_tag'] = $deleteTag;
                                                $candidateTagModelObj = new VendorTagModel();
                                                $candidateTagResponse = $candidateTagModelObj->deleteVendorTag($UpdateData);
                                                if ($candidateTagResponse) {
                                                    $deleteTermEntry = $vendorModelObj->deleteVendorTerms($UpdateData);
                                                }
                                            }
                                        }

                                        foreach ($UpdateData['tags'] as $tag) {
                                            $trimTag = trim($tag);
                                            $tagModelObj = new TagModel();
                                            $tagResponse = $tagModelObj->isTagIdExist($trimTag);
                                            if (!$tagResponse) {
                                                $response = array("message" => ApiConstant::TAG_NOT_EXIST);
                                            } else {
                                                $UpdateData['id_tag'] = $tag;
                                                $candidateTagModelObj = new VendorTagModel();
                                                $candidateTagResponse = $candidateTagModelObj->saveCandidateTag($UpdateData);
                                                if ($candidateTagResponse) {
                                                    $response = array("message" => ApiConstant::VENDOR_UPDATED_SUCCESSFULLY);
                                                }
                                            }
//
                                        }

                                        if (!$result) {
                                            $error = ApiConstant::VENDOR_UPDATE_FAIL;
                                        }
                                    }
                                    break;
                                }

                                case 'finance' : {
                                    $UpdateData['bank_name'] = $data['bank_name'];
                                    $UpdateData['bank_number'] = $data['bank_details'];
                                    $UpdateData['bank_type'] = $data['bank_type'];
                                    $UpdateData['ifsc_code'] = $data['ifsc_code'];
                                    $UpdateData['gst_number'] = $data['gst_number'];
                                    $UpdateData['pan_number'] = $data['pan_number'];
                                    $UpdateData['terms_id'] = $data['terms_id'];
                                    $UpdateData['terms_data'] = $data['terms_data'];
                                    $UpdateData['terms_tag'] = $data['terms_tag'];
                                    break;
                                }
                                default : {
                                    return $error = ApiConstant::DATA_NOT_SAVED;
                                    break;
                                }
                            }

                            if ($user->id) {
                                $candidateModelObj = new VendorModel();
                                $response = $candidateModelObj->updateVendorDetails($UpdateData);

                                if ($response) {
                                    $response = array("message" => ApiConstant::VENDOR_UPDATED_SUCCESSFULLY);
                                } else {
                                    $error = ApiConstant::ERROR_EMAIL_UPDATE;
                                }

                            } else {
                                $error = ApiConstant::INVALID_ID;
                            }
                        } else {
                            $error = ApiConstant::EMAIL_NOT_FOUND;
                        }
                    } else {
                         $error = ApiConstant::EMAIL_NOT_VALID;
                    }

                } else {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function searchCandidateDetails(Request $request)
    {
        $response = null;
        $error = null;
        $nameofCandidate = array();
        $date = $request->input();
        $candidate = new UserModel();
        $response = $candidate->searchCandidate($date);
        if (!empty($response)) {

            foreach ($response as $list) {
                $nameofCandidate[] = $list['name'];
            }
        }
        return $this->returnableResponseData($nameofCandidate, $error);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteVendor(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            DB::beginTransaction();
            $data = $request->input();
            $id = $data['id'];
            $userModelObj = new UserModel();
            $userBaseModel = new BaseUserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $deleteUserRole = $userRoleModelObj->deleteUserRole($id);
                $candidateModelObj = new VendorModel();
                if ($deleteUserRole) {
                    $itemDependancy = $candidateModelObj->checkVendorDependancy($id);
                    if (!empty($itemDependancy)) {
                        $error = $error = ApiConstant::VENDOR_DEPENDANCY;
                        $message = array("message" => 'Vendor used in purchase Order');
                    } else {
                        $vendorItemsObject = new BaseVendorItemsModel();
                        $response = $vendorItemsObject::where('vendor_id',$id)->first();
                        if (empty($response)) {
                            $itemTagsObject = new BaseVendorTagModel();
                            $response = $itemTagsObject::where('vendor_id',$id)->delete();
                            if ($response) {
                                $deleteCandidate = $userBaseModel::where('users.id',$id)->delete();
                                if ($deleteCandidate) {
                                    $deleteCandidate = $candidateModelObj->deleteVendor($id);
                                }
                            }
                            if ($deleteCandidate) {
                                DB::commit();
                                $response = array("message" => ApiConstant::CANDIDATE_DELETED);
                            }
                        } else {
                            $error = $error = ApiConstant::ITEM_Dependancy_VENDOR_ITEMS;
                            $message = array("message" => 'Vendor used in Vendor Item');
                        }

                    }
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }


    public function forgotPassword(Request $request)
    {
        $error = null;
        $response = null;
        $message = null;
        $hash = 1;
        try {
            $user = $request->input();
            $user['email'] = $this->getTrimmedString($user['email']);
            if ($user['email'] != null) {
                $userModel = new UserModel();
                $userCount = $userModel->getUserCount($user);
                if ($userCount == 1) {
                    $userDetails = $userModel->userDetailsByEmail($user);
                    $id = $userDetails->id;
                    $bcryptId = bcrypt($id);
                    $templateId = 31;
                    $values['USER_EMAIL'] = $userDetails['email'];
                    $values['USER_NAME'] = $userDetails['name'];
                    $values['USER_PASSWORD'] = $userDetails['password'];
                    $values['BCRYPT_ID'] = $bcryptId;
                    $saveRemenberId = $userModel->saveRemeberId($bcryptId, $id);
                    if ($saveRemenberId) {
                        $templateModel = new TemplateModel();
                        $templateData = $templateModel->getTemplateById($templateId);
                        $renderTemplate = AppUtility::renderEmail($templateData['content']);
                        $renderTemplateData = AppUtility::renderTemplate($renderTemplate, $values);

                        //---------------------------------------------------
                        $subject = $templateData['subject'];
                        $body = $renderTemplateData;
                        $result = AppUtility::sendEmail($subject, $body, $userDetails->email, $hash);
                        if ($result == 1) {
                            $response = array($userDetails->email, 'message' => "Please check your email account");
                        }
                    } else {
                        $error = ApiConstant::DATA_NOT_SAVED;
                    }

                } else {
                    $error = ApiConstant::INVALID_USERNAME;
                }
            } else {
                $error = ApiConstant::PARAMETER_MISSING;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function sendMail(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $user = $request->input();
        $userType = 'candidate';
        $emailList = $user['emailList'];
        $template_id = $user['templateId'] ?? '';
        $response = null;
        $error = null;
        $mute = $user['mute'] ?? '';
        $dateTime = $user['dateTime'] ?? '';
        if (!empty($dateTime)) {
            $datePieces = explode(" ", $dateTime);
            $createDate = date_create($datePieces[0]);
            $formatedDate = date_format($createDate, "dS F Y ");
            $dateTime = $formatedDate . ' ' . $datePieces[1] . ' ' . $datePieces[2] . ' ' . $datePieces[3];
        }
        $recruiter_id = $authenticatedUser;
        $round_id = $user['id_round'];
        $roundModelObj = new RoundModel();
        $round_data = $roundModelObj->getRoundById($round_id);
        $templateModel = new TemplateModel();
        $template = $templateModel->getTemplateDetailsById($template_id);
        $renderTemplate = AppUtility::renderEmail($template['content']);
        $userModel = new UserModel();
        $candidateModelObj = new CandidateModel();
        try {
            $delay = 1;
            foreach ($emailList as $list) {
                $emailObj = new Emails();
                $user = $userModel->getUserDetails($list);
                $candidateData = $candidateModelObj->getCandidateByUserId($user->id);
                if ($user) {
                    if (!empty($mute)) {
                        if (empty($dateTime)) {
                            $dateTime = $candidateData->date_time;
                        }
                        $muteCandidate = $candidateModelObj->updateCandidateStage($user->id, $round_id, $dateTime);
                        if ($muteCandidate) {
                            if ($template_id == null) {
                                $template_id = 0;
                            }
                            $insertData = $emailObj->insertEmail($user->email, $user->name, $template_id, $template['content'], $template['subject'], $round_id, $recruiter_id, $user->id, $dateTime, $userType);
                            if ($insertData) {
                                $response = ApiConstant::CANDIDATE_MUTED_SUCCESSFULLY;
                            }
                        }
                    } else {
                        $values['USER_EMAIL'] = $user->email;
                        $values['USER_NAME'] = $user->name;
                        $values['DATE_TIME_INFROMATION'] = $dateTime;
                        $templateSendModelObj = new CandidateTemplateSend();
                        $result = $templateSendModelObj->isTemplateSend($template_id, $user->id,$user->created_at);
                        $renderTemplateData = AppUtility::renderTemplate($renderTemplate, $values);
                        $template['content'] = $renderTemplateData;
                        if(!$candidateData->block_mail)
                        {
                            if ($result == 1) {
                                $isSent = 0;
                                $templateSendModelObj->insertTemplateId($template_id, $user->id, $recruiter_id, $isSent);
                                $response = $emailObj->insertEmail($user->email, $user->name, $template_id, $template['content'], $template['subject'], $round_id, $recruiter_id, $user->id, $dateTime, $userType);
                                //$ironmq = $this->getIronMq();
                                /*$delay = $delay + 2;
                                $ironMessage = array('email_id' => $response->id);
                                $ironmq->postMessage($_ENV['IRON_MQ_QUEUE_NAME'], json_encode($ironMessage), array(
                                    "timeout" => 60, # Timeout, in seconds. After timeout, item will be placed back on queue. Defaults to 60.
                                    "delay" => $delay, # The item will not be available on the queue until this many seconds have passed. Defaults to 0.
                                    "expires_in" => 2 * 24 * 3600 # How long, in seconds, to keep the item on the queue before it is deleted.
                                ));*/
                                $webhookControllerObj = new WebhookController();
                                $sendEmail = $webhookControllerObj->sendEmail($response->id);
                                $response = ApiConstant::EMAIL_SENT;
                            }
                            else{
                                $error = ApiConstant::EMAIL_ALREADY_SENT;
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

    public function viewVendors(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();

            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $response = $userModelObj->viewVendors();
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());die();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getVendorById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $userModelObj = new UserModel();
                $response = $userModelObj->viewVendorById($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    /* update profile details of admin and recruiter*/
    public function updateProfileDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $filePath = null;
        $message = null;
        $imgData = null;
        $imageName = null;
        $baseUrl = public_path('upload/');
        $UpdateData = array();
        $data = $request->input();
        try {
            if (!empty($data)) {
                $UpdateData['id'] = $authenticatedUser;
                $userModel = new UserModel();
                $user = $userModel->getUserDetails($UpdateData['id']);
                //$user = BaseUserModel::where('id', $UpdateData['id'])->first();
                $UpdateData['name'] = $data['name'];
                $UpdateData['email'] = $data['email'];
                $UpdateData['phone_no'] = $data['phone_no'];
                $UpdateData['image'] = $data['image'];
                if ($user) {
                    if ($UpdateData['image'] != null) {
                        $image = explode(',', $UpdateData['image']);
                        $data1 = $image[1];
                        $data1 = str_replace(' ', '+', $data1);
                        $imgData = base64_decode($data1);
                        $fInfo = finfo_open();
                        $mimeType = finfo_buffer($fInfo, $imgData, FILEINFO_MIME_TYPE);
                        $imageType = explode('/', $mimeType);
                        $imageExtension = $imageType[1];
                        if ($imageExtension == 'jpeg' || $imageExtension == 'jpg' || $imageExtension == 'png' || $imageExtension == 'bmp') {
                            $imageName = substr(str_shuffle("abefghijklmnopqrstuvwxyzABEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(10, 15));
                            $imageName = $imageName . "." . $imageExtension;
                            $filePath = $baseUrl . $imageName;
                            $UpdateData['image'] = $imageName;
                            $error = AppUtility::validBase64Params($data1);
                            if ($error == null) {
                                $userModel = new UserModel();
                                $response = $userModel->updateProfile($UpdateData, $filePath, $imgData);
                                if ($response == ApiConstant::EXCEPTION_OCCURED || $response == ApiConstant::RECORD_NOT_EXIST || $response == ApiConstant::DATA_NOT_SAVED) {
                                    $error = $response;
                                } else {
                                    DB::commit();
                                }
                            }
                        } else {
                            $error = ApiConstant::FORMAT_NOT_SUPPORTED;
                        }
                    } else {
                        $updateObj = new UserModel();
                        $response = $updateObj->updateProfile($UpdateData, $filePath, $imgData);
                        if ($response == ApiConstant::EXCEPTION_OCCURED || $response == ApiConstant::RECORD_NOT_EXIST || $response == ApiConstant::DATA_NOT_SAVED) {
                            $error = $response;
                        } else {
                            DB::commit();
                        }
                    }
                } else {
                    $error = ApiConstant::INVALID_ID;
                }

            } else {
                $error = ApiConstant::DATA_NOT_FOUND;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function resetPasswordDetails(Request $request)
    {
        $error = null;
        $response = null;
        $user = $request->input();
        try {
            $remberId = $this->getTrimmedString($user['id']);

            $user['password'] = $this->getTrimmedString($user['password']);
            if ($remberId != null) {
                $userModel = new UserModel();
                $userDetails = $userModel->getUserDetailsByRememberId($remberId);
                if ($userDetails == ApiConstant::LINK_ALREADY_USED) {
                    $error = ApiConstant::LINK_ALREADY_USED;
                } else {
                    $user['id'] = $userDetails['id'];
                    $reset = $userModel->resetPasswordDetails($user);
                    if ($reset) {
                        $result = $userModel->removeRememberId($user);
                        if ($result) {
                            $response = ApiConstant::PASSWORD_CHANGE_SUCCESSFULLY;
                        }
                    } else {
                        $error = ApiConstant::ERROR_PASSWORD_UPDATE;
                    }


                }
            } else {
                $error = ApiConstant::PARAMETER_MISSING;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function changePassword(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $userData = $request->input();
        $error = null;
        $returnData = null;
        try {
            $user['password'] = $userData ['password'];
            $user['old_password'] = $userData ['old_password'];
            $user['id'] = $authenticatedUser;
            if (!empty($userData)) {
                $userModelObj = new UserModel();
                $result = $userModelObj->changePassword($user);
                if ($result['message'] == ApiConstant::PASSWORD_CHANGE_SUCCESSFULLY) {
                    $returnData = $result;
                } else {
                    $error = ApiConstant::RECORD_NOT_EXIST;
                }
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($returnData, $error);
    }

    public function getIronMq()
    {
        return new IronMQ(array(
            "token" => $_ENV['IRON_MQ_TOKEN'],
            "project_id" => $_ENV['IRON_MQ_PROJECT_ID']));
    }

    public function barGraph(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $message = null;
        $response = null;
        $graphInput = $request->input();
        $graphData['from_date'] = $graphInput['from_date'] ?? null;
        $graphData['to_date'] = $graphInput['to_date'] ?? null;
        $userControllerObj = new UserModel();

        $template = new TemplateModel();
        try {
            if (empty($graphData['from_date']) || empty($graphData['to_date'])) {
                $result = $userControllerObj->barGraph();
                if (!empty($result[1] > 0)) {

                    $response = $result;
                } else {
                    $response = $template->viewTemplateForGraph();
                }
            } else {
                $result = $userControllerObj->barGraphWithinDate($graphData);
                if ($result[1] > 0) {
                    $response = $result;
                } else {
                    $response = $template->viewTemplateForGraph();
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function sendTestMail(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $user = $request->input();
        $hash = 1;
        $userModelObj = new UserModel();
        $userData = $userModelObj->viewUserDetailById($authenticatedUser);
        $emailList = $userData['email'];
        $template_id = $user['templateId'];
        $response = null;
        $dateTime = $user['dateTime']?? '';
        $error = null;
        $message = null;
        $values['USER_EMAIL'] = $emailList;
        $values['USER_NAME'] = $userData['name'];
        $values['DATE_TIME_INFROMATION'] = $dateTime;
        $templateModel = new TemplateModel();
        $template = $templateModel->getTemplateDetailsById($template_id);
        $renderTemplate = AppUtility::renderEmail($template['content']);
        $renderTemplateData = AppUtility::renderTemplate($renderTemplate, $values);
        $subject = $template['subject'];
        $body = $renderTemplateData;
        try {
            $result = AppUtility::sendEmail($subject, $body, $emailList, $hash);
            if ($result == 1) {
                $response = array($emailList, 'message' => "Email send successfully.");
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);

    }

    // ExpectedJoiners API
    public function addExpectedJoiners(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
        $email = AppUtility::check_email_address($userData['email']);
        if ($email) {
            $user['email'] = $userData['email'];
            $user['phone_no'] = $userData['phone_no'];
            $user['qualification'] = $userData['qualification'];
            $user['first_name'] = $userData['first_name'];
            $user['last_name'] = $userData['last_name'];
            $user['status'] = $userData['status'];
            $user['college_name'] = $userData['college_name'];
            $user['batch'] = $userData['batchId'];
            $user['stage'] = 29;
            $user['name'] = $user['first_name'] . " " . $user['last_name'];
            $user['role'] = 5;
        } else {
            $error = ApiConstant::EMAIL_NOT_VALID;
        }
        try {
            if (!empty($user)) {
                $expectedJoinersModelObj = new ExpectedJoinersModel();
                $isUserAlreadyExist = $expectedJoinersModelObj->isUserAlreadyExist($user);
                if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                    $error = $isUserAlreadyExist;
                } else {
                    $expectedJoinerDetails = $expectedJoinersModelObj->addExpectedJoinerDetails($user);
                    if ($expectedJoinerDetails == ApiConstant::DATA_NOT_SAVED) {
                        $error = $expectedJoinerDetails;
                    } else {
                        $response = ApiConstant::EXPECTED_JOINERS_CREATED_SUCCESSFULLY;
                    }
                }

            }

        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function editExpectedJoiners(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $UpdateData = array();
        $expectedJoinersModelObj = new ExpectedJoinersModel();
        $data = $request->input();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $expectedJoinerData = $expectedJoinersModelObj->getExpectedJoinerDetailsByEmail($data['email']);

            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                if ($data['id']) {
                    $email = AppUtility::check_email_address($data['email']);
                    if ($email) {
                        $UpdateData['id'] = $data['id'];
                        $UpdateData['name'] = $data['name'];
                        $UpdateData['qualification'] = $data['qualification'];
                        $UpdateData['email'] = $data['email'];
                        $UpdateData['phone_no'] = $data['phone_no'];
                        $UpdateData['status'] = $data['status'];
                        $UpdateData['id_user'] = $authenticatedUser;
                        $UpdateData['stage'] = $data['stage'];
                        $UpdateData['batch'] = $data['batchId'];
                        $UpdateData['college_name'] = $data['college_name'];

                    } else {
                        return $error = ApiConstant::EMAIL_NOT_VALID;
                    }
                    if ($expectedJoinerData == ApiConstant::DATA_NOT_FOUND) {
                        $result = $expectedJoinersModelObj->editExpectedJoinerDetails($UpdateData);
                        if ($result) {
                            $response = array("message" => ApiConstant::EXPECTED_JOINER_UPDATED_SUCCESSFULLY);
                        }
                    } else {

                        if ($expectedJoinerData['id'] == $data['id']) {
                            $result = $expectedJoinersModelObj->editExpectedJoinerDetails($UpdateData);
                            if ($result) {
                                $response = array("message" => ApiConstant::EXPECTED_JOINER_UPDATED_SUCCESSFULLY);
                            }
                        } else {
                            $error = ApiConstant::EMAIL_ALREADY_EXIST;
                        }

                    }

                } else {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewExpectedJoiners(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $expectedJoinersModelObj = new ExpectedJoinersModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? '';
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $response = $expectedJoinersModelObj->viewExpectedJoiners($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteExpectedJoiners(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $id = $data['id'];
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $expectedJoinersModelObj = new ExpectedJoinersModel();
                $deleteCandidate = $expectedJoinersModelObj->deleteExpectedJoinerDetails($id);
                if ($deleteCandidate) {
                    $response = array("message" => ApiConstant::EXPECTED_JOINER_DELETED);
                }

            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getExpectedJoinerDetailsById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $expectedJoinersModelObj = new ExpectedJoinersModel();
                $response = $expectedJoinersModelObj->getExpectedJoinerDetails($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function viewExpectedJoinerDetailsByBatch(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $expectedJoinersModelObj = new ExpectedJoinersModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['batch'] = $inputData['batchId'];
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? '';
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $response = $expectedJoinersModelObj->viewExpectedJoinerDetailsByBatch($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function sendMailToExpectedJoiners(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $user = $request->input();
        $userType = 'expectedJoiner';
        $emailList = $user['emailList'];
        $template_id = $user['templateId'] ?? '';
        $response = null;
        $error = null;
        $mute = $user['mute'] ?? '';
        $dateTime = $user['dateTime'] ?? '';
        if (!empty($dateTime)) {
            $datePieces = explode(" ", $dateTime);
            $createDate = date_create($datePieces[0]);
            $formatedDate = date_format($createDate, "jS F Y ");
            $dateTime = $formatedDate . ' ' . $datePieces[1] . ' ' . $datePieces[2] . ' ' . $datePieces[3];
        }
        $recruiter_id = $authenticatedUser;
        $round_id = $user['id_round'];
        $roundModelObj = new RoundModel();
        $round_data = $roundModelObj->getRoundById($round_id);
        $templateModel = new TemplateModel();
        $template = $templateModel->getTemplateDetailsById($template_id);
        $renderTemplate = AppUtility::renderEmail($template['content']);
        try {
            $delay = 1;
            foreach ($emailList as $list) {
                $emailObj = new Emails();
                $expectedJoinersModelObj = new ExpectedJoinersModel();
                $user = $expectedJoinersModelObj->getExpectedJoinerDetailsByID($list);
                if ($user) {
                    if (!empty($mute)) {
                        $muteCandidate = $expectedJoinersModelObj->updateExpectedJoinerStatus($user->id, $round_data['round_name']);
                        if ($muteCandidate) {
                            if ($template_id == null) {
                                $template_id = 0;
                            }
                            $insertData = $emailObj->insertEmail($user->email, $user->name, $template_id, $template['content'], $template['subject'], $round_id, $recruiter_id, $user->id, $dateTime, $userType);
                            if ($insertData) {
                                $response = ApiConstant::CANDIDATE_MUTED_SUCCESSFULLY;
                            }
                        }
                    } else {
                        $values['USER_EMAIL'] = $user->email;
                        $values['USER_NAME'] = $user->name;
                        $values['DATE_TIME_INFROMATION'] = $dateTime;
                        $result = $expectedJoinersModelObj->isTemplateSend($template_id, $user->id);
                        $renderTemplateData = AppUtility::renderTemplate($renderTemplate, $values);
                        $template['content'] = $renderTemplateData;
                        if ($result == 1) {
                            $isSent = 0;
                            $expectedJoinersTemplateModel = new ExpectedJoinersTemplateSendModel();
                            $insertTemplateId =  $expectedJoinersTemplateModel->insertTemplateId($template_id, $user->id, $recruiter_id, $isSent);
                            $response = $emailObj->insertEmail($user->email, $user->name, $template_id, $template['content'], $template['subject'], $round_id, $recruiter_id, $user->id, $dateTime, $userType);
//                            $ironmq = $this->getIronMq();
//                            $delay = $delay + 2;
//                            $ironMessage = array('email_id' => $response->id);
//                            $ironmq->postMessage($_ENV['IRON_MQ_QUEUE_NAME'], json_encode($ironMessage), array(
//                                "timeout" => 60, # Timeout, in seconds. After timeout, item will be placed back on queue. Defaults to 60.
//                                "delay" => $delay, # The item will not be available on the queue until this many seconds have passed. Defaults to 0.
//                                "expires_in" => 2 * 24 * 3600 # How long, in seconds, to keep the item on the queue before it is deleted.
//                            ));
                            $webhookControllerObj = new WebhookController();
                            $sendEmail = $webhookControllerObj->sendEmail($response->id);
                            $response = ApiConstant::EMAIL_SENT;
//                            $response = 'Inserted into queue';
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

    // TPO-Consultancy-Institute API

    public function addTpoDetails(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
        $email = AppUtility::check_email_address($userData['email']);
        if ($email) {
            $user['name'] = $userData['name'];
            $user['email'] = $userData['email'];
            $user['phone_no'] = $userData['phone_no'];
            $user['status'] = $userData['status'];
            $user['college_name'] = $userData['college_name'];
            $user['website'] = $userData['website'] ?? '';
            $user['notes'] = $userData['notes'] ?? '';
            $user['representative_type'] = $userData['representative_type'];
            $user['fresher_charges'] = $userData['fresher_charges'] ?? '';
            $user['experience_charges'] = $userData['experience_charges'] ?? '';
            $user['stage'] = 25;
            $user['last_touch'] = $userData['last_touch'];
        } else {
            $error = ApiConstant::EMAIL_NOT_VALID;
        }
        try {
            if (!empty($user)) {
                $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
                $isUserAlreadyExist = $tpoConsultancyInstituteModelObj->isUserAlreadyExist($user);
                if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                    $error = $isUserAlreadyExist;
                } else {
                    $tpoDetails = $tpoConsultancyInstituteModelObj->addTpoDetails($user);
                    if ($tpoDetails == ApiConstant::DATA_NOT_SAVED) {
                        $error = $tpoDetails;
                    } else {
                        $tpoTouchObjModel = new TpoTouchLogsModel();
                        $tpoTouchdata = $tpoTouchObjModel->saveTouchLogs($tpoDetails);
                        $response = ApiConstant::TPO_CREATED_SUCCESSFULLY;
                    }
                }

            }

        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function editTpoDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $UpdateData = array();
        $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
        $data = $request->input();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $tpoData = $tpoConsultancyInstituteModelObj->getTpoDetailsByEmail($data['email']);
            if ($data['last_touch'] > $tpoData['last_touch']) {
                $tpoTouchObjModel = new TpoTouchLogsModel();
                $tpoTouchdata = $tpoTouchObjModel->saveTouchLogs($data);
            }
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                if ($data['id']) {
                    $email = AppUtility::check_email_address($data['email']);
                    if ($email) {
                        $UpdateData['id'] = $data['id'];
                        $UpdateData['name'] = $data['name'];
                        $UpdateData['email'] = $data['email'];
                        $UpdateData['phone_no'] = $data['phone_no'];
                        $UpdateData['status'] = $data['status'];
                        $UpdateData['id_user'] = $authenticatedUser;
                        $UpdateData['stage'] = $data['stage'];
                        $UpdateData['college_name'] = $data['college_name'];
                        $UpdateData['website'] = $data['website'] ?? '';
                        $UpdateData['notes'] = $data['notes'] ?? '';
                        $UpdateData['representative_type'] = $data['representative_type'];
                        $UpdateData['fresher_charges'] = $data['fresher_charges'] ?? '';
                        $UpdateData['experience_charges'] = $data['experience_charges'] ?? '';
                        $UpdateData['last_touch'] = $data['last_touch']?? '';

                    } else {
                        return $error = ApiConstant::EMAIL_NOT_VALID;
                    }
                    if ($tpoData == ApiConstant::DATA_NOT_FOUND) {
                        $result = $tpoConsultancyInstituteModelObj->editTpoDetails($UpdateData);
                        if ($result) {
                            $response = array("message" => ApiConstant::TPO_UPDATED_SUCCESSFULLY);
                        }
                    } else {

                        if ($tpoData['id'] == $data['id']) {
                            $result = $tpoConsultancyInstituteModelObj->editTpoDetails($UpdateData);
                            if ($result) {
                                $response = array("message" => ApiConstant::TPO_UPDATED_SUCCESSFULLY);
                            }
                        } else {
                            $error = ApiConstant::EMAIL_ALREADY_EXIST;
                        }

                    }

                } else {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewTpoDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['representative_type'] = $inputData['representative_type'] ?? '';
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? '';
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $response = $tpoConsultancyInstituteModelObj->viewTpoDetails($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteTpoDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $id = $data['id'];
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
                $deleteCandidate = $tpoConsultancyInstituteModelObj->deleteTpoDetails($id);
                if ($deleteCandidate) {
                    $response = array("message" => ApiConstant::TPO_DELETED);
                }

            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getTpoDetailsById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
                $response = $tpoConsultancyInstituteModelObj->getTpoDetailsById($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function viewTpoTouchLogs(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $tpoTouchObjModel = new TpoTouchLogsModel();
                $response = $tpoTouchObjModel->viewTpoTouchLogs($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function sendMailToTpo(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $user = $request->input();
        $userType = 'tpo';
        $emailList = $user['emailList'];
        $template_id = $user['templateId'] ?? '';
        $response = null;
        $error = null;
        $mute = $user['mute'] ?? '';
        $dateTime = $user['dateTime'] ?? '';
        if (!empty($dateTime)) {
            $datePieces = explode(" ", $dateTime);
            $createDate = date_create($datePieces[0]);
            $formatedDate = date_format($createDate, "jS F Y ");
            $dateTime = $formatedDate . ' ' . $datePieces[1] . ' ' . $datePieces[2] . ' ' . $datePieces[3];
        }
        $recruiter_id = $authenticatedUser;
        $round_id = $user['id_round'];
        $roundModelObj = new RoundModel();
        $round_data = $roundModelObj->getRoundById($round_id);
        $templateModel = new TemplateModel();
        $template = $templateModel->getTemplateDetailsById($template_id);
        $renderTemplate = AppUtility::renderEmail($template['content']);
        try {
            $delay = 1;
            foreach ($emailList as $list) {
                $emailObj = new Emails();
                $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
                $user = $tpoConsultancyInstituteModelObj->getTpoDetailsWithID($list);
                if ($user) {
                    if (!empty($mute)) {
                        $muteCandidate = $tpoConsultancyInstituteModelObj->updateTpoStatus($user->id, $round_id);
                        if ($muteCandidate) {
                            if ($template_id == null) {
                                $template_id = 0;
                            }
                            $insertData = $emailObj->insertEmail($user->email, $user->name, $template_id, $template['content'], $template['subject'], $round_id, $recruiter_id, $user->id, $dateTime, $userType);
                            if ($insertData) {
                                $response = ApiConstant::CANDIDATE_MUTED_SUCCESSFULLY;
                            }
                        }
                    } else {
                        $values['USER_EMAIL'] = $user->email;
                        $values['USER_NAME'] = $user->name;
                        $values['DATE_TIME_INFROMATION'] = $dateTime;
                        $result = $tpoConsultancyInstituteModelObj->isTemplateSend($template_id, $user->id);
                        $renderTemplateData = AppUtility::renderTemplate($renderTemplate, $values);
                        $template['content'] = $renderTemplateData;
                        if ($result == 1) {
                            $response = $emailObj->insertEmail($user->email, $user->name, $template_id, $template['content'], $template['subject'], $round_id, $recruiter_id, $user->id, $dateTime, $userType);
//                            $ironmq = $this->getIronMq();
//                            $delay = $delay + 2;
                           /* $ironMessage = array('email_id' => $response->id);
                            $ironmq->postMessage($_ENV['IRON_MQ_QUEUE_NAME'], json_encode($ironMessage), array(
                                "timeout" => 60, # Timeout, in seconds. After timeout, item will be placed back on queue. Defaults to 60.
                                "delay" => $delay, # The item will not be available on the queue until this many seconds have passed. Defaults to 0.
                                "expires_in" => 2 * 24 * 3600 # How long, in seconds, to keep the item on the queue before it is deleted.
                            ));*/
                            $webhookControllerObj = new WebhookController();
                            $sendEmail = $webhookControllerObj->sendEmail($response->id);
                            $response = ApiConstant::EMAIL_SENT;
                            $response = 'Inserted into queue';
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

    public function saveCandidateFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $inputData = $request->input();
        $error = null;
        $response = null;
        if (empty($inputData['feedback_type'])) {
            $inputData['feedback_type'] = 2;
        }
        try {
            $userModelObj = new UserModel();
            $userdetails = $userModelObj->getUserDetails($authenticatedUser);
            $inputData['recruiter_id'] = $userdetails['id'];
            $candidateFeedbackModelObj = new CandidateFeedbackModel();
            $response = $candidateFeedbackModelObj->saveCandidateFeedback($inputData);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewCandidateFeedback(Request $request)
    {
        $inputData = $request->input();
        $error = null;
        try {
            $candidateFeedbackModelObj = new CandidateFeedbackModel();
            $candidateFeedback = $candidateFeedbackModelObj->getCandidateFeedback($inputData);
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($candidateFeedback, $error);
    }

    public function deleteCandidateFeedback(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $id = $data['id'];
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $candidateFeedbackModelObj = new CandidateFeedbackModel();
                $deleteCandidateFeedback = $candidateFeedbackModelObj->deleteCandidateFeedback($id);
                if ($deleteCandidateFeedback) {
                    $response = array("message" => ApiConstant::CANDIDATE_FEEDBACK_DELETED);
                }

            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function searchCandidateByInput(Request $request)
    {
        $inputData = $request->input();
        $error = null;
        try {
            $userModelObj = new UserModel();
            $response = $userModelObj->searchCandidateByInput($inputData);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    function save(Request $request)
    {
        $file = $request->file('file');
        $fileExtension = $file->getClientOriginalExtension();
        $baseUrl = public_path('csv/');
        $response = null;
        $i = 0;
        try {
            $fileName = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(10, 15));
            $csvPath = $baseUrl . $fileName . '.' . $fileExtension;
            $file->move($baseUrl, $fileName . '.' . $fileExtension);
            chmod($baseUrl . $fileName . '.' . $fileExtension, 0777);
            $csvname = $fileName . '.' . $fileExtension;
            if ($file->getClientSize() > 0) {
                $file1 = fopen($baseUrl . '/' . $csvname, "r");
                $getData = fgetcsv($file1, 1024, ",");
               while (($getData = fgetcsv($file1, 1, ",")) !== FALSE) {
                $unApproveModelObj = new UnApprovedCandidates();
                    $unApproveModelObj->name = $getData[0];
                    $unApproveModelObj->email = $getData[1];
                    $unApproveModelObj->status = $getData[2];
                    $unApproveModelObj->resume = $getData[3]??'';
                    $unApproveModelObj->subject = $getData[4]??'';
                    $unApproveModelObj->source_id = $getData[5]??'';
                    $unApproveModelObj->source_info = $getData[6]??'';
                    $unApproveModelObj->written_feedback = $getData[7]??'';
                    $unApproveModelObj->save();
                    $i++;
                }
                $response = ApiConstant:: DATA_ADDED;
                fclose($file);
            } else {
                $error = ApiConstant::DATA_NOT_SAVED;
            }
        } catch
        (\Exception $e) {
            print_r($e->getMessage());
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addSenderDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $Data = array();
        try {
            $data = $request->input();
            if (!empty($data['sender_name'])) {
                $Data['sender_name'] = $data['sender_name'];
            } else {
                $error = ApiConstant::EMPTY_FIRST_NAME;
            }
            if (!empty($data['sender_email'])) {
                $Data['sender_email'] = $data['sender_email'];
            } else {
                $error = ApiConstant::EMPTY_EMAIL;
            }
            $Data['id'] = 1;
            $Data['id_user'] = $authenticatedUser;
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $senderModelObj = new SenderModel();
                $result = $senderModelObj->addSenderDetails($Data);
                if ($result == ApiConstant::UPDATED_SUCCESSFULLY) {
                    $response = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
                } else {
                    $response = array('data' => $result, 'message' => ApiConstant::INVALID_ID);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewSenderDetails()
    {
        $response = null;
        $error = null;
        try {
            $senderModelObj = new SenderModel();
            $response = $senderModelObj->viewSenderDetails();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewInterviewerWork(Request $request)
    {
        $inputData = $request->input();
        $response = null;
        $error = null;
        try {
            $candidateFeedBackModelObj = new CandidateFeedbackModel();
            $response = $candidateFeedBackModelObj->getInterviewerWork($inputData);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewBirthdayList(Request $request)
    {
        $response = null;
        $error = null;
        $inputData = $request->input();
        $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
        $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
        $limit = $data['limit'] ?? '';
        $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
        try {
            $userModelObj = new UserModel();
            $response = $userModelObj->viewBirthdayList($data);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewCandidateByStatus(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = [];
        $result = [];
        $error = null;
        $total = 0;
        try {
            $userModelObj = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['status'] = $inputData['status'];
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? 20;
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                    $candidateModelObj = new CandidateModel();
                    $candidateData = $candidateModelObj->viewCandidateByStatus($data);
                $result['data'] = $candidateData[0];
                $result['count'] = $candidateData[1];
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }catch (\Exception $e) {
            $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($result, $error);
    }

    public function getCandidatesByFilters(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = [];
        $result = [];
        $error = null;
        $total = 0;
        try {
            $userModelObj = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['from_date'] = $inputData['from_date'];
            $data['to_date'] = $inputData['to_date'];
            $data['status'] = $inputData['status'];
            $data['id_tag'] = $inputData['id_tag'];
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $limit = $data['limit'] ?? 20;
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $data['skip'] = isset($data['pageNumber'])?(($data['pageNumber'] - 1) * ($limit)):null;
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $candidateModelObj = new CandidateModel();
                $candidateData = $candidateModelObj->getCandidatesByFilters($data);
                $result['data'] = $candidateData[0];
                $result['count'] = $candidateData[1];
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }catch (\Exception $e) {
            $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($result, $error);
    }


    public function getVendorsByFilters(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = [];
        $result = [];
        $error = null;
        $candidateData = null;
        $total = 0;
        try {
            $userModelObj = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['search_input'] = ($inputData['search_input']) ? $inputData['search_input'] : null;
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $userModelObj = new UserModel();
                $candidateData = $userModelObj->getVendorsByFilters($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }catch (\Exception $e) {
            $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($candidateData, $error);
    }

    public function viewCandidateByDate(Request $request)
    {
        $error = null;
        $response = null;
        $inputData = $request->input();
        $data['from_date'] = $inputData['from_date'];
        $data['to_date'] = $inputData['to_date'];
        $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
        $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
        $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
        $limit = $data['limit'] ?? '';
        $data['skip'] = isset($data['pageNumber'])? ($data['pageNumber'] - 1) * ($limit) :null ;
        try {
            $userModelObj = new UserModel();
            $response = $userModelObj->viewCandidateByDate($data);
        }
        catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getCandidatesByDates(Request $request)
    {
        $inputData = $request->input();
        $result = [];
        $response = [];
        $error = null;
        $data['from_date'] = $inputData['from_date'];
        $data['to_date'] = $inputData['to_date'];
        $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
//        print_r($data['search_input']);die;
        $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
        $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
        $limit = $data['limit'] ?? 20;
        $data['skip'] = isset($data['pageNumber'])? ($data['pageNumber'] - 1) * ($limit) :null ;
        print_r($data['skip']);
        try {
            $userModelObj = new UserModel();
            $candidateData = $userModelObj->getCandidatesByDates($data);
            $result['data'] = $candidateData[0];
            $result['count'] = $candidateData[1];
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($result, $error);
    }

//    public function viewCandidateTags(Request $request)
//    {
//        $inputData = $request->input();
//        $error = null;
//        $candidateTagData = null;
//        try {
//            $candidateTagModelObj = new CandidateTagModel();
//            $candidateTagData = $candidateTagModelObj->viewCandidateTags($inputData);
//        } catch (\Exception $e) {
//            print_r($e->getMessage());
//            $error = ApiConstant::EXCEPTION_OCCURED;
//        }
//        return $this->returnableResponseData($candidateTagData, $error);
//    }
    public function viewVendorTags(Request $request)
    {
        $inputData = $request->input();
        $error = null;
        $candidateTagData = null;
        try {
            $candidateTagModelObj = new VendorTagModel();
            $candidateTagData = $candidateTagModelObj->viewVendorTags($inputData);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($candidateTagData, $error);
    }

    public function viewAllTags(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $tagModelObj = new TagModel();
                $response = $tagModelObj->viewAllTags();
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function viewCandidateByTags(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $result = [];
        $error = null;
        $response = [];
        $total = 0;
        try {
            $userModelObj = new UserModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['pageNumber'] = isset($inputData['pageNumber']) ? (!empty($inputData['pageNumber']) ? $inputData['pageNumber'] : null) : null;
            $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : null;
            $data['id_tag'] = $inputData['id_tag'];
            $data['search_input'] = isset($inputData['search_input']) ? $inputData['search_input'] : null;
            $limit = $data['limit'] ?? '';
            $list = [];
            $data['skip'] = ($data['pageNumber'] - 1) * ($limit);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                    $candidateModelObj = new CandidateModel();
                    $candidateData = $candidateModelObj->viewCandidateBytags($data);
                    $response = array_values(array_map("unserialize", array_unique( array_map("serialize", array_merge($response, $candidateData[0]) ))));
                    $result['data'] = $response;
                    $result['count'] = $candidateData[1];
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($result, $error);
    }

}

