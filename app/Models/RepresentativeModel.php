<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 22/6/17
 * Time: 1:58 PM
 */

namespace App\Models;
use App\BaseModels\BaseRepresentativeModel;
use App\Helpers\ApiConstant;
use File;
use Illuminate\Support\Facades\DB;

class RepresentativeModel extends BaseRepresentativeModel
{
    public function addRepresentativeType($data)
    {
        $returnData = null;
        $this->representative_type = $data['representative_type'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editRepresentativeType($data)
    {
        $returnData = null;
        $update = $this::where('id', $data['id'])->update([
            'representative_type' => $data['representative_type']
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewRepresentativeType()
    {
        $response = $this::select('*')->
        orderBy('id', 'desc')
            -> get();
        return $response;
    }

    public function deleteRepresentativeType($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::DATA_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

    public function getRepresentativeTypeById($id)
    {
        $response = $this:: where('id', $id)->first();
        return $response;
    }
}