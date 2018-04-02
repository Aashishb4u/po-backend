<?php

namespace App\Http\Controllers;

use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use Illuminate\Http\Response;

abstract class AppController extends Controller
{
    public function returnableResponseData($dataObject, $error, $message = null){
        $responseData = array();
        $status = ApiConstant::SUCCESS_CODE;
        if ($error) {
            $responseData = $this->_terminateResponse($error, $message);
            $status = ApiConstant::ERROR_STATUS;
        } else {
            $responseData = $this->_success($dataObject);
        }
        return $this->_sendResponse($responseData, $status);
    }

    private function _terminateResponse($errorCode = -1, $message){
        $response = $this->_terminate($errorCode, $message);
        return array('response_status' => AppUtility::getHTTPStatusCodeForErrorCode($errorCode), 'response_content' => $response);
    }

    private function _success($responseData){
        $res = '';
        if (is_array($responseData)) {
            $res = $responseData;
        } else {
            $res = array($responseData);
        }
        $httpResponseCode = ApiConstant::HTTP_RESPONSE_CODE_SUCCESS;
        return array('response_status' => $httpResponseCode, 'response_content' => $res);
    }

    private function _sendResponse($responseData, $status){
        $responseArray = array();
        $responseStatus = isset($responseData['response_status']) ? $responseData['response_status'] : ApiConstant::HTTP_RESPONSE_CODE_SUCCESS;
        $responseContent = isset($responseData['response_content']) ? $responseData['response_content'] : array();
        $responseArray = array("status" => $status, "data" => $responseContent);
        $response = new Response($responseArray, $responseStatus);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @param int $errorCode
     * @return string
     */
    private function _terminate($errorCode = -1, $message){
        if (!$message) {
            $message = AppUtility::getMessageForErrorCode($errorCode);
        }
        $res = array('code' => intval($errorCode), 'message' => $message);
        return $res;
    }
    public function getTrimmedString($text)
    {
        return trim($text);
    }
}
