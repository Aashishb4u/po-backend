<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 17/8/17
 * Time: 11:24 PM
 */

namespace App\Models;


use App\BaseModels\BaseSenderModel;
use App\Helpers\ApiConstant;

class SenderModel extends BaseSenderModel
{
    public function addSenderDetails($data)
    {
        $returnData = null;
        $senderName = $data['sender_name'];
        $senderEmail = $data['sender_email'];
        $id = $data['id'];
        $update = $this::where('id', $id)->update([
            'sender_name' => $senderName,
            'sender_email' => $senderEmail
        ]);
        if ($update) {
            $returnData = ApiConstant::UPDATED_SUCCESSFULLY;
        }
        return $returnData;
    }

    public function viewSenderDetails()
    {
        $response = $this::first();
        return $response;
    }
}