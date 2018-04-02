<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 15/6/17
 * Time: 1:31 PM
 */

namespace App\Models;
use App\BaseModels\BaseExpectedJoinerTemplateModel;

class ExpectedJoinersTemplateSendModel extends BaseExpectedJoinerTemplateModel
{
    public function insertTemplateId($template_id, $user_id, $recruiter_id, $isSent)
    {
        $this->id_template = $template_id;
        $this->id_expected_joiner = $user_id;
        $this->id_recruiter = $recruiter_id;
        $this->is_sent = $isSent;
        $this->save();
        return $this;
    }

    public function updateCandidateIsSent($template_id, $user_id, $isSent)
    {
        $result = null;
        $result = $this::where('id_expected_joiner', $user_id)
            ->where('id_template', $template_id)
            ->update([
            'is_sent' => $isSent,
        ]);
        return $result;

    }
}