<?php

namespace App\Models;

use App\BaseModels\BaseForbiddenDomainModel;
use App\Helpers\ApiConstant;

class ForbiddenDomainModel extends BaseForbiddenDomainModel
{
    public function saveEmailData($user)
    {
        $returnData = null;
        $this->email_data = $user['email_data'];
        $this->reason = $user['reason'];
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }
}
