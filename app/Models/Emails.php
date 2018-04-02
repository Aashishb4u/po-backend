<?php

namespace App\Models;

use App\BaseModels\BaseEmailModel;

class Emails extends BaseEmailModel
{
    public function insertEmail($userEmail, $name, $template_id, $content, $subject, $status, $recruiter_id, $userId, $dateTime, $userType)
    {
        $this->from_name = 'Recruitment Team';
        $this->from_email = 'joinus@tudip.com';
        $this->to_name = $name;
        $this->to_email = $userEmail;
        $this->template_id = $template_id;
        $this->is_sent = 0;
        $this->content = $content ?? '';
        $this->subject = $subject ?? '';
        $this->status = $status;
        $this->id_recruiter = $recruiter_id;
        $this->id_user = $userId;
        $this->date_time = $dateTime;
        $this->user_type = $userType;
        $this->save();
        return $this;
    }

    public function insertIsSent($id, $hash)
    {
        $result = BaseEmailModel::where('id', $id)->update([
            'is_sent' => 1,
            'hash'=> $hash
        ]);
        return $result;
    }

    public function getLogdataByHash($hash)
    {
        $result = $this::select('*')->where('hash', $hash)->first();
        return $result;
    }

    public function InsertLogData($userEmail, $toMail, $name, $content, $subject, $status, $recruiter_id, $userId, $dateTime, $userType, $parentId, $hash)
    {
        $this->from_name = $name;
        $this->from_email = $userEmail;
        $this->to_name = 'Recruitment Team';
        $this->to_email = $toMail;
        $this->template_id = 0;
        $this->is_sent = 0;
        $this->content = $content ?? '';
        $this->subject = $subject ?? '';
        $this->status = $status;
        $this->id_recruiter = $recruiter_id;
        $this->id_user = $userId;
        $this->date_time = $dateTime;
        $this->user_type = $userType;
        $this->parentId = $parentId;
        $this->hash = $hash;
        $this->save();
        return $this;
    }

    public function viewCandidateEmailLogs($data)
    {
        $result = $this::where('from_email', $data['candidate_email'])->
            orwhere('to_email',$data['candidate_email'])->
            orderBy('created_at','asc')->
            select('*')->
            get();
        return $result;
    }
}
