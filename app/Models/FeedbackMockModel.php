<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 7/9/17
 * Time: 2:55 PM
 */

namespace App\Models;


use App\BaseModels\BaseFeedbackMockModel;
use App\Helpers\ApiConstant;

class FeedbackMockModel extends BaseFeedbackMockModel
{
    public function addFeedbackMock($data)
    {
        $returnData = null;
        $this->feedback_data = $data['feedback_data'];
        if ($this->save()) {
            $returnData = array("message" => ApiConstant::DATA_ADDED);
        } else {
            $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
        }
        return $returnData;
    }

    public function editFeedbackMock($data)
        {
            $returnData = null;
            $update = $this::where('id', $data['id'])->update([
                'feedback_data' => $data['feedback_data']
            ]);
            if ($update) {
                $returnData = array("message" => ApiConstant::UPDATED_SUCCESSFULLY);
            } else {
                $returnData = array("message" => ApiConstant::DATA_NOT_SAVED);
            }
            return $returnData;
        }

    public function viewFeedbackMock()
    {
        $response = $this::select('*')->
        orderBy('id', 'desc')
            -> get();
        return $response;
    }

    public function getFeedbackMockById($id)
    {
        $response = $this:: where('id', $id)->first();
        return $response;
    }

}