<?php

namespace App\Models;

use App\BaseModels\BaseQualificationModel;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ApiConstant;


class QualificationModel extends BaseQualificationModel
{
    public function addQualification($data)
    {
        $returnData = null;
        $this->qualification = $data['qualification'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::QUALIFICATION_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editQualification($data)
    {
        $returnData = null;
        $id = $data['id'];
        $qualification = $data['qualification'];
        $update = $this::where('id', $id)->update([
            'qualification' => $qualification
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function deleteQualification($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::QUALIFICATION_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function viewQualification()
    {
        $response = $this::orderBy('qualifications.qualification', 'asc')->select('*')->get();
        return $response;
    }

    public function getQualificationById($id)
    {
        $response = $this::where('id', $id)->select('*')->get();
        return $response;
    }
}
