<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 19/2/18
 * Time: 8:14 PM
 */

namespace App\Models;

use App\BaseModels\BasePurchaseOrderModel;
use File;
use App\BaseModels\BasePaymentDetailsModel;


class PaymentDetailsModel extends BasePaymentDetailsModel
{
    public function savePaymentDetails($data)
    {
        $response = null;
        $error = null;
        $amountToSave = null;
        $vendorId = $data['vendor_id'] ?? null;
        $purchaseId = $data['purchase_id'] ?? null;
        $date = $data['date'] ?? null;
        $paymentMode = $data['payment_mode'] ?? null;
        $paymenAmount = $data['payment_amount'] ?? 0;
        $paymenAmountReceived = $data['amount_received'] ?? 0;
        $amountToSave = $paymenAmountReceived + $paymenAmount;
        $this->payment_mode = $paymentMode;
        $this->payment_amount = $paymenAmount;
        $this->vendor_id = $vendorId;
        $this->purchase_id = $purchaseId;
        $this->date = $date;
        $response = ($this->save()) ? $this : false;
        $purchaseObj = new BasePurchaseOrderModel();
        if ($response) {
            $response = $purchaseObj::where('id',$purchaseId)
                ->where('vendor_id',$vendorId)
                ->update(['amount_recieved' => $amountToSave]);
            $response = $response ? $this : false;
        }
        return $response;
    }

    public function updatePaymentAmount($data) {
        $amountToSave = $data['amount_received'];
        $vendorId = $data['vendor_id'];
        $purchaseId = $data['purchase_id'];
        $response = null;
        $error = null;
        $purchaseObj = new BasePurchaseOrderModel();
        $response = $purchaseObj::where('id',$purchaseId)
            ->where('vendor_id',$vendorId)
            ->update(['amount_recieved' => $amountToSave]);
        return $response = $response ? $this : false;
    }

    public function updatePaymentDetails($data)
    {
        $returnData = null;
        $error = null;
        $response = null;
        $date = $data['date'] ?? null;
        $paymentMode = $data['payment_mode'] ?? null;
        $paymentId = $data['payment_id'] ?? null;
        $paymenAmount = $data['payment_amount'] ?? 0;
        $response = $this::where('id',$paymentId)->update([
            'date' => $date,
            'payment_mode' => $paymentMode,
            'payment_amount' => $paymenAmount,
        ]);
        return $response = ($response) ? true : false;
    }

    public function deletePayment($data)
    {
        $returnData = null;
        $error = null;
        $user = null;
        $deleteResponse = null;
        $response = null;
        $paymentId = $data['payment_id'] ?? null;
        $response = $this::where('id', $paymentId)->first();
        $user['purchase_id'] = $response['purchase_id'];
        $user['vendor_id'] = $response['vendor_id'];
        $purchaseOrder = new BasePurchaseOrderModel();
        $totalAmount = $purchaseOrder::where('id',$user['purchase_id'])->where('vendor_id',$user['vendor_id'])->select('purchase_orders.amount_recieved')->first();
        $user['total_amount'] = $totalAmount['amount_recieved'];
        $user['amount_received'] = $user['total_amount'] - $response['payment_amount'];
        $response = $this::updatePaymentAmount($user);
        $deleteResponse = $this::where('id', $paymentId)->delete();
        return $response = ($deleteResponse) ? true : false;
    }

    public function viewPaymentDetails($data)
    {
        $vendorId = $data['vendor_id'];
        $PurchaseId = $data['purchase_id'];
        $response = null;
        $purchaseOrder = new BasePurchaseOrderModel();
        $total = $purchaseOrder::where('id',$PurchaseId)->where('vendor_id',$vendorId)->select('purchase_orders.total_amount')->get();
        $response = $this::where('vendor_id',$vendorId)
            ->where('purchase_id',$PurchaseId)
            ->select('*')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return array($response, $total);
    }
}