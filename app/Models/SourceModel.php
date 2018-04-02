<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 8/8/17
 * Time: 9:18 PM
 */

namespace App\Models;


use App\BaseModels\BaseSourceModel;
use Illuminate\Support\Facades\DB;

class SourceModel extends BaseSourceModel
{
    public function addSource($data)
    {
        $returnData = null;
        $this->batch = $data['batch'];
        $this->batch = $data['batch'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editSource($data)
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

    public function viewSource()
    {
        $response = $this::select('*')->
        orderBy('id', 'desc')
            -> get();
        return $response;
    }

    public function deleteSource($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::DATA_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function getSourceById($id)
    {
        $response = $this:: where('id', $id)->first();
        return $response;
    }

    public function getSourceIdByMatchingKey($key)
    {
        $response = $this:: where('matching_key','LIKE' ,$key)->pluck('id')->first();
        return $response;
    }

    public function getSourceIdDefaultTrue()
    {
        return $this:: where('is_default',1)->value('id');
    }

    public function getSources(){
        return $this::where('automatic_detection', 1)
        ->get();
    }

}