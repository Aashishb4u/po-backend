<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 16/9/17
 * Time: 4:14 PM
 */

namespace App\Models;


//use App\Helpers\ApiConstant;
use App\BaseModels\BaseItemTagModel;
use App\BaseModels\BaseTagModel;
use App\BaseModels\BaseVendorTagModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use File;


class ItemTagModel extends BaseItemTagModel
{
    public function isTagExist($data)
    {
        $response = null;
        $response = $this::where('id_item',$data['id_item'])
            ->where('id_tag',$data['id_tag'])
            ->first();
        return $response;
    }

    public function saveItemTag($data)
    {
        $returnData = null;
        $candidateData = $this->isTagExist($data);
        if(empty($candidateData))
        {
            $this->id_item = $data['id_item'];
            $this->id_tag = $data['id_tag'];
            if ($this->save()) {
                return true;
            }
            else{
                return false;
            }
        }

    }

    public function viewItemTags($data)
    {
        $response = null;
        $response = $this::where('item_tags.id_item',$data['id'])
            ->join('tags','tags.id','=','item_tags.id_tag')
            ->select('tags.name as tag_name','tags.id as tag_id')
            ->get();
        return $response;
    }

    public function deleteItemTag($data)
    {
        $response = null;
        $response = $this::where('id_item',$data['id_item'])
            ->where('id_tag',$data['id_tag'])
            ->delete();
        return $response;
    }

}