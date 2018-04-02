<?php

/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 16/9/17
 * Time: 1:58 PM
 */

namespace App\Models;


use App\BaseModels\BaseTermModel;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use Illuminate\Support\Facades\DB;


class TermModel extends BaseTermModel
{
    public function saveTerms($terms)
    {
        $returnData = null;
        $this->terms = $terms;
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::ITEM_EXIST;
        }
        return $returnData;
    }

    public function viewTermsDataById($termId)
    {
        $response = null;
        $response = $this::where('id',$termId)->select('terms')->get();
        return $response;
    }

    public function updateTerms($term)
    {
        $response = null;
        $termData = $term['terms'];
        $termId = $term['term_id'];
        $returnData = null;
        $response = $this::where('id', $termId)->update([
            'terms' => $termData,
        ]);

        if ($response) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::ITEM_EXIST;
        }
        return $returnData;
    }

    public function isTagExistAndSave($tagName)
    {
        $response = null;
        $response = $this::where('name',$tagName)->first();
        if(!$response)
        {
            $response =$this->saveTagDetails($tagName);
        } else {
            $response = null;
        }

        return $response;
    }

    public function isTermExist($termId)
    {
        $response = null;
        $response = $this::where('id',$termId)->first();
        if(empty($response))
        {
            $response = ApiConstant::TAG_NOT_EXIST;
        }
        return $response;
    }

//    public function isTagExistTest($tagName)
//    {
//        $response = null;
//        $response = $this::where('name',$tagName)->first();
//        if(empty($response))
//        {
//            $response = array("message" => ApiConstant::TAG_NOT_EXIST);
//        }
//        return $response;
//    }

    public function viewAllTags()
    {
        $response = null;
        $response = $this::select('id','name')->orderBy('id','desc')->get();
        return $response;
    }

    public function deleteTag($tagId)
    {
        $vendor_tags = new VendorTagModel();
        $item_tags = new ItemTagModel();
        $response = $this::where('id', $tagId)->delete();
        $response1 = $vendor_tags::where('id_tag', $tagId)->delete();
        $response2 = $item_tags::where('id_tag', $tagId)->delete();
        if ($response && $response1 && $response2) {
            $response = array("message" => ApiConstant::TAG_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }
}