<?php

namespace App\Models;

use App\BaseModels\BaseStaticReplacementModel;
use App\Helpers\ApiConstant;

use Illuminate\Database\Eloquent\Model;

class StaticReplacementModel extends BaseStaticReplacementModel
{
    public function addStaticReplacement($data)
    {
        $returnData = null;
        $this->key = $data['key'];
        $this->value = $data['value'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function isDataAlreadyExist($data)
    {
        $id = $data['id'];
        $returnData = null;
        $returnData = $this::where('id',$id )->first();
        if (!empty($isDataAlreadyExist)) {
            $returnData = ApiConstant::DATA_ALREADY_EXIST;
        }
        return $returnData;
    }

    public function isStaticDataAlreadyExist($data)
    {
        $returnData = null;
        $isDataAlreadyExist = $this::where('key', $data)->first();
        return $isDataAlreadyExist;
    }

    public function isAddStaticDataAlreadyExist($data)
    {

        $key = $data['key'];
        $returnData = null;
        $returnData = $this::where('key',$key )->first();
        if (!empty($returnData)) {
            $returnData = ApiConstant::DATA_ALREADY_EXIST;
        }
        return $returnData;
    }

    public function editStaticReplacement($data)
    {
        $returnData = null;
        $key = $data['key'];
        $value = $data['value'];
        $id = $data['id'];

        $update = $this::where('id', $id)->update([
            'key' => $key,
            'value' => $value
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function deleteStaticReplacement($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::DATA_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }
    public function viewStaticReplacement()
    {
        $response = $this::orderBy('setting.key', 'asc')->select('*')->get();
        return $response;
    }

    public function getStaticReplacementById($Id)
    {
        $response = $this::where('id', $Id)->select('*')->first();
        return $response;
    }

}
