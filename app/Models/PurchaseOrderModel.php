<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 22/1/18
 * Time: 4:01 PM
 */

namespace App\Models;

use App\BaseModels\BaseItemQuantityModel;
use App\BaseModels\BaseItemTagModel;
use App\BaseModels\BaseTagModel;
use App\BaseModels\BasePurchaseOrderModel;
use App\BaseModels\BasePurchaseOrderDetailsModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use File;

class PurchaseOrderModel extends BasePurchaseOrderModel
{
    public function savePurchaseOrder($data)
    {
        $returnData = null;
        $error = null;
        $vendorId = $data['vendor_id'] ?? null;
        $amount = $data['total_amount'] ?? null;
        $terms = $data['terms_data'] ?? null;
        $status = $data['status'] ?? null;
            $this->terms = $terms;
            $this->vendor_id = $vendorId;
            $this->total_amount = $amount;
            $this->status = $status;
            if ($this->save()) {
                return $this;
            } else {
                return false;
            }
    }

    public function updatePurchaseOrder($data)
    {
        $returnData = null;
        $error = null;
        $purchaseDetailsObj = null;
        $vendorId = $data['vendor_id'] ?? null;
        $amount = $data['total_amount'] ?? null;
        $terms = $data['terms_data'] ?? null;
        $purchaseId = $data['purchase_id'] ?? null;

        $result = $this::where('vendor_id', $vendorId)
            ->where('id', $purchaseId)->update([
            'total_amount' => $amount,
            'terms' => $terms,
        ]);
        return $result;

//        if ($result) {
//            $result = $purchaseDetailsObj::where('purchase_id', $purchaseId)->update([
//                'total_amount' => $amount,
//            ]);
//        }
//            $this->terms = $terms;
//            $this->vendor_id = $vendorId;
//            $this->total_amount = $amount;
//            $this->status = $status;
//            if ($this->save()) {
//                return $this;
//            } else {
//                return false;
//            }
    }



//    public function sendTestPurchaseOrder($data)
//    {
//
//    }
    public function getPurchaseOrder($data)
    {
        $result = null;
        $purchaseId = $data['purchase_id'] ?? null;
        $vendorId = $data['vendor_id'] ?? null;
        $result = $this::where('vendor_id', $vendorId)
            ->where('id', $purchaseId)->select('pdf_name')->first();
        return $result;
    }

    public function sendPurchaseOrder($data)
    {
        $error = null;
        $vendorId = $data['vendor_id'] ?? null;
        $pdf = $data['pdf_file'] ?? null;
        $purchaseId = $data['purchase_id'] ?? null;
        $status = 'Sent';
        $result = $this::where('vendor_id', $vendorId)
            ->where('id', $purchaseId)->first();
        if($result) {
            $result->status = $status;
            $result->pdf_name = $pdf;
            $result->save();
        }
        return $result;
    }

    public function sendTestPurchaseOrder($data)
    {
        $error = null;
        $vendorId = $data['vendor_id'] ?? null;
        $pdf = $data['pdf_file'] ?? null;
        $purchaseId = $data['purchase_id'] ?? null;
        $status = 'Draft';
        $result = $this::where('vendor_id', $vendorId)
            ->where('id', $purchaseId)->first();
        if($result) {
            $result->status = $status;
            $result->pdf_name = $pdf;
            $result->save();
        }
        return $result;
    }

    public function checkItemEntry($data)
    {
        $purchaseDetailsObj = new BasePurchaseOrderDetailsModel();
        $response = null;
        $response = $purchaseDetailsObj::where('purchase_id',$data['purchase_id'])->where('id_item',$data['id_item'])->first();
        return $response;
    }

    public function deletePurchaseOrderDetails($data)
    {
        $returnData = null;
        $error = null;
        $result = null;
        $deleteData = null;
        $purchaseDetailsObj = new BasePurchaseOrderDetailsModel();
        $deleteData['purchase_id'] = $data['purchase_id'];
        $deleteData['id_item'] = $data['id_item'];
        $result = $purchaseDetailsObj::where('purchase_id', $deleteData['purchase_id'])
            ->where('id_item', $deleteData['id_item']);
        if ($result->first()) {
            $result = $result->delete();
        }
        return $result;
    }


    public function updatePurchaseOrderDetails($data)
    {
        $returnData = null;
        $error = null;
        $result = null;
        $purchaseData = null;
        $purchaseData['price'] = $data['price'] ?? null;
        $purchaseData['purchase_id'] = $data['purchase_id'] ?? null;
        $purchaseData['id_item'] = $data['id_item'] ?? null;
        $purchaseData['quantity'] = isset($data['quantity']) ? $data['quantity'] : 0;
        $purchaseData['total'] = isset($data['total']) ? $data['total'] : 0;
        $purchaseData['gst'] = isset($data['gst']) ? $data['gst'] : 0;
//        dd($purchaseData['gst']);
        $purchaseDetailsObj = null;
//        dd($purchaseData);
        $purchaseDetailsObj = new BasePurchaseOrderDetailsModel();
            $result = $purchaseDetailsObj::where('purchase_id', $purchaseData['purchase_id'])
                ->where('id_item', $purchaseData['id_item'])->first();
            if ($result) {
              $result->total = $purchaseData['total'];
              $result->quantity = $purchaseData['quantity'];
              $result->price = $purchaseData['price'];
              $result->gst = $purchaseData['gst'];
              $result->save();
            } else {
                $result = $this->savePurchaseOrderDetails($purchaseData, $purchaseData['purchase_id']);
            }
            return ($result) ? true : false;
    }

