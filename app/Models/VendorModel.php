<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 5/12/17
 * Time: 8:41 PM
 */

namespace App\Models;
use App\BaseModels\BaseUserModel;
use App\BaseModels\BaseUserRoleModel;
use App\BaseModels\BaseVendorModel;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;



class VendorModel extends BaseVendorModel
{
    public function saveVendor($user)
    {
        $returnData = null;
        $id = $user['id'];
        $companyName = ($user['company_name']) ? $user['company_name'] : '';
        $alternateContact = ($user['alternate_contact_number']) ? $user['alternate_contact_number'] : '';
        $alternateEmail = ($user['alternate_email']) ? $user['alternate_email'] : '';
        $addressOne = ($user['address_one']) ? $user['address_one'] : '' ;
        $addressTwo = ($user['address_two']) ? $user['address_two'] : '';
        $city = ($user['city'])? $user['city'] : '';
        $state = ($user['state'])? $user['state'] : '';
        $pin_code = ($user['pin_code'])?$user['pin_code']: 0;
            $response = BaseVendorModel::insert(
                [
                    'company_name' => $companyName,
                    'address_line_one' => $addressOne,
                    'address_line_two' => $addressTwo,
                    'city' => $city,
                    'alternate_email' => $alternateEmail,
                    'alternate_contact' => $alternateContact,
                    'state' => $state,
                    'pin_code' => $pin_code,
                    'id_user' => $id

                ]);
        return $response;
    }

    public function isTermsAssignedToVendor($user){
        $response = null;
        $vendorId = $user['vendor_id'];
        $response = $this::where('id_user', $vendorId)
            ->select('vendors.terms_tag')->first();
        return $response;
    }

    public function checkVendorDependancy($id)
    {
        $response = $this::where('vendors.id_user', $id)
            ->where('purchase_orders.vendor_id',$id)
            ->join('purchase_orders','vendors.id_user','purchase_orders.vendor_id')
            ->first();
        return $response;
    }

    public function deleteVendor($id)
    {
        $response = $this::where('id_user', $id)->delete();
        return $response;
    }

    public function viewVendorNames()
    {
        $response = null;
        $response = $this::select('vendors.id_user','vendors.company_name')->orderBy('vendors.company_name','asc')->get();
        $response2 = null;
        $response2 = $this::leftjoin('purchase_orders','vendors.id_user', '=', 'purchase_orders.vendor_id' )->where('purchase_orders.status','=','Draft')->select('purchase_orders.status', 'purchase_orders.id as purchase_id','vendors.id_user','vendors.company_name')->orderBy('vendors.company_name','asc')->get();
        return array($response,$response2);
    }

    public function viewVendorByItemId($data)
    {
        $response = null;
        $item_id = $data['id_item'];
        $vendorItems = new ItemTagModel();
        $response = $vendorItems::where('item_tags.id_item', $item_id)
            ->join('vendor_tags','item_tags.id_tag','vendor_tags.id_tag')
            ->join('vendors','vendor_tags.vendor_id','vendors.id_user')
            ->select('vendor_tags.vendor_id','vendors.company_name')
            ->groupBy('vendor_tags.vendor_id','vendors.company_name')
            ->orderBy('vendors.company_name','asc')
            ->get();
        return $response;
    }

    public function updateVendorDetails($data)
    {
        // status
        $update_status = ($data['update_status']) ? $data['update_status'] : '';
        $result = null;

        if ($update_status == 'contact') {

            // contact Information
            $alternateContact = ($data['alternate_contact_number']) ? $data['alternate_contact_number'] : '';
            $alternateEmail = ($data['alternate_email']) ? $data['alternate_email'] : '';
            $companyName = ($data['company_name']) ? $data['company_name'] : '';
            $addr1 = ($data['address_one']) ? $data['address_one'] : '';
            $addr2 = ($data['address_two']) ? $data['address_two'] : '';
            $pinCode = ($data['pin_code']) ? $data['pin_code'] : 0;
            $city = ($data['city']) ? $data['city'] : '';
            $state = ($data['state']) ? $data['state'] : '';
            $result = BaseVendorModel::where('id_user', $data['id'])->update(
                [
                    'company_name' => $companyName,
                    'address_line_one' => $addr1,
                    'alternate_contact' => $alternateContact,
                    'alternate_email' => $alternateEmail,
                    'address_line_two' => $addr2,
                    'pin_code' => $pinCode,
                    'city' => $city,
                    'state' => $state,
                ]);
        } else if ($update_status == 'finance') {

            // finance Information
            $bankName = ($data['bank_name']) ? $data['bank_name'] : '';
            $bankNumber = ($data['bank_number']) ? $data['bank_number'] : '';
            $accounttype = ($data['bank_type']) ? $data['bank_type'] : 0;
            $pan = ($data['pan_number']) ? $data['pan_number'] : '';
            $ifsc = ($data['ifsc_code']) ? $data['ifsc_code'] : '';
            $gst = ($data['gst_number']) ? $data['gst_number'] : '';
            $terms= ($data['terms_data']) ? $data['terms_data'] : '';
            $terms_id= ($data['terms_id']) ? $data['terms_id'] : 0;
            $terms_tag= ($data['terms_tag']) ? $data['terms_tag'] : 0;
            $result = BaseVendorModel::where('id_user', $data['id'])->update(
                [
                    'bank_name' => $bankName,
                    'account_number' => $bankNumber,
                    'bank_type' => $accounttype,
                    'pan_number' => $pan,
                    'gst_number' => $gst,
                    'ifsc_code' => $ifsc,
                    'terms' => $terms,
                    'terms_id' => $terms_id,
                    'terms_tag' => $terms_tag,
                ]);
        }


//        $result = BaseVendorModel::where('id_user', $data['id'])->update(
//            [
//            'company_name' => $companyName,
//            'address_line_one' => $addr1,
//            'address_line_two' => $addr2,
//            'pin_code' => $pinCode,
//            'city' => $city,
//            'pan_number' => $pan,
//            'gst_number' => $gst,
//            'state' => $state,
//            'bank_name' => $bankName,
//            'account_number' => $bankNumber,
//            'ifsc_code' => $ifsc,
//            'bank_type' => $accounttype,
//            'terms' => $terms,
//            'terms_id' => $terms_id,
//        ]
//        );
        return $result;
    }

    public function getUserCount($user)
    {
        $response = null;
        try {
            $response = BaseUserModel::where('email', $user['email'])->count();
            if (!empty($response)) {
                return $response;
            } else {
                return ApiConstant::DATA_NOT_FOUND;
            }
        } catch (\Exception $e) {
            return ApiConstant::EXCEPTION_OCCURED;
        }
    }

    public function isUserAlreadyExist($user)
    {
        $isUserAlreadyExist = $this::where('email', $user['email'])->first();
        $returnData = null;
        if (!empty($isUserAlreadyExist)) {
            $returnData = ApiConstant::EMAIL_ALREADY_EXIST;
        }
        return $returnData;
    }

    public function saveUserDetails($user)
    {
        $returnData = null;
        $this->email = $user['email'];
        $this->name = $user['name'];
        $this->phone_no = $user['phone_no'];
        $this->status = $user['status'];
        $this->dob = $user['dob'] ?? '';
        $this->subjects = $user['subjects'] ?? '';
        $this->password = bcrypt($user['password']);
        $this->image = 'default.png';
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function deleteVendorTerms($data)
    {

        $response = null;
        $response = $this::where('id_user',$data['id'])
//            ->join('tags','vendors.terms_id','tags.id_terms')
            ->where('terms_tag', $data['id_tag'])->first();

        if ($response) {
            $response->terms = null;
            $response->terms_id = null;
            $response->terms_tag = null;
            $response->save();
        }

//            ->update([
//                'terms' => null,
//                'terms_id' => null,
//            ]);
        return $response;
    }

}