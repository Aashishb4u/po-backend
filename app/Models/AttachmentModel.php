<?php

namespace App\Models;

use App\BaseModels\BaseAttachmentModel;

class AttachmentModel extends BaseAttachmentModel
{
    public function saveResume($data)
    {
        $this->email = $data['email'];
        $this->resume = $data['resume'];
        return $this->save();
    }

    public function viewAttachments($email)
    {
        $result = null;
        $error = null;
        $result = $this::select('resume')->where('email', $email)->get();
        return $result;
    }
}
