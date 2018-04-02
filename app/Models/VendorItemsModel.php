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
use App\BaseModels\BaseVendorItemsModel;
use App\BaseModels\BaseVendorTagModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use File;


class VendorItemsModel extends BaseVendorItemsModel
{
    public function isEntryExist($data)
    {
        $response = null;
        $response = $this::where('id_item',$data['id_item'])
            ->where('vendor_id',$data['vendor_id'])
            ->first();
        return $response;
    }

    public function saveVendorItem($data)
    {
        $returnData = null;
        $candidateData = $this->isEntryExist($data);
        if(empty($candidateData))
        {
            $this->id_item = $data['id_item'];
            $this->vendor_id = $data['vendor_id'];
            $this->price = $data['price'];
            if ($this->save()) {
                return array("message" => ApiConstant::VENDOR_ITEM_ADDED_SUCCESSFULLY);
            }
        }
        else{
            return ApiConstant::ITEM_EXIST;
        }
    }

    public function viewVendorItems($data)
    {
        $response = null;
        $response = $this::where('vendor_id',$data['id'])
            ->join('items','items.id','=','vendor_items.id_item')
            ->select('items.name as item_name','vendor_items.id_item','vendor_items.price','items.gst')
            ->orderBy('vendor_items.id', 'desc')
            ->get();
        return $response;
    }

    public function viewItemVendors($data)
    {
        $response = null;
        $response = $this::where('id_item',$data['id'])
            ->join('vendors','vendors.id_user','=','vendor_items.vendor_id')
            ->select('vendors.company_name as vendor_name','vendors.id_user','vendor_items.price')
            ->orderBy('vendor_items.id', 'desc')
            ->get();
        return $response;
    }

    public function deleteVendorItem($data)
    {
        $response = null;
        $response = $this::where('id_item',$data['id_item'])
            ->where('vendor_id',$data['vendor_id'])
            ->delete();
        if ($response) {
            $response = array("message" => ApiConstant::ITEM_DELETED);
        } else {
            $response = ApiConstant::ID_NOT_FOUND;
        }
        return $response;
    }

}