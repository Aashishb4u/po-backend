<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 16/9/17
 * Time: 4:14 PM
 */

namespace App\Models;



use App\BaseModels\BaseVendorTagModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use File;


class VendorTagModel extends BaseVendorTagModel
{
    public function isUserDetailsExist($data)
    {
        $response = null;
        $response = $this::where('vendor_id',$data['id'])
            ->where('id_tag',$data['id_tag'])
            ->first();
        return $response;
    }

    public function saveCandidateTag($data)
    {
       $returnData = null;
//        print_r($data);die;
        $candidateData = $this->isUserDetailsExist($data);
      if(empty($candidateData))
      {
//                  print_r('here');die;
          $this->vendor_id = $data['id'];
          $this->id_tag = $data['id_tag'];
          if ($this->save()) {
          return true;
          }
          else{
              return false;
          }
      }
    }

    public function viewVendorTags($data)
    {
        $response = null;
        $response = $this::where('vendor_tags.vendor_id',$data['id'])
            ->join('tags','tags.id','=','vendor_tags.id_tag')
            ->select('tags.name as tag_name','tags.id as tag_id')
            ->get();
        return $response;
    }

    public function deleteVendorTag($data)
    {
        $response = null;
        $response = $this::where('vendor_id',$data['id'])
            ->where('id_tag',$data['id_tag'])
            ->delete();
        return $response;
    }

}