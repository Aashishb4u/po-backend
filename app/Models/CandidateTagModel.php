<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 16/9/17
 * Time: 4:14 PM
 */

namespace App\Models;



use App\BaseModels\BaseCandidateTagModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use File;


class CandidateTagModel extends BaseCandidateTagModel
{
    public function isUserDetailsExist($data)
    {
        $response = null;
        $response = $this::where('candidate_id',$data['id'])
            ->where('id_tag',$data['id_tag'])
            ->first();
        return $response;
    }

    public function saveCandidateTag($data)
    {
       $returnData = null;
      $candidateData = $this->isUserDetailsExist($data);
      if(empty($candidateData))
      {
          $this->candidate_id = $data['id'];
          $this->id_tag = $data['id_tag'];
          if ($this->save()) {
          return true;
          }
          else{
              return false;
          }
      }
    }

    public function viewCandidateTags($data)
    {
        $response = null;
        $response = $this::where('candidate_tags.candidate_id',$data['id'])
            ->join('tags','tags.id','=','candidate_tags.id_tag')
            ->select('tags.name as tag_name','tags.id as tag_id')
            ->get();
        return $response;
    }

    public function deleteCandidateTag($data)
    {
        $response = null;
        $response = $this::where('candidate_id',$data['id'])
            ->where('id_tag',$data['id_tag'])
            ->delete();
        return $response;
    }

}