<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 18/7/17
 * Time: 4:13 PM
 */

namespace App\Models;


use App\BaseModels\BaseTpoTouchLogs;

class TpoTouchLogsModel extends BaseTpoTouchLogs
{

    public function saveTouchLogs($data)
    {
        $returnData = null;
        $this->representative_id = $data['id'];
        $this->last_touch = $data['last_touch'];
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }


    public function getTouchLogs($id)
    {
        $response = null;
        $response = $this::where('representative_id', $id)->last();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

    public function viewTpoTouchLogs($id)
    {
        $response = null;
        $response = $this::where('representative_id', $id)
        ->orderBy('id','desc')
        ->get();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

}