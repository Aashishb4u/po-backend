<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 26/6/17
 * Time: 4:49 PM
 */

namespace App\Models;


use App\BaseModels\BaseTpoTemplateModel;

class TpoConsultancyInstituteTemplateModel extends BaseTpoTemplateModel
{
    public function insertTemplateId($template_id, $user_id, $recruiter_id)
    {
        $this->id_template = $template_id;
        $this->id_tpo = $user_id;
        $this->id_recruiter = $recruiter_id;
        $this->save();
        return $this;
    }
}