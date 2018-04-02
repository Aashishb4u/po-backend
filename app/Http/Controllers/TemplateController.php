<?php

namespace App\Http\Controllers;

use App\Helpers\ApiConstant;
use App\Models\TemplateModel;
use App\Models\UserRoleModel;
use Illuminate\Http\Request;

class TemplateController extends AppController
{
    public function add(Request $request)
    {
        $error = null;
        $response = null;
        $message = null;
        $templateData = $request->input();
        $template['id'] = $templateData['id'] ?? null;
        if (!empty($templateData['name'])) {
            $template['name'] = $templateData['name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($templateData['id_round'])) {
            $template['id_round'] = $templateData['id_round'];
        } else {
            $error = ApiConstant::ID_NOT_FOUND;
        }
        if (!empty($templateData['subject'])) {
            $template['subject'] = $templateData['subject'];
        } else {
            $error = ApiConstant::EMPTY_SUBJECT;
        }
        if (!empty($templateData['content'])) {
            $template['content'] = $templateData['content'];
        } else {
            $error = ApiConstant::EMPTY_CONTENT;
        }
        if (!empty($templateData['description'])) {
            $template['description'] = $templateData['description'];
        } else {
            $error = ApiConstant::EMPTY_DESCRIPTION;
        }
        if (!empty($template)) {
            try {
                $templateModelObj = new TemplateModel();
                $templateDetails = $templateModelObj->add($template);
                if ($templateDetails == ApiConstant::TEMPLATE_CREATED_SUCCESSFULLY || $templateDetails == ApiConstant::TEMPLATE_UPDATED_SUCCESSFULLY) {
                    $response = array("message" => $templateDetails);
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($response, $error,$message);
    }

    public function view(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $template = new TemplateModel();
                $response = $template->viewTemplate();
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function viewTemplateByRound(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $templateData = $request->input();
        $roundId = $templateData['id_round'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $template = new TemplateModel();
                $response = $template->viewTemplateByRound($roundId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getTemplateById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $templateInput = $request->input();
        $templateId = $templateInput['id'];
       // $templateData['id_round'] = $templateInput['id_round'];

        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 4) {
                $template = new TemplateModel();
                $response = $template->getTemplateById($templateId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteTemplate(Request $request)
    {
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            if ($id != 27 && $id != 28 && $id != 30 && $id != 31)
            {
                $recruiter = new TemplateModel();
                $response = $recruiter->deleteTemplate($id);
                if ($response == ApiConstant::TEMPLATE_DELETED) {
                    $response = array("message" => $response);
                }
            }else{
                $error = ApiConstant::TEMPLATE_NOT_DELETED;
               // $error = array("message" => $response);
            }

        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

}
