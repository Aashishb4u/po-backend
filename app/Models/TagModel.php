<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 16/9/17
 * Time: 1:58 PM
 */

namespace App\Models;


use App\BaseModels\BaseTagModel;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use Illuminate\Support\Facades\DB;

class TagModel extends BaseTagModel
{
    public function saveTagDetails($tag)
    {
        $returnData = null;
        $this->name = $tag;
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function saveTermsDetails($tagId, $termsId)
    {
//        print_r($tagId);
//        print_r($termsId);die;
        $result = $this::where('id', $tagId)->update([
            'id_terms' => $termsId,
        ]);
        return $result;
    }

    public function updateTermsEntry($tagId)
    {
        $result = $this::where('id', $tagId)->update([
            'id_terms' => null,
        ]);
        return $result;
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

    // check for tag exist in tags table by tag Name.
    public function isTagExist($tagName)
    {
        $response = null;
        $response = $this::where('name',$tagName)->first();
        if(empty($response))
        {
            $response = array("message" => ApiConstant::TAG_NOT_EXIST);
        }
        return $response;
    }

    public function isTagIdExist($tagId)
    {
        $response = null;
        $response = $this::where('id',$tagId)->first();
        if(empty($response))
        {
            $response = array("message" => ApiConstant::TAG_NOT_EXIST);
        }
        return $response;
    }



    public function viewTerms($data)
    {
        $termsResponse = null;
        $response2 = null;
        $skip_count = null;
        $response = null;
        $count = null;
        $response3 = null;
        $skip_count = $data['page_number'] * $data['limit'];
        $termAndTags = [];
        $second= [];
        $termsResponse = $this::select('id_terms')->where('id_terms','!=', null)->distinct('id_terms')->get();

        if (count($termsResponse)) {
            foreach ($termsResponse as $termId) {
                $termAndTags['id_terms'] = $termId['id_terms'];
                $response2 = $this::where('id_terms', $termId['id_terms'])->pluck('name');
                $termAndTags['tag_name'] = $response2;
                $response3 = $this::where('id_terms', $termId['id_terms'])->pluck('id');
                $termAndTags['id_tag'] = $response3;
                array_push($second, $termAndTags);
                $termAndTags = [];
            }
        }

        $response = array_slice($second, $skip_count, $data['limit']);
        $count = count($second);

        return array($response, $count);
    }

    public function deleteTermsEntry($id_term, $id_tag)
    {
        $response = null;
        $termModelObj = new TermModel();
        $response = $this::where('id',$id_tag)->update(['id_terms' => null]);
        if ($response) {
            $response = $termModelObj::where('id', $id_term)->delete();
            if($response){
                $response = array("message" => ApiConstant::TERM_DELETED);
                DB::commit();
            }
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
        $response = $this::select('id','name')->orderBy('name','asc')->get();
        return $response;
    }

    public function deleteTag($tagId)
    {
        $error = null;
        $vendor_tags = new VendorTagModel();
        $item_tags = new ItemTagModel();
        $checkVendorTag = $vendor_tags::where('id_tag', $tagId)->first();
        $checkItemTag = $item_tags::where('id_tag', $tagId)->first();
        if (!empty($checkVendorTag)) {
            $response = array("message" => ApiConstant::TAG_DEPENDENT_VENDOR);
        } else if (!empty($checkItemTag)) {
            $response = array("message" => ApiConstant::TAG_DEPENDENT_ITEM);
        } else {
            $response = $this::where('id', $tagId)->delete();
            if ($response) {
                DB::commit();
                $response = array("message" => ApiConstant::TAG_DELETED);
            }
        }
        return $response;
    }

    public function viewTagsInTermsById($termId)
    {
        $response = null;
        $response = $this::where('id_terms',$termId)
            ->get();
        return $response;
    }

    public function viewTermsByTag($tagId)
    {
        $response = null;
        $response = $this::where('tags.id',$tagId)
            ->join('terms_conditions','tags.id_terms','terms_conditions.id')
            ->select('tags.id_terms','terms_conditions.terms')
            ->get();
        return $response;
    }
}