<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 9/6/17
 * Time: 3:25 PM
 */

namespace App\Models;
use App\BaseModels\BaseDateTimeModel;
use App\Helpers\ApiConstant;
use File;



class DateTimeModel extends BaseDateTimeModel
{
    public function addDateTime($data)
    {
        $returnData = null;
        $this->date_time = $data['dateTime'];
        $this->id_round = $data['id_round'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editDateTime($data)
    {
        $returnData = null;
        $dateTime = $data['dateTime'];
        $idRound = $data['id_round'];
        $id = $data['id'];
        $update = $this::where('id', $id)->update([
            'date_time' => $dateTime,
            'id_round' => $idRound
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewDateTime()
    {
        $response = $this::join('rounds', 'date_time.id_round', '=', 'rounds.id')
            ->select('date_time.*', 'rounds.round_name')
            ->get();
        return $response;
    }

    public function getDateTimeById($Id)
    {
        $response = $this::where('date_time.id', $Id)
            ->join('rounds', 'date_time.id_round', '=', 'rounds.id')
            ->select('date_time.*', 'rounds.round_name')
            ->get();
        return $response;

    }

    public function deleteDateTime($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::DATA_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function viewDateTimeByRound($roundId)
    {
        $response = $this::orderBy('date_time.id', 'desc')->where('id_round', $roundId)->select('date_time')->get();
        return $response;
    }

}