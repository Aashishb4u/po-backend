<?php

namespace App\Models;

use App\BaseModels\BaseCandidateTemplateModel;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use App\BaseModels\BaseMonthDifferenceModel;

class CandidateTemplateSend extends BaseCandidateTemplateModel
{
    public function insertTemplateId($template_id, $user_id, $recruiter_id, $isSent)
    {
        $this->id_template = $template_id;
        $this->id_candidate = $user_id;
        $this->id_recruiter = $recruiter_id;
        $this->is_sent = $isSent;
        $this->save();
        return $this;

    }

    public function isTemplateSend($template_id, $user_id,$createdDate)
    {
        $result = BaseCandidateTemplateModel::where('id_template', $template_id)->where('id_candidate', $user_id)->first();

        if (empty($result))
        {
         return 1;
        }else{
            $month = BaseMonthDifferenceModel::select('month_difference')->first();
            $days = $month->month_difference * 30;
            $oldDate = new DateTime($createdDate);
            $now = new DateTime(Date('Y-m-d'));
            $interval = $oldDate->diff($now);
            if($interval->days >= $days){
                return 1;
            }else{
                return 0;
            }
        }
    }

    public function updateCandidateIsSent($template_id, $user_id, $isSent)
    {
        $result = null;
        $result = $this::where('id_candidate', $user_id)
            ->where('id_template', $template_id)
            ->update([
                'is_sent' => $isSent,
            ]);
        return $result;

    }
}
