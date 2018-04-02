<?php

namespace App\Http\Controllers;

use App\BaseModels\BaseEmailModel;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use App\Models\AttachmentModel;
use App\Models\CandidateModel;
use App\Models\CandidateTemplateSend;
use App\Models\Emails;
use App\Models\ExpectedJoinersModel;
use App\Models\ExpectedJoinersTemplateSendModel;
use App\Models\ForbiddenDomainModel;
use App\Models\RejectedDomainModel;
use App\Models\SourceModel;
use App\Models\TpoConsultancyInstituteTemplateModel;
use App\Models\UnApprovedCandidates;
use App\Models\UserModel;
use App\Models\TpoConsultancyInstituteModel;
use Postmark\Inbound;
use DateTime;


class WebHookController extends AppController
{
    public function ironMqReceiver()
    {
        $message = @file_get_contents('php://input');
        if (isset($message)) {
            $body = json_decode($message);
            $emails_id = isset($body->email_id) ? $body->email_id : null;

            if ($emails_id && $emails_id > 0) {
                $this->sendEmail($emails_id);
            }
        }
    }

    public function sendEmail($emails_id)
    {
        $isSent = 0;
        $message = null;
        $response = BaseEmailModel::where('is_sent', $isSent)->where('id', $emails_id)->get();
        foreach ($response as $list) {
            $id = $list->id;
            $hash = md5($id);
            $template_id = $list->template_id;
            $recruiter_id = $list->id_recruiter;
            $emails = new Emails();
            $subject = $list->subject;
            $body = $list->content;
            if ($list->user_type == ApiConstant::CANDIDATE_TYPE) {
                $userModelObj = new UserModel();
                $userData = $userModelObj->getUserDetailsByEmail($list->to_email);
                if($list->status == 37)
                {
                    $response = AppUtility::sendEmail($subject, $body, $list->to_email, $hash);
                }
                else{
                    $response = AppUtility::sendEmail($subject, $body, $list->to_email, $hash);
                    if ($response == 1) {
                        $result = $emails->insertIsSent($id, $hash);
                        if ($result) {
                            $isSent = 1;
                            $template = new CandidateTemplateSend();
                            $template->updateCandidateIsSent($template_id, $userData['id'], $isSent);
                            $candidateModelObj = new CandidateModel();
                            $candidateModelObj->updateCandidateStage($userData['id'], $list->status, $list->date_time);
                        }
                    }
                }
            } else {
                if ($list->user_type == ApiConstant::TPO_TYPE) {
                    $tpoConsultancyInstituteModelObj = new TpoConsultancyInstituteModel();
                    $tpoConsultancyInstituteTemplateModelObj = new TpoConsultancyInstituteTemplateModel();
                    $tpoDetails = $tpoConsultancyInstituteModelObj->getTpoDetailsByEmail($list->to_email);
                    if($list->status == 37)
                    {
                        $response = AppUtility::sendEmail($subject, $body, $list->to_email, $hash);
                    }
                    else{
                        $response = AppUtility::sendEmail($subject, $body, $list->to_email, $hash);
                        if ($response == 1) {
                            $result = $emails->insertIsSent($id, $hash);
                            if ($result) {
                                $tpoConsultancyInstituteTemplateModelObj->insertTemplateId($template_id, $tpoDetails['id'], $recruiter_id);
                                $tpoConsultancyInstituteModelObj->updateTpoStatus($tpoDetails['id'], $list->status);
                            }
                        }
                    }
                } else {
                    $expectedJoinersModel = new ExpectedJoinersModel();
                    $expectedJoinersTemplateModel = new ExpectedJoinersTemplateSendModel();
                    $expectedJoinersDetails = $expectedJoinersModel->getExpectedJoinerDetailsByEmail($list->to_email);
                    if($list->status == 37)
                    {
                        $response = AppUtility::sendEmail($subject, $body, $list->to_email, $hash);
                    }
                    else {
                        $response = AppUtility::sendEmail($subject, $body, $list->to_email, $hash);
                        if ($response == 1) {
                            $result = $emails->insertIsSent($id, $hash);
                            if ($result) {
                                $isSent = 1;
                                $expectedJoinersTemplateModel->updateCandidateIsSent($template_id, $expectedJoinersDetails['id'], $isSent);
                                $expectedJoinersModel->updateExpectedJoinerStatus($expectedJoinersDetails['id'], $list->status);
                            }
                        }
                    }
                }
            }
        }
    }

    public function WebhookMailTest()
    {
        $user = array();
        $sourceId = null;
        $inbound = new Inbound(file_get_contents('php://input'));
        $userEmail = $inbound->ReplyTo();
        if (!isset($userEmail) || $userEmail == "") {
            $userEmail = $inbound->FromEmail();
        } else {
            $userEmailData = AppUtility::extract_emails_from($userEmail);
            $userEmail = $userEmailData[0];
        }
//        $user['source_id'] = 2;
        $user['email'] = $userEmail;
        $domainObj = new RejectedDomainModel();
        $needle = $domainObj->domainList();
        $result = $this->strpos_arr($user['email'], $needle);
        $user['name'] = $inbound->FromName();
        $user['resume'] = '';
        $user['status'] = ApiConstant::UNAPPROVED_STATUS;
        $id = $inbound->MessageID();
        $subject = $inbound->Subject();
        $toEmail = $inbound->To();
        $sourceModel = new SourceModel();
        $sources = $sourceModel->getSources();

        if (count($sources)) {
            foreach ($sources as $source) {
                if ($source->matching_source == 'sender') {
                    $matchingParts = explode('@', $userEmail);
                    $key = '%' . $matchingParts[1];
                    $sourceId = $sourceModel->getSourceIdByMatchingKey($key);
                }elseif ($source->matching_source == 'subject') {
                    if (strpos($subject, 'CreCa') !== false) {
                        $retArray = explode("|", $subject);
                        $key = $retArray[0] . '%';
                        $sourceId = $sourceModel->getSourceIdByMatchingKey($key);
                    }
                } elseif ($source->matching_source == 'receiver') {
                    $sourceId = $sourceModel->getSourceIdByMatchingKey($toEmail);
                }

                if ($sourceId != null) {
                    break;
                }
            }
        }

        if (empty($sourceId)) {
            $defaultSourceId = $sourceModel->getSourceIdDefaultTrue();
            if ($defaultSourceId) {
                $user['source_id'] = $defaultSourceId;
            }
        } else {
            $user['source_id'] = $sourceId;
        }
        if (empty($subject)) {
            $user['subject'] = null;
        } else {
            $user['subject'] = $subject;
        }
        $fwdMessage = false;
        if (strpos($subject, 'CreCa') !== false) {
            $retArray = explode("|", $subject);
            $user['name'] = trim($retArray[1]);
            $user['email'] = trim($retArray[2]);
            $fwdMessage = true;
        }
        if ($result != 1 || $fwdMessage) {
            if (sizeof($inbound->Attachments()->attachments) > 0) {
                $userModelObj = new UnApprovedCandidates();
                $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($user['email']);
                if ($isUserAlreadyExist != ApiConstant::EMAIL_ALREADY_EXIST) {
                    $resume = array();
                    $i = 0;
                    foreach ($inbound->Attachments() as $attachment) {
                        $date = new DateTime();
                        $time = $date->getTimestamp();
                        $fileExtention = pathinfo($attachment->Name)['extension'];
                        if (empty($fileExtention)) {
                            $fileExtention = 'docx';
                        }
                        $fileName = $id . "_" . $time . substr(str_shuffle("abeghijklmnopqrstuvwxyzABEGHIJKLMNOPQRSTUVWXYZ"), 0, rand(5, 9));
                        $attachment->Name = $fileName . "." . $fileExtention;
                        $attachment->ContentType;
                        $attachment->Content;
                        $attachment->ContentLength;
                        $resume[$i] = $attachment->Name;
                        $attachment->Download(public_path() . '/resume/');//takes directory as first argument
                        //$command = '/usr/bin/libreoffice --headless --invisible --convert-to pdf --outdir /var/www/tudip-recruitment-prod/public/resume /var/www/tudip-recruitment-prod/public/resume/'.$attachment->Name;
                        //$success = shell_exec($command);
                        $i++;
                    }
                    $candidateObj = new UnApprovedCandidates();
                    $saveData = $candidateObj->saveUnApprovedCandidateDetails($user);
                    if ($saveData) {
                        foreach ($resume as $key => $list) {
                            $data['email'] = $user['email'];
                            $data['resume'] = $list;
                            $saveAttachmentObj = new AttachmentModel();
                            $saveAttachmentObj->saveResume($data);
                        }
                    }
                } else {
                    $data = array();
                    $data['email_data'] = file_get_contents('php://input');
                    $data['reason'] = ApiConstant::REJECTED_DUE_TO_ALREADY_EMAIL;
                    $emailDataObj = new ForbiddenDomainModel();
                    $emailDataObj->saveEmailData($data);
                }
            } else {
                $data = array();
                $data['email_data'] = file_get_contents('php://input');
                $data['reason'] = ApiConstant::REJECTED_DUE_TO_NO_ATTACHMENT;
                $emailDataObj = new ForbiddenDomainModel();
                $emailDataObj->saveEmailData($data);
            }
        }
    }

    public function strpos_arr($haystack, $needle)
    {
        for ($i = 0; $i < sizeOf($needle); $i++) {
            if (strpos($haystack, $needle[$i]->name) != false) {
                return true;
            }
        }

    }

}
