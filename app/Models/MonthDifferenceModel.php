<?php

namespace App\Models;

use App\BaseModels\BaseMonthDifferenceModel;
use App\Helpers\ApiConstant;

class MonthDifferenceModel extends BaseMonthDifferenceModel
{
    public function addNoOfMonths($data)
    {
        $returnData = null;
        $months = $data['month_difference'];
        $id = $data['id'];
        $update = BaseMonthDifferenceModel::where('id', $id)->update([
            'month_difference' => $months
        ]);
        if ($update) {
            $returnData = ApiConstant::UPDATED_SUCCESSFULLY;
        }
        return $returnData;
    }

    public function viewNoOfMonths()
    {
        $response = BaseMonthDifferenceModel::all();
        return $response;
    }
}