    public function savePurchaseOrderDetails($data, $purchaseId)
    {
        $returnData = null;
        $error = null;
        $purchaseDetailsObj = null;
        $purchaseDetailsObj = new BasePurchaseOrderDetailsModel();
        $itemId = $data['id_item'] ?? null;
        $price = $data['price'] ?? null;
        $quantity = $data['quantity'] ?? null;
        $gst = $data['gst'] ?? 0;
        $total = $data['total'] ?? null;
        $purchaseDetailsObj->id_item = $itemId;
        $purchaseDetailsObj->price = $price;
        $purchaseDetailsObj->quantity = $quantity;
        $purchaseDetailsObj->gst = $gst;
        $purchaseDetailsObj->total = $total;
        $purchaseDetailsObj->purchase_id = $purchaseId;
        if ($purchaseDetailsObj->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function viewPurchaseOrder($data)
    {
        $response = null;
        $count = null;
        $hasDraft = null;

        $responseData = null;
        $response = $this::where('purchase_orders.vendor_id',$data['vendor_id'])
            ->select(DB::raw("Exists(select id from purchase_order_details pod where pod.purchase_id = purchase_orders.id ) as isExist"),'purchase_orders.id', 'purchase_orders.total_amount','purchase_orders.amount_recieved','purchase_orders.status')
            ->orderBy('purchase_orders.id', 'desc')
            ->distinct('purchase_orders.id');
        $count = $response->get()->count();
        $responseData = $response->paginate(10);
        $draftResponse = $this::where('purchase_orders.vendor_id',$data['vendor_id'])
            ->where('purchase_orders.status','Draft')
            ->first();
        $hasDraft['hasDraft'] = empty($draftResponse) ? false : true;
        return array($responseData, $count, $hasDraft);
    }

    public function viewPurchaseDetailsByItemId($data) {
        $purchaseResponse = null;
        $response = null;
        $quantity = null;
        $purchaseDetailsObj = new BasePurchaseOrderDetailsModel();
        $purchaseResponse = $purchaseDetailsObj::where('purchase_id', $data['purchase_id'])
            ->where('id_item', $data['id_item'])->first();
//        dd($purchaseResponse->quantity);
        $itemLogsObject = new BaseItemQuantityModel();
        $response = $itemLogsObject::where('purchase_id',$data['purchase_id'])
            ->where('item_id', $data['id_item'])->select(DB::raw("SUM(item_quantity_logs.quantity) as quantity"))->first();
        $quantity = $purchaseResponse->quantity - $response->quantity;
        $purchaseResponse->quantity = $quantity;
        return $purchaseResponse;
    }

    public function viewPurchaseOrderById($data)
    {
        $response = null;
        $response = $this::where('purchase_orders.id',$data['purchase_id'])
            ->join('purchase_order_details','purchase_orders.id','purchase_order_details.purchase_id')
            ->join('items','purchase_order_details.id_item','items.id')
            ->select('purchase_orders.*','purchase_order_details.*','items.name as item_name')
            ->orderBy('purchase_orders.id', 'desc')
            ->distinct('purchase_orders.id', 'desc')
            ->get();

        $total = $this::where('purchase_orders.id',$data['purchase_id'])
            ->select('purchase_orders.total_amount')
            ->first();

        $terms = $this::where('purchase_orders.id',$data['purchase_id'])
            ->select('purchase_orders.terms')
            ->first();
        return array($response,$total,$terms);
    }

    public function checkPurchaseStatus($data)
    {
        $response = null;
        $vendorId = $data['vendor_id'];
        $response = $this::where('purchase_orders.status','Draft')
            ->where('purchase_orders.vendor_id',$vendorId)
            ->first();
        return $response;
    }

    public function deletePurchaseOrderById($data)
    {
        $response = null;
        $purchaseDetailsObj = new BasePurchaseOrderDetailsModel();

        $responsePurchase = $this::where('purchase_orders.id',$data['purchase_id'])
            ->where('purchase_orders.vendor_id',$data['vendor_id'])
            ->delete();

        if(empty($responsePurchase)) {
            return $response = ApiConstant::ID_NOT_FOUND;
        }

        $responseDetails = $purchaseDetailsObj::where('purchase_order_details.purchase_id',$data['purchase_id'])
            ->delete();

        if ($responseDetails || $responsePurchase) {
            return $response = array("message" => ApiConstant::PURCHASE_DELETED);
        } else {
            return $response = ApiConstant::ID_NOT_FOUND;
        }

    }

}