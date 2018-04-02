<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\BaseModels\BaseApplicationStageModel;
use App\Helpers\ApiConstant;

class ApplicationStageModel extends BaseApplicationStageModel
{
    public function addApplicationStage($data)
    {
        $returnData = null;
        $stage = $data['stage'];
        $result = $this::insert(['stage' => $stage]);
        if ($result) {
            $returnData = array("message" => ApiConstant::APPLICATION_STAGE_ADDED);
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }


    public function editApplicationStage($data)
    {
        $returnData = null;
        $stage = $data['stage'];
        $id = $data['id'];
        $update = $this::where('id', $id)->update(['stage' => $stage]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function viewApplicationStage()
    {
        $response = $this::orderBy('stages.id', 'desc')->select('*')->get();
        return $response;
    }

    public function getApplicationStageById($stageId)
    {
        $response = $this::where('id', $stageId)->select('*')->get();
        return $response;
    }

    public function deleteApplicationStage($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::APPLICATION_STAGE_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }
}
