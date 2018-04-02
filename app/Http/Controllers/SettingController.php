<?php

namespace App\Http\Controllers;

use App\BaseModels\BaseBatchModel;
use App\Models\ApplicationStageModel;
use App\Models\BatchModel;
use App\Models\DateTimeModel;
use App\Models\ExperienceModel;
use App\Models\FeedbackMockModel;
use App\Models\MonthDifferenceModel;
use App\Models\QualificationModel;
use App\Models\RejectedDomainModel;
use App\Models\RepresentativeModel;
use App\Models\RoundModel;
use App\Models\SettingModel;
use App\Models\StaticReplacementModel;
use App\Models\UserRoleModel;
use Illuminate\Http\Request;
use App\Helpers\ApiConstant;
use App\Models\OrganisationModel;

class SettingController extends AppController
{
    public function addNoOfMonths(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $Data = array();
        try {
            $data = $request->input();
            $Data['month_difference'] = $data['month_difference'];
            $Data['id'] = 1;
            $Data['id_user'] = $authenticatedUser;
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $updateObj = new MonthDifferenceModel();
                $result = $updateObj->addNoOfMonths($Data);
                if ($result == ApiConstant::UPDATED_SUCCESSFULLY) {
                    $response = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
                } else {
                    $response = array('data' => $result, 'message' => ApiConstant::INVALID_ID);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);

    }

    public function viewNoOfMonths(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $job = new MonthDifferenceModel();
            $response = $job->viewNoOfMonths();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function addJob(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $jobDetails = null;
        $jobData = $request->input();
        if (!empty($jobData['name'])) {
            $job['name'] = $jobData['name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($jobData['job_description'])) {
            $job['job_description'] = $jobData['job_description'];
        } else {
            $error = ApiConstant::EMPTY_DESCRIPTION;
        }
        if (!empty($job)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $job['id_user'] = $authenticatedUser;
                    $jobModelObj = new SettingModel();
                    $jobDetails = $jobModelObj->addJob($job);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($jobDetails, $error);
    }

    public function editJob(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $jobDetails = null;
        $jobData = $request->input();
        if (!empty($jobData['id'])) {
            $job['id'] = $jobData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($jobData['name'])) {
            $job['name'] = $jobData['name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($jobData['job_description'])) {
            $job['job_description'] = $jobData['job_description'];
        } else {
            $error = ApiConstant::EMPTY_DESCRIPTION;
        }
        if (!empty($job)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $job['id_user'] = $authenticatedUser;
                    $jobModelObj = new SettingModel();
                    $jobDetails = $jobModelObj->editJob($job);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($jobDetails, $error);
    }

    public function viewJob(Request $request)
    {
        $response = null;
        $error = null;
        try {
            $job = new SettingModel();
            $response = $job->viewJob();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getJobById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $jobData = $request->input();
        $jobId = $jobData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $job = new SettingModel();
                $response = $job->getJobById($jobId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteJob(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $recruiter = new SettingModel();
                $response = $recruiter->deleteJob($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addJobExperience(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $jobDetails = null;
        $jobData = $request->input();
        if (!empty($jobData['experience'])) {
            $job['experience'] = $jobData['experience'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($job)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $jobModelObj = new ExperienceModel();
                    $jobDetails = $jobModelObj->addJobExperience($job);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }

            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($jobDetails, $error);
    }

    public function editJobExperience(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $jobDetails = null;
        $jobData = $request->input();
        if (!empty($jobData['id'])) {
            $job['id'] = $jobData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($jobData['experience'])) {
            $job['experience'] = $jobData['experience'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($job)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $jobModelObj = new ExperienceModel();
                    $jobDetails = $jobModelObj->editJobExperience($job);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($jobDetails, $error);
    }

    public function viewJobExperience(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $job = new ExperienceModel();
            $response = $job->viewJobExperience();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getJobExperienceById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $jobData = $request->input();
        $jobId = $jobData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $job = new ExperienceModel();
                $response = $job->getJobExperienceById($jobId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteJobExperience(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $recruiter = new ExperienceModel();
                $response = $recruiter->deleteJobExperience($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewJobAndExperience(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $job = new SettingModel();
            $response = $job->viewJobAndExperience();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function addApplicationStage(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $stage = null;
        $stageDetails = null;
        $stageData = $request->input();
        if (!empty($stageData['stage'])) {
            $stage['stage'] = $stageData['stage'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($stage)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $stageModelObj = new ApplicationStageModel();
                    $stageDetails = $stageModelObj->addApplicationStage($stage);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }

            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($stageDetails, $error);
    }

    public function editApplicationStage(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $stageDetails = null;
        $stageData = $request->input();
        if (!empty($stageData['id'])) {
            $stage['id'] = $stageData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($stageData['stage'])) {
            $stage['stage'] = $stageData['stage'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($stage)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $stageModelObj = new ApplicationStageModel();
                    $stageDetails = $stageModelObj->editApplicationStage($stage);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($stageDetails, $error);
    }

    public function viewApplicationStage(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $stageModelObj = new ApplicationStageModel();
            $response = $stageModelObj->viewApplicationStage();
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteApplicationStage(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try{
        $userRoleModelObj = new UserRoleModel();
        $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
        if ($userRole->id_role == 1 || $userRole->id_role == 2) {
            $stageModelObj = new ApplicationStageModel();
            $response = $stageModelObj->deleteApplicationStage($id);
            if ($response == ApiConstant::ID_NOT_FOUND) {
                $error = ApiConstant::ID_NOT_FOUND;
            }
        } else {
            $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
        }
      }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getApplicationStageById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $stageData = $request->input();
        $stageId = $stageData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $stageModelObj = new ApplicationStageModel();
                $response = $stageModelObj->getApplicationStageById($stageId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addRejectedDomain(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $RejectedDomainDetails = null;
        $RejectedDomainData = $request->input();
        if (!empty($RejectedDomainData['name'])) {
            $RejectedDomain['name'] = $RejectedDomainData['name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($RejectedDomain)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $rejectedDomainObj = new RejectedDomainModel();
                    $RejectedDomainDetails = $rejectedDomainObj->addRejectedDomain($RejectedDomain);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($RejectedDomainDetails, $error);
    }

    public function editRejectedDomain(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $RejectedDomainDetails = null;
        $RejectedDomainData = $request->input();
        if (!empty($RejectedDomainData['id'])) {
            $RejectedDomain['id'] = $RejectedDomainData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($RejectedDomainData['name'])) {
            $RejectedDomain['name'] = $RejectedDomainData['name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($RejectedDomain)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $RejectedDomain['id_user'] = $authenticatedUser;
                    $RejectedDomainModelObj = new RejectedDomainModel();
                    $RejectedDomainDetails = $RejectedDomainModelObj->editRejectedDomain($RejectedDomain);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($RejectedDomainDetails, $error);
    }

    public function viewRejectedDomain(Request $request)
    {
        $response = null;
        $error = null;
        try {
            $job = new RejectedDomainModel();
            $response = $job->viewRejectedDomain();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getRejectedDomainById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $RejectedDomainData = $request->input();
        $jobId = $RejectedDomainData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $job = new RejectedDomainModel();
                $response = $job->getRejectedDomainById($jobId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteRejectedDomain(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
       try {
           $userRoleModelObj = new UserRoleModel();
           $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
           if ($userRole->id_role == 1 || $userRole->id_role == 2) {
               $domain = new RejectedDomainModel();
               $response = $domain->deleteRejectedDomain($id);
               if ($response == ApiConstant::ID_NOT_FOUND) {
                   $error = ApiConstant::ID_NOT_FOUND;
               }
           } else {
               $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
           }
       }
       catch (\Exception $e) {
           $error = ApiConstant::EXCEPTION_OCCURED;
       }
        return $this->returnableResponseData($response, $error);
    }

    public function addRound(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $round = null;
        $message = null;
        $roundDetails = null;
        $roundData = $request->input();
        if (!empty($roundData['round_name'])) {
            $round['round_name'] = $roundData['round_name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($roundData['description'])) {

            $round['description'] = $roundData['description'];
        } else {
            $error = ApiConstant::EMPTY_DESCRIPTION;
        }
        if (!empty($round)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $roundModelObj = new RoundModel();
                    $roundDetails = $roundModelObj->addRound($round);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($roundDetails, $error,$message);
    }

    public function editRound(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $roundDetails = null;
        $roundData = $request->input();
        if (!empty($roundData['id'])) {
            $round['id'] = $roundData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($roundData['round_name'])) {
            $round['round_name'] = $roundData['round_name'];
        } else {
            $error = ApiConstant::EMPTY_FIRST_NAME;
        }
        if (!empty($roundData['description'])) {
            $round['description'] = $roundData['description'];
        } else {
            $error = ApiConstant::EMPTY_DESCRIPTION;
        }
        if (!empty($round)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $round['id_user'] = $authenticatedUser;
                    $roundModelObj = new RoundModel();
                    $roundDetails = $roundModelObj->editRound($round);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($roundDetails, $error);
    }

    public function viewRound()
    {
        $response = null;
        $error = null;
        try {
            $roundModelObj = new RoundModel();
            $response = $roundModelObj->viewRound();
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteRound(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $roundModelObj = new RoundModel();
                $response = $roundModelObj->deleteRound($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getRoundById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $roundData = $request->input();
        $roundId = $roundData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $roundModelObj = new RoundModel();
                $response = $roundModelObj->getRoundById($roundId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }


    public function addQualification(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $qualification = null;
        $qualificationDetails = null;
        $qualificationData = $request->input();
        if (!empty($qualificationData['qualification'])) {
            $qualification['qualification'] = $qualificationData['qualification'];
        } else {
            $error = ApiConstant::EMPTY_QUALIFICATION;
        }
        if (!empty($qualification)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $qualificationModelObj = new QualificationModel();
                    $qualificationDetails = $qualificationModelObj->addQualification($qualification);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }

            } catch (\Exception $e) {
                print_r('catch');
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($qualificationDetails, $error);
    }

    public function editQualification(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $qualificationDetails = null;
        $qualificationData = $request->input();
        if (!empty($qualificationData['id'])) {
            $qualification['id'] = $qualificationData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($qualificationData['qualification'])) {
            $qualification['qualification'] = $qualificationData['qualification'];
        } else {
            $error = ApiConstant::EMPTY_QUALIFICATION;
        }
        if (!empty($qualification)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $qualificationModelObj = new QualificationModel();
                    $qualificationDetails = $qualificationModelObj->editQualification($qualification);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($qualificationDetails, $error);
    }

    public function deleteQualification(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $qualificationModelObj = new QualificationModel();
                $response = $qualificationModelObj->deleteQualification($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewQualification()
    {    $response = null;
        $error = null;
        try {
            $qualificationModelObj = new QualificationModel();
            $response = $qualificationModelObj->viewQualification();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getQualificationById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $qualificationData = $request->input();
        $qualificationId = $qualificationData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $qualificationModelObj = new RoundModel();
                $response = $qualificationModelObj->getQualificationById($qualificationId);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function addStaticReplacement(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $message = null;
         $Data = null;
        $data = $request->input();
        if (!empty($data['key'])) {
            $Data['key'] = $data['key'];
        } else {
            $error = ApiConstant::EMPTY_KEY_NAME;
        }
        if (!empty($data['value'])) {
            $Data['value'] = $data['value'];
        } else {
            $error = ApiConstant::EMPTY_VALUE;
        }
        if (!empty($Data)) {
            try {
                $settingModelObj = new StaticReplacementModel();
                $isDataAlreadyExist = $settingModelObj->isAddStaticDataAlreadyExist($Data);
                if ($isDataAlreadyExist == ApiConstant::DATA_ALREADY_EXIST) {
                    $error = $isDataAlreadyExist;
                }
                else{
                    $userRoleModelObj = new UserRoleModel();
                    $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                    if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                        $response = $settingModelObj->addStaticReplacement($Data);
                    } else {
                        $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                    }
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($response, $error,$message);
    }

    public function editStaticReplacement(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $Data = null;
        $message =
        $replacementDetails = null;
        $roundDetails = null;
        $data = $request->input();
        $settingModelObj = new StaticReplacementModel();
        $staticData = $settingModelObj->isDataAlreadyExist($data);
        $data['key'] = $staticData['key'];
        if (!empty($data['id'])) {
            $Data['id'] = $data['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($data['key'])) {
            $Data['key'] = $data['key'];
        } else {
            $error = ApiConstant::EMPTY_KEY_NAME;
        }
        if (!empty($data['value'])) {
            $Data['value'] = $data['value'];
        } else {
            $error = ApiConstant::EMPTY_VALUE;
        }
        if (!empty($Data)) {
            try {
                    $userRoleModelObj = new UserRoleModel();
                    $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                    if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                        $Data['id_user'] = $authenticatedUser;
                        $dataModelObj = new StaticReplacementModel();
                        $replacementDetails = $dataModelObj->editStaticReplacement($Data);
                    } else {
                        $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                    }

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($replacementDetails, $error,$message);
    }

    public function deleteStaticReplacement(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $dataModelObj = new StaticReplacementModel();
                $response = $dataModelObj->deleteStaticReplacement($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewStaticReplacement()
    {    $response = null;
        $error = null;
        $message = null;
        try {
            $dataModelObj = new StaticReplacementModel();
            $response = $dataModelObj->viewStaticReplacement();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getStaticReplacementById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $data = $request->input();
        $Id = $data['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $dataModelObj = new StaticReplacementModel();
                $response = $dataModelObj->getStaticReplacementById($Id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addDateTime(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $dateTimeDetails = null;
        $inptData = $request->input();
        if (!empty($inptData['dateTime'])) {
            $dateTime['dateTime'] = $inptData['dateTime'];
        } else {
            $error = ApiConstant::EMPTY_DATE_TIME;
        }
        if (!empty($inptData['id_round'])) {
            $dateTime['id_round'] = $inptData['id_round'];
        } else {
            $error = ApiConstant::EMPTY_ROUND_ID;
        }
        if (!empty($dateTime)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $dateTime['id_user'] = $authenticatedUser;
                    $dateTimeModelObj = new DateTimeModel();
                    $dateTimeDetails = $dateTimeModelObj->addDateTime($dateTime);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {

                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($dateTimeDetails, $error);
    }

    public function editDateTime(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $dateTimeDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['id'])) {
            $dateTime['id'] = $inputData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($inputData['dateTime'])) {
            $dateTime['dateTime'] = $inputData['dateTime'];
        } else {
            $error = ApiConstant::EMPTY_DATE_TIME;
        }
        if (!empty($inputData['id_round'])) {
            $dateTime['id_round'] = $inputData['id_round'];
        } else {
            $error = ApiConstant::EMPTY_ROUND_ID;
        }
        if (!empty($dateTime)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $dateTime['id_user'] = $authenticatedUser;
                    $dateTimeModelObj = new DateTimeModel();
                    $dateTimeDetails = $dateTimeModelObj->editDateTime($dateTime);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }else{
           $error = ApiConstant::DATA_NOT_FOUND;
        }
        return $this->returnableResponseData($dateTimeDetails, $error);
    }

    public function viewDateTime()
    {
        $response = null;
        $error = null;
        try {
            $dateTimeModelObj = new DateTimeModel();
            $response = $dateTimeModelObj->viewDateTime();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getDateTimeById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $message = null;
        $error = null;
        $inputData = $request->input();
        $Id = $inputData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $dateTimeModelObj = new DateTimeModel();
                $response = $dateTimeModelObj->getDateTimeById($Id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error, $message);
    }

    public function deleteDateTime(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $dateTimeModelObj = new DateTimeModel();
                $response = $dateTimeModelObj->deleteDateTime($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewDateTimeByRound(Request $request)
    {
        $response = null;
        $error = null;
        $roundId = $request->id_round;
        if(!empty($roundId))
        {
            try {
                $dateTimeModelObj = new DateTimeModel();
                $response = $dateTimeModelObj->viewDateTimeByRound($roundId);
            } catch (\Exception $e) {print_r($e->getMessage());
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        else{
            $error = ApiConstant::ID_NOT_FOUND;
        }

        return $this->returnableResponseData($response, $error);
    }


    public function addOrganisationDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $collegeDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['organisation'])) {
            $collegeData['organisation'] = $inputData['organisation'];
        } else {
            $error = ApiConstant::EMPTY_ORGANISATION_NAME;
        }
        if (!empty($inputData['city'])) {
            $collegeData['city'] = $inputData['city'];
        } else {
            $error = ApiConstant::EMPTY_CITY_NAME;
        }
        if (!empty($inputData['last_invited'])) {
            $collegeData['last_invited'] = $inputData['last_invited'];
        } else {
            $error = ApiConstant::EMPTY_LAST_INVITED_DATE;
        }
        if (!empty($inputData['representative_type'])) {
            $collegeData['representative_type'] = $inputData['representative_type'];
        } else {
            $error = ApiConstant::EMPTY_REPRESENTATIVE_TYPE;
        }
        if (!empty($collegeData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $collegeDataModelObj = new OrganisationModel();
                    $collegeDetails = $collegeDataModelObj->addOrganisationDetails($collegeData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {

                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($collegeDetails, $error);
    }

    public function editOrganisationDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $collegeDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['id'])) {
            $collegeData['id'] = $inputData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($inputData['organisation'])) {
            $collegeData['organisation'] = $inputData['organisation'];
        } else {
            $error = ApiConstant::EMPTY_ORGANISATION_NAME;
        }
        if (!empty($inputData['city'])) {
            $collegeData['city'] = $inputData['city'];
        } else {
            $error = ApiConstant::EMPTY_CITY_NAME;
        }
        if (!empty($inputData['last_invited'])) {
            $collegeData['last_invited'] = $inputData['last_invited'];
        } else {
            $error = ApiConstant::EMPTY_LAST_INVITED_DATE;
        }
        if (!empty($inputData['representative_type'])) {
            $collegeData['representative_type'] = $inputData['representative_type'];
        } else {
            $error = ApiConstant::EMPTY_REPRESENTATIVE_TYPE;
        }
        if (!empty($collegeData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $collegeDataModelObj = new OrganisationModel();
                    $collegeDetails = $collegeDataModelObj->editOrganisationDetails($collegeData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                print_r($e->getMessage());
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }else{
            $error = ApiConstant::DATA_NOT_FOUND;
        }
        return $this->returnableResponseData($collegeDetails, $error);
    }

    public function viewOrganisationDetails()
    {
        $response = null;
        $error = null;
        try {
            $collegeDataModelObj = new OrganisationModel();
            $response = $collegeDataModelObj->viewOrganisationDetails();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function viewOrganisationDetailsByType(Request $request)
    {
        $response = null;
        $error = null;
        $inputData = $request->input();
        try {
            $collegeDataModelObj = new OrganisationModel();
            $response = $collegeDataModelObj->viewOrganisationDetailsByType($inputData);
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getOrganisationDetailsById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $message = null;
        $error = null;
        $inputData = $request->input();
        $Id = $inputData['id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $collegeDataModelObj = new OrganisationModel();
                $response = $collegeDataModelObj->getOrganisationDetailsById($Id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error, $message);
    }

    public function deleteOrganisationDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $collegeDataModelObj = new OrganisationModel();
                $response = $collegeDataModelObj->deleteOrganisationDetails($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

     // TPO-Representative type
    public function addRepresentativeType(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $typeDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['representative_type'])) {
            $typeData['representative_type'] = $inputData['representative_type'];
        } else {
            $error = ApiConstant::EMPTY_REPRESENTATIVE_TYPE;
        }

        if (!empty($typeData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $typeDataModelObj = new RepresentativeModel();
                    $typeDetails = $typeDataModelObj->addRepresentativeType($typeData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {

                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($typeDetails, $error);
    }

    public function editRepresentativeType(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $typeDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['id'])) {
            $typeData['id'] = $inputData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($inputData['representative_type'])) {
            $typeData['representative_type'] = $inputData['representative_type'];
        } else {
            $error = ApiConstant::EMPTY_REPRESENTATIVE_TYPE;
        }
        if (!empty($typeData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $dateTime['id_user'] = $authenticatedUser;
                    $typeDataModelObj = new RepresentativeModel();
                    $typeDetails = $typeDataModelObj->editRepresentativeType($typeData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                print_r($e->getMessage());
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }else{
            $error = ApiConstant::DATA_NOT_FOUND;
        }
        return $this->returnableResponseData($typeDetails, $error);
    }

    public function viewRepresentativeType()
    {
        $response = null;
        $error = null;
        try {
            $typeDataModelObj = new RepresentativeModel();
            $response = $typeDataModelObj->viewRepresentativeType();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteRepresentativeType(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $typeDataModelObj = new RepresentativeModel();
                $response = $typeDataModelObj->deleteRepresentativeType($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getRepresentativeTypeById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $typeDataModelObj = new RepresentativeModel();
                $response = $typeDataModelObj->getRepresentativeTypeById($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    // Expected Joiners Batch (Ex.2016-17)

    public function addBatch(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $batchDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['batch'])) {
            $batchData['batch'] = $inputData['batch'];
        } else {
            $error = ApiConstant::EMPTY_BATCH;
        }

        if (!empty($batchData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $batchModelObj = new BatchModel();
                    $batchDetails = $batchModelObj->addBatch($batchData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {

                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($batchDetails, $error);
    }

    public function editBatch(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $batchDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['id'])) {
            $batchData['id'] = $inputData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($inputData['batch'])) {
            $batchData['batch'] = $inputData['batch'];
        } else {
            $error = ApiConstant::EMPTY_BATCH;
        }
        if (!empty($batchData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $batchModelObj = new BatchModel();
                    $batchDetails = $batchModelObj->editBatch($batchData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                print_r($e->getMessage());
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }else{
            $error = ApiConstant::DATA_NOT_FOUND;
        }
        return $this->returnableResponseData($batchDetails, $error);
    }

    public function viewBatch()
    {
        $response = null;
        $error = null;
        try {
            $batchModelObj = new BatchModel();
            $response = $batchModelObj->viewBatch();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function deleteBatch(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $batchModelObj = new BatchModel();
                $response = $batchModelObj->deleteBatch($id);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        }
        catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function getBatchById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $batchModelObj = new BatchModel();
                $response = $batchModelObj->getBatchById($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function addFeedbackMock(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $mockDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['feedback_data'])) {
            $mockDetails['feedback_data'] = $inputData['feedback_data'];
        } else {
            $error = ApiConstant::DATA_NOT_FOUND;
        }

        if (!empty($mockDetails)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $mockModelObj = new FeedbackMockModel();
                    $mockDetails = $mockModelObj->addFeedbackMock($mockDetails);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            }

            catch (\Exception $e) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $this->returnableResponseData($mockDetails, $error);
    }

    public function editFeedbackMock(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $error = null;
        $feedbackDetails = null;
        $inputData = $request->input();
        if (!empty($inputData['id'])) {
            $feedbackData['id'] = $inputData['id'];
        } else {
            $error = ApiConstant::INVALID_ID;
        }
        if (!empty($inputData['feedback_data'])) {
            $feedbackData['feedback_data'] = $inputData['feedback_data'];
        } else {
            $error = ApiConstant::EMPTY_BATCH;
        }
        if (!empty($feedbackData)) {
            try {
                $userRoleModelObj = new UserRoleModel();
                $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
                if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                    $feedbackModelObj = new FeedbackMockModel();
                    $feedbackDetails = $feedbackModelObj->editFeedbackMock($feedbackData);
                } else {
                    $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
                }
            } catch (\Exception $e) {
                print_r($e->getMessage());
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }else{
            $error = ApiConstant::DATA_NOT_FOUND;
        }
        return $this->returnableResponseData($feedbackDetails, $error);
    }

    public function viewFeedbackMock()
    {
        $response = null;
        $error = null;
        try {
            $feedbackModelObj = new FeedbackMockModel();
            $response = $feedbackModelObj->viewFeedbackMock();
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getFeedbackMockById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $feedbackModelObj = new FeedbackMockModel();
                $response = $feedbackModelObj->getFeedbackMockById($id);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }


}
