<?php

namespace App\Models;

use App\BaseModels\BaseRoundModel;
use App\Helpers\ApiConstant;
class RoundModel extends BaseRoundModel
{
    public function addRound($data)
    {
        $returnData = null;
        $this->round_name = $data['round_name'];
        $this->description = $data['description'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::ROUND_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editRound($data)
    {
        $returnData = null;
        $name = $data['round_name'];
        $description = $data['description'];
        $id = $data['id'];
        $update = $this::where('id', $id)->update([
            'round_name' => $name,
            'description' => $description
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewRound()
    {
        $response = $this::where('visible_to_admin','=','1')
        ->orderBy('rounds.round_name', 'asc')
        ->select('*')
        ->get();
        return $response;
    }

    public function deleteRound($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::ROUND_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function getRoundById($roundId)
    {
        $response = $this::where('id', $roundId)->select('*')->first();
        return $response;
    }
}
