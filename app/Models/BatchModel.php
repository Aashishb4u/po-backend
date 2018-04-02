<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 27/6/17
 * Time: 1:52 PM
 */

namespace App\Models;


use App\BaseModels\BaseBatchModel;
use App\Helpers\ApiConstant;

class BatchModel extends BaseBatchModel
{

    public function addBatch($data)
    {
        $returnData = null;
        $this->batch = $data['batch'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editBatch($data)
    {
        $returnData = null;
        $update = $this::where('id', $data['id'])->update([
            'batch' => $data['batch']
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewBatch()
    {
        $response = $this::select('*')->
        orderBy('id', 'desc')
            -> get();
        return $response;
    }

    public function deleteBatch($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::DATA_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function getBatchById($id)
    {
        $response = $this:: where('id', $id)->first();
        return $response;
    }
}