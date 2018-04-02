<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 13/6/17
 * Time: 7:34 PM
 */

namespace App\Models;
use App\BaseModels\BaseOrganisationDetailsModel;
use App\Helpers\ApiConstant;
use File;
use Illuminate\Support\Facades\DB;


class OrganisationModel extends BaseOrganisationDetailsModel
{
    public function addOrganisationDetails($data)
    {
        $returnData = null;
        $this->organisation = $data['organisation'];
        $this->city = $data['city'];
        $this->last_invited = $data['last_invited'];
        $this->representative_type = $data['representative_type'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editOrganisationDetails($data)
    {
        $returnData = null;
       $update = $this::where('id', $data['id'])->update([
            'organisation' => $data['organisation'],
            'city' => $data['city'],
            'last_invited' => $data['last_invited'],
            'representative_type' => $data['representative_type']

        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewOrganisationDetails()
    {
        $response = $this::select('*')->
            orderBy('organisation', 'asc')
            -> get();
        return $response;
    }

    public function viewOrganisationDetailsByType($data)
    {
        $response = $this::where('representative_type', $data['representative_type'])
        ->select('*')
        ->orderBy('organisation', 'asc')
        -> get();
        return $response;
    }

    public function getOrganisationDetailsById($Id)
    {
        $response = $this::select('*')->where('id', $Id)->first();
        return $response;

        /*$response = $this::where('id', $Id)->select('*')->first();
        return $response;*/
    }

    public function deleteOrganisationDetails($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::DATA_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }
}