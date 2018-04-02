<?php

namespace App\Models;

use App\BaseModels\BaseRejectedDomainModel;
use App\Helpers\ApiConstant;

class RejectedDomainModel extends BaseRejectedDomainModel
{
    public function domainList()
    {
        $result = $this::orderBy('rejected_domains.name', 'asc')->select('name')->get();
        return $result;
    }

    public function addRejectedDomain($data)
    {
        $returnData = null;
        $this->name = $data['name'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::REJECTED_DOMAIN_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editRejectedDomain($data)
    {
        $returnData = null;
        $name = $data['name'];
        $id = $data['id'];
        $update = $this::where('id', $id)->update([
            'name' => $name,
        ]);
        if ($update) {
            $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function viewRejectedDomain()
    {
        $response = $this::orderBy('rejected_domains.name', 'asc')->select('*')->get();
        return $response;
    }

    public function getRejectedDomainById($jobId)
    {
        $response = $this::where('id', $jobId)->select('*')->get();
        return $response;
    }

    public function deleteRejectedDomain($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::REJECTED_DOMAIN_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }
}
