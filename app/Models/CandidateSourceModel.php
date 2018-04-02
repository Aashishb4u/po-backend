<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 8/8/17
 * Time: 9:20 PM
 */

namespace App\Models;


use App\BaseModels\BaseCandidateSourceModel;

class CandidateSourceModel extends BaseCandidateSourceModel
{
    public function saveCandidateSource($data)
    {
        $returnData = null;
        $this->source_id = $data['source_id'];
        $this->candidate_id = $data['candidate_id'];
        $this->source_info = $data['source_info'] ?? '';
        if ($this->save()) {
            $returnData = true;
        } else {
            $returnData = false;
        }
        return $returnData;
    }
}