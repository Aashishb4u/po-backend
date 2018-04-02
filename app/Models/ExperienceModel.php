<?php

namespace App\Models;

use App\BaseModels\BaseExperienceModel;
use App\Helpers\ApiConstant;

class ExperienceModel extends BaseExperienceModel
{
    public function addJobExperience($data)
    {
        $returnData = null;
        $experience = $data['experience'];
        $result = BaseExperienceModel::insert(['experience' => $experience]);
        if ($result) {
            $returnData = array("message" => ApiConstant::EXPERIENCE_ADDED);
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function editJobExperience($data)
    {
        $returnData = null;
        $experience = $data['experience'];
        $id = $data['id'];
        $update = BaseExperienceModel::where('id', $id)->update(['experience' => $experience]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function viewJobExperience()
    {
        $response = BaseExperienceModel::orderBy('experiences.experience', 'asc')->select('*')->get();
        return $response;
    }

    public function getJobExperienceById($jobId)
    {
        $response = BaseExperienceModel::where('id', $jobId)->select('*')->get();
        return $response;
    }

    public function deleteJobExperience($id)
    {
        $response = BaseExperienceModel::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::JOB_EXPERIENCE_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }
}
