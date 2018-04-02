<?php

namespace App\Models;

use App\BaseModels\BaseExperienceModel;
use App\BaseModels\BasePositionModel;
use App\Helpers\ApiConstant;

class SettingModel extends BasePositionModel
{
    public function addJob($data)
    {
        $returnData = null;
        $this->name = $data['name'];
        $this->job_description = $data['job_description'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::JOB_PROFILE_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editJob($data)
    {
        $returnData = null;
        $name = $data['name'];
        $job_description = $data['job_description'];
        $id = $data['id'];
        $update = BasePositionModel::where('id', $id)->update([
            'name' => $name,
            'job_description' => $job_description
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewJob()
    {
        $response = BasePositionModel::orderBy('positions.name', 'asc')->select('*')->get();
        return $response;
    }

    public function getJobById($jobId)
    {
        $response = BasePositionModel::where('id', $jobId)->select('*')->get();
        return $response;
    }

    public function deleteJob($id)
    {
        $response = BasePositionModel::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::JOB_PROFILE_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function viewJobAndExperience()
    {
        $job = BaseExperienceModel::orderBy('experiences.experience', 'asc')->select('*')->get();
        $experience = BasePositionModel::orderBy('positions.name', 'asc')->select('*')->get();
        $response = array($job, $experience);
        return $response;
    }


}
