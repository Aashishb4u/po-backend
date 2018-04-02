<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 19/2/18
 * Time: 8:24 PM
 */

namespace App\Http\Controllers;
use App\Models\PaymentDetailsModel;
use App\Helpers\ApiConstant;
use Illuminate\Http\Request;

class PaymentDetailsController extends AppController
{
    // API is used to add payment details of purchase order.
    public function addPaymentDetails(Request $request)
    {
        $response = null;
        $message = null;
        $userData = $request->input();
        $user['purchase_id'] = $userData['purchase_id'];
        $user['vendor_id'] = $userData['vendor_id'];
        $user['date'] = $userData['date'];
        $user['payment_mode'] = $userData['payment_mode'];
        $user['payment_amount'] = $userData['payment_amount'];
        $user['amount_received'] = $userData['amount_received'];
        $error = null;
        try {
            if (!empty($user)) {
                $tagModelObj = new PaymentDetailsModel();
                $tagResponse = $tagModelObj->savePaymentDetails($user);
                if($tagResponse) {
                    $response = array("message" => ApiConstant::PAYMENT_ADDED_SUCCESSFULLY);
                } else {
                    $error = ApiConstant::ADD_PAYMENT_FAIL;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to delete payment details of purchase order.
    public function deletePaymentDetails(Request $request)
    {
        $response = null;
        $message = null;
        $userData = $request->input();
        $user['payment_id'] = $userData['payment_id'];
        $error = null;
        try {
            if (!empty($user)) {
                $tagModelObj = new PaymentDetailsModel();
                $tagResponse = $tagModelObj->deletePayment($user);
                if($tagResponse) {
                    $response = array("message" => ApiConstant::PAYMENT_DELETED_SUCCESSFULLY);
                } else {
                    $error = ApiConstant::ADD_PAYMENT_FAIL;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to edit payment details of purchase order.
    public function editPaymentDetails(Request $request)
    {
        $response = null;
        $message = null;
        $userData = $request->input();
        $user['payment_id'] = $userData['payment_id'];
        $user['date'] = $userData['date'];
        $user['payment_mode'] = $userData['payment_mode'];
        $user['payment_amount'] = $userData['payment_amount'];
        $error = null;
        try {
            if (!empty($user)) {
                $tagModelObj = new PaymentDetailsModel();
                $tagResponse = $tagModelObj->updatePaymentDetails($user);
                if($tagResponse) {
                    $response = array("message" => ApiConstant::PAYMENT_ADDED_SUCCESSFULLY);
                } else {
                    $error = ApiConstant::ADD_PAYMENT_FAIL;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to get payment details of purchase order by vendor_id and purchase_id.
    public function getPaymentDetails(Request $request)
    {
        $response = null;
        $message = null;
        $error = null;
        $tagResponse = null;
        $userData = $request->input();
        $user['vendor_id'] = $userData['vendor_id'];
        $user['purchase_id'] = $userData['purchase_id'];
        try {
            if (!empty($user)) {
                $tagModelObj = new PaymentDetailsModel();
                $response = $tagModelObj->viewPaymentDetails($user);
                if($response) {
                    $message = array("message" => ApiConstant::PAYMENT_ADDED_SUCCESSFULLY);
                } else {
                    $error = ApiConstant::ADD_PAYMENT_FAIL;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

}