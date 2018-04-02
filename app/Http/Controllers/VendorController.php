<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 5/12/17
 * Time: 8:40 PM
 */

namespace App\Http\Controllers;

use App\BaseModels\BasePurchaseOrderModel;
use App\Models\CandidateTagModel;
use App\Models\PurchaseOrderModel;
use App\Models\TagModel;
use App\Models\TermModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\VendorItemsModel;
use App\Models\VendorModel;
use App\Helpers\ApiConstant;
use App\Models\VendorTagModel;
//use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Http\Request;
use App\Helpers\AppUtility;
use Illuminate\Support\Facades\DB;
use Imagick;
use DateTime;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;


class VendorController extends AppController
{

    // API is used to save tags
    public function loadMpdf()
    {
        require '../../../vendor/autoload.php';
    }

    public function addVendorTags(Request $request)
    {

        $response = null;
        $message = null;
        $userData = $request->input();
        $user['tag'] = $userData['tag'];
        $error = null;
        try {
            if (!empty($user)) {
                $trimTag = trim($user['tag']);
                $tagModelObj = new TagModel();
                $tagResponse = $tagModelObj->isTagExistAndSave($trimTag);
                if ($tagResponse) {
                    $response = array("message" => ApiConstant::TAG_ADDED_SUCCESSFULLY);
                } else {
                    $error = ApiConstant::TAG_EXIST;
                    $response = array("message" => ApiConstant::TAG_ALREADY_EXIST);
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to delete tags
    public function deleteVendorTags(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $userData = $request->input();
        try {
            DB::beginTransaction();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                foreach ($userData['tags'] as $tag) {
                    $tagObj = new TagModel();
                    $response = $tagObj->deleteTag($tag);
                    if ($response == ApiConstant::ID_NOT_FOUND) {
                        $error = ApiConstant::ID_NOT_FOUND;
                    }
                }

            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
            DB::rollback();
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to add vendor to users table and vendors table.
    public function addVendor(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $userModelObj = new UserModel();
        $message = null;
        $email = AppUtility::check_email_address($userData['email']);
        if ($email) {
            $user['vendor_name'] = $userData['vendor_name'];
            $user['company_name'] = $userData['company_name'];
            $user['email'] = $userData['email'];
            $user['alternate_email'] = $userData['alternate_email'];
            $user['address_one'] = $userData['address_one'];
            $user['address_two'] = $userData['address_two'];
            $user['contact_number'] = $userData['contact_number'];
            $user['alternate_contact_number'] = $userData['alternate_contact_number'];
            $user['city'] = $userData['city'];
            $user['state'] = $userData['state'];
            $user['pin_code'] = $userData['pin_code'];
            $user['tags'] = $userData['tags'];
            $user['role'] = 3;

        } else {
            $error = ApiConstant::EMAIL_NOT_VALID;
        }
        try {
            if (!empty($user)) {
                $isUserAlreadyExist = $userModelObj->isUserAlreadyExist($user);
                if ($isUserAlreadyExist == ApiConstant::EMAIL_ALREADY_EXIST) {
                    $error = $isUserAlreadyExist;
                } else {
                    $userDetails = $userModelObj->saveUserDetails($user);
                    foreach ($user['tags'] as $tag) {
                        $trimTag = trim($tag);
                        $tagModelObj = new TagModel();
                        $tagResponse = $tagModelObj->isTagIdExist($trimTag);
                        if (!$tagResponse) {
                            $response = array("message" => ApiConstant::TAG_NOT_EXIST);
                        } else {
                            $user['id_tag'] = $tagResponse['id'];
                            $user['id'] = $userDetails['id'];
                            $candidateTagModelObj = new VendorTagModel();
                            $candidateTagResponse = $candidateTagModelObj->saveCandidateTag($user);
                            if ($candidateTagResponse) {
                                $response = array("message" => ApiConstant::VENDOR_UPDATED_SUCCESSFULLY);
                            }
                        }
                    }
                    if ($userDetails == ApiConstant::DATA_NOT_SAVED) {
                        $error = $userDetails;
                    } else {

                        $userRoleModelObj = new UserRoleModel();
                        $userRole = $userRoleModelObj->insertUserRoleId($userDetails['id'], $user['role']);
                        if ($userRole) {
                            $user['id'] = $userDetails['id'];
                            $userModelObj = new VendorModel();
                            $userDetails = $userModelObj->saveVendor($user);
                            if ($userDetails) {
                                $response['vendor_id'] = $user['id'];
                            } else {
                                $error = ApiConstant::APPLY_FAILED;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to add vendor Items to vendors_item table.
    public function addVendorItems(Request $request)
    {
        $user = null;
        $response = null;
        $message = null;
        $userData = $request->input();
        $user['id_item'] = $userData['item_id'];
        $user['vendor_id'] = $userData['id'];
        $user['price'] = $userData['price'];
        $error = null;
        try {
            if (!empty($user)) {
                $tagModelObj = new VendorItemsModel();
                $tagResponse = $tagModelObj->saveVendorItem($user);
                if ($tagResponse == ApiConstant::ITEM_EXIST) {
                    $response = array("message" => ApiConstant::VENDOR_ITEM_ALREADY_EXIST);
                    $error = ApiConstant::ITEM_EXIST;
                } else {
                    $response = $tagResponse;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to view vendor Items from vendors_item table.
    public function viewVendorItems(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new VendorItemsModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['id'] = $inputData['id'];
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $userModelObj->viewVendorItems($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to view all vendor related to item from vendors Item table.
    public function viewAllVendorsForItem(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new VendorItemsModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['id'] = $inputData['id'];
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $userModelObj->viewItemVendors($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to delete vendor Items from vendors_item table.
    public function deleteVendorItems(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $userData = $request->input();
        $user['id_item'] = $userData['id'];
        $user['vendor_id'] = $userData['vendor_id'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $VendorItemModelObj = new VendorItemsModel();
                $response = $VendorItemModelObj->deleteVendorItem($user);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to view vendor names from vendors_item table.
    public function viewVendorNames(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new VendorModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $userModelObj->viewVendorNames();
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }


    public function viewVendorNamesByItemId(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $itemData = $request->input();
        $user['id_item'] = $itemData['id_item'];
        try {
            $userModelObj = new VendorModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $userModelObj->viewVendorByItemId($user);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addPurchaseOrder(Request $request)
    {
        $response = null;
        $message = null;
        $data = null;
        $purchaseStatus = $request->input();
        $user['status'] = $purchaseStatus['status'];
        $user['vendor_id'] = $purchaseStatus['vendor_id'];
        $user['total_amount'] = isset($purchaseStatus['total_amount']) ?? 0;
        $user['terms_data'] = isset($purchaseStatus['terms_data']) ?? '';
        $error = null;
        try {
            if (!empty($user)) {
                DB::beginTransaction();
                $purchaseModelObj = new PurchaseOrderModel();
                $statusResponse = $purchaseModelObj->checkPurchaseStatus($user);
                if (!$statusResponse) {
                    $userModelObj = new VendorModel();
                    $statusResponse = $userModelObj->isTermsAssignedToVendor($user);
                    if ($statusResponse->terms_tag) {
                        $tagResponse = $purchaseModelObj->savePurchaseOrder($user);
                        $purchase_id = $tagResponse->id;
                        if ($tagResponse) {
                            $message = array("message" => ApiConstant::PURCHASE_ADDED_SUCCESSFULLY);
                            $response['purchase_id'] = $purchase_id;
                            DB::commit();
                        } else {
                            $error = ApiConstant::ADD_PURCHASE_FAIL;
                        }
                    } else {
                        $error = ApiConstant::NO_TERMS_ASSIGNED;
                    }
                } else {
                    $error = ApiConstant::ADD_PURCHASE_FAIL;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
            DB::rollback();
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function editPurchaseOrder(Request $request)
    {
        $response = null;
        $message = null;
        $purchaseStatus = $request->input();
        $user['status'] = $purchaseStatus['status'];
        $user['vendor_id'] = $purchaseStatus['vendor_id'];
        $user['purchase_id'] = $purchaseStatus['purchase_id'];
        $purchase_data = $purchaseStatus['purchase_data'];
        $delete_data = isset($purchaseStatus['delete_data']) ? $purchaseStatus['delete_data'] : '';
        $user['total_amount'] = $purchaseStatus['total_amount'];
        $user['terms_data'] = $purchaseStatus['terms_data'];
        $error = null;
        try {
            if (!empty($user)) {
                DB::beginTransaction();
                $purchaseModelObj = new PurchaseOrderModel();
                $tagResponse = $purchaseModelObj->updatePurchaseOrder($user);
                if ($tagResponse) {
                    if($delete_data) {
                        foreach ($delete_data as $data) {
                            $tagResponse = $purchaseModelObj->deletePurchaseOrderDetails($data);
                            if (!$tagResponse) {
                                $error = ApiConstant::ADD_PURCHASE_FAIL;
                            }
                        }
                    }
                    foreach ($purchase_data as $data) {
                        $data['purchase_id'] = $user['purchase_id'];
                        $tagResponse = $purchaseModelObj->updatePurchaseOrderDetails($data);
                        if ($tagResponse) {
                            $response = array("message" => ApiConstant::PURCHASE_ADDED_SUCCESSFULLY);
                            DB::commit();
                        } else {
                            $error = ApiConstant::ADD_PURCHASE_FAIL;
                        }
                    }
                } else {
                    $error = ApiConstant::ADD_PURCHASE_FAIL;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
            DB::rollback();
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function buyItemsFromVendor(Request $request)
    {
        $response = null;
        $updateResponse = null;
        $message = null;
        $itemsList = $request->input();
        $user['total_amount'] = '';
        $user['terms_data'] = '';
        $user['items_list'] = $itemsList['id_item_array'];
        $user['vendor_id'] = $itemsList['vendor_id'];
        $user['total_amount'] = isset($itemsList['total_amount']) ? $itemsList['total_amount'] : 0;
        $user['terms_data'] = isset($itemsList['terms_data']) ? $itemsList['terms_data'] : '';
        $error = null;
        try {
            if (!empty($user)) {
                $purchaseModelObj = new PurchaseOrderModel();
                $purchaseBaseModelObj = new BasePurchaseOrderModel();
                $response = $purchaseModelObj->checkPurchaseStatus($user);
                if (!$response) {
                    $user['status'] = 'Draft';
                    $response = $purchaseModelObj->savePurchaseOrder($user);
                } else {
                    $user['purchase_id'] = $response->id;
                    $result = $purchaseBaseModelObj::where('id', $user['purchase_id'])->update([
                        'terms' => $user['terms_data'],
                    ]);
                    if (!$result) {
                        return $error = ApiConstant::ADD_PURCHASE_FAIL;
                    }
                }
                $user['total_amount'] = $response->total_amount;
                $user['terms_data'] = $response->terms;
                $user['purchase_id'] = $response->id;
                foreach ($user['items_list'] as $data) {
                    $data['purchase_id'] = $user['purchase_id'];
                    $check = $purchaseModelObj->checkItemEntry($data);
                    if (empty($check)) {
                        $tagResponse = $purchaseModelObj->updatePurchaseOrderDetails($data);
                        if ($tagResponse) {
                            $response['purchase_id'] = $data['purchase_id'];
                            $response['message'] = array("message" => ApiConstant::PURCHASE_ADDED_SUCCESSFULLY);
                        } else {
                            $error = ApiConstant::ADD_PURCHASE_FAIL;
                        }
                    }
                }
            } else {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function downloadPurchaseOrder(Request $request)
    {
        $response = null;
        $message = null;
        $error = null;
        $purchaseStatus = $request->input();
        $user['vendor_id'] = $purchaseStatus['vendor_id'];
        $user['purchase_id'] = $purchaseStatus['purchase_id'];
        try {
            if (!empty($user)) {
                $purchaseModelObj = new PurchaseOrderModel();
                $response = $purchaseModelObj->getPurchaseOrder($user);
                $pdfName = $response->pdf_name;
                $file = public_path('purchase_orders/' . $pdfName . '.pdf');
                if ($file) {
                    $message = array("message" => ApiConstant::PURCHASE_DOWNLOAD_SUCCESSFULLY);
                } else {
                    $error = ApiConstant::EMPTY_VALUE;
                }
//                dd($file);
//                $headers = array(
//                    'Content-Type: application/pdf',
//                );
//                return Response()->download($file, $pdfName.'.pdf', $headers);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);

    }

    public function sendPurchaseOrderBackend(Request $request)
    {
        $response = null;
        $now = new DateTime();
        $TodayDate = $now->getTimestamp();
        $data = $request->input();
        $fileName = 'pdf' . $TodayDate . '.pdf';
        $pdfFileName = 'pdf' . $TodayDate;
        $message = null;
        $error = null;
        $sendData['vendor_data'] = $data['vendor_data'];
        $hash = 1;
        $sendData['vendor_id'] = $data['vendor_id'];
        $sendData['purchase_data'] = $data['purchase_data'];
        $sendData['terms'] = $data['terms_data'];
        $sendData['total_amount'] = $data['total_amount'];
        $sendData['purchase_id'] = $data['purchase_id'];
        $sendData['status'] = $data['status'];
        $subject = $data['subject'];
        $body = $data['template'];
        $emailList = $data['email'];
        $poTemplate = null;
        $subject = 'PO '.$sendData['purchase_id'].' '.ucwords(strtolower($sendData['vendor_data'][0]['name']));
        $poTemplate = 'Hi '. ucwords(strtolower($sendData['vendor_data'][0]['name']));
        $poTemplate = $poTemplate.'<p>Greeting from Tudip!</p>';
        $poTemplate = $poTemplate.'Please find the attached PO - '.$sendData['purchase_id'].' ,GST certificate copy of Tudip Technologies and the list of documents you need to submit before the start of the service.';
        $docslist = '<div><ul>
	<li>Shop act license.</li>
</ul>

<ul>
	<li>Submit the hard copy of the PO with stamp and sign of authorized signatory from you estimated organization at our location.</li>
</ul>

<ul>
	<li>Vendor company PAN photocopy.</li>
</ul>

<ul>
	<li>Vendor company GST registration certificate copy.</li>
</ul>

<ul>
	<li>License for services/Authorization letter.</li>
</ul>

<ul>
	<li>Contact person address and photo proof.
	<ul>
		<li>Adhar Card.</li>
		<li>Passport.</li>
		<li>Driving license.</li>
		<li>Voter Id Card.</li>
	</ul>
	</li>
</ul></div>';
        $poTemplate = $poTemplate.$docslist;
        $poTemplate = $poTemplate.'Please feel free to get in touch with us if you have any questions.';
        $testMail = 'admin@tudip.com';
        $basePath = public_path('purchase_orders/');
        $html = null;
        $html = '<div style="background-color: white">
        <div style="padding: 10px 50px  10px 50px "><a class="al-logo clearfix"><img style="width: 120px; height: 52.5px;"
                                                        src="data:image/png;base64,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"></a>
                        <div>
                            <h3 style="margin-top: 0; color: #3b5998; font-weight: bold">Integrity, Innovation, Serenity</h3>
                        </div><div>
                        	<p><img alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAO0AAAAECAYAAABiBrjFAAAACXBIWXMAAA7IAAAN8wFwqNANAAAAMElEQVR4nGORdsz/zzAKRsEoGDKAZaAdMApGwSggDYxm2lEwCoYYGM20o2AUDDEAACYTAdvLTgm6AAAAAElFTkSuQmCCAA==" style="height:6px; width:50%" /><img alt="" src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBxdWFsaXR5ID0gOTkK/9sAQwABAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQECAgEBAgEBAQICAgICAgICAgECAgICAgICAgIC/9sAQwEBAQEBAQEBAQEBAgEBAQICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC/8AAEQgBXgO2AwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A/F+iiiv8pz/v4CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9k=" style="height:6px; width:50%" /></p>

                        </div>
                        <div style="padding-top: 10px; border-top: 1px solid lightgrey">
                        </div>
                        <table style="font-size: 16px; width: 100%">
                        	<tbody> <tr>
			<td style="font-size: 22px; width: 250px;">Vendor Name:</td>
			<td style="font-size: 22px; width: 300px;">' . $sendData['vendor_data'][0]['name'] . '</td>
			<td style="font-size: 22px; width: 350px;">&nbsp;</td>
			<td style="font-size: 22px; width: 250px;">PO Number:</td>
			<td style="font-size: 22px; width: 100px;">' . $sendData['purchase_id'] . '</td>
		</tr> <tr>
			<td style="font-size: 22px; width: 250px;">Company Name:</td>
			<td style="font-size: 22px; width: 300px;">' . $sendData['vendor_data'][0]['company_name'] . '</td>
			<td style="width: 350px;">&nbsp;</td>
			<td style="width: 250px;">&nbsp;</td>
			<td style="width: 100px;">&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 22px; width: 250px;">Contact:</td>
			<td style="font-size: 22px; width: 250px;">' . $sendData['vendor_data'][0]['contact_number'] . '</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 22px; width: 250px;">Email:</td>
			<td style="font-size: 22px; width: 250px;">' . $sendData['vendor_data'][0]['email'] . '</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 22px; width: 250px;">Address:</td>
			<td style="font-size: 22px; width: 250px;">' . (($sendData['vendor_data'][0]['address_line_one'].$sendData['vendor_data'][0]['address_line_two']) ? $sendData['vendor_data'][0]['address_line_one'].'   '.$sendData['vendor_data'][0]['address_line_two'] : 'NA') . '</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 22px; width: 250px;">GST Number:</td>
			<td style="font-size: 22px; width: 250px;">' . (($sendData['vendor_data'][0]['gst_number']) ? ($sendData['vendor_data'][0]['gst_number']) : 'NA') . '</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
                        </tbody>
                        	</table>

                        
                         <div style="margin-top: 30px; padding: 10px; border-top: 1px solid lightgrey"></div>';
        $html = $html . '<div style="position: relative">
                        <table  border="1" cellpadding="0" cellspacing="0" style="width:100%;">
	<thead>
		<tr>
			<th style="padding: 5px 5px 5px 25px; text-align: left; width: 150px; font-size: 10px;" scope="col">Name</th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">Price Per Item (₹)</th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">Quantity</th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">Total (₹)</th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">GST (%)</th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">GST Price</th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">Total Amount (₹)</th>
		</tr>
	</thead>';
        $html = $html . '<tfoot>
    <tr>
			<th scope="col"></th>
			<th scope="col"></th>
			<th scope="col"></th>
			<th scope="col"></th>
			<th scope="col"></th>
			<th style="padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">Total</th>
			<th style="text-align: left; padding: 5px 5px 5px 10px; font-size: 10px;" scope="col">' . $sendData['total_amount'] . '</th>
		</tr>
  </tfoot> ';

        $html = $html . '<tbody>';

        foreach ($sendData['purchase_data'] as $purchase) {
            $html = $html . '<tr><td style="font-size: 10px; padding: 5px 5px 5px 25px; ">';
            $html = $html . $purchase['item_name'];
            $html = $html . "</td>";
            $html = $html . '<td style="font-size: 10px; padding: 5px 5px 5px 10px; ">';
            $html = $html . $purchase['price'];
            $html = $html . "</td>";
            $html = $html . '<td style="font-size: 10px; padding: 5px 5px 5px 10px; ">';
            $html = $html . $purchase['quantity'];
            $html = $html . "</td>";
            $html = $html . '<td style="font-size: 10px; padding: 5px 5px 5px 10px; ">';
            $html = $html . $purchase['total'];
            $html = $html . "</td>";
            $html = $html . '<td style="font-size: 10px; padding: 5px 5px 5px 10px; ">';
            $html = $html . $purchase['gst'];
            $html = $html . "</td>";
            $html = $html . '<td style="font-size: 10px; padding: 5px 5px 5px 10px; ">';
            $html = $html . $purchase['gst_price'];
            $html = $html . "</td>";
            $html = $html . '<td style="text-align: left; font-size: 10px; padding: 5px 5px 5px 10px; ">';
            $html = $html . $purchase['total_amount'];
            $html = $html . "</td></tr>";
        }

        $html = $html . '</tbody></table></div>';

        $html = $html . '<div style=\'margin-top: 30px; padding: 10px; border-top: 1px solid lightgrey\'>
                        </div>
                   <div>
                    	<div>
                            <span style="margin: 5px; font-size: 10px; padding:0; font-weight: bold">Terms and Conditions</span>
                        </div>';

        $html = $html . '<div style="font-size: 10px;">' . $sendData['terms'] . '</div>';

        $html = $html . '<div style=\'margin-top: 30px; padding: 10px; border-top: 1px solid lightgrey\'>
                        </div>
                        <div>
                        	<table style="width: 100%">
                        	<tbody>
        <tr>
			<td style="font-size: 25px; width: 400px;">Company Name:</td>
			<td style=" width: 30px;">&nbsp;</td>
			<td style="font-size: 25px; width: 600px;">Tudip Technologies Pvt Ltd</td>
			<td style=" width: 650px;">&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 25px; width: 400px;">Complete Postal Address with Pin:</td>
			<td style=" width: 30px;">&nbsp;</td>
			<td style="font-size: 25px; width: 600px;">S. No. 241/3/A, Datta Mandir Road, Wakad, Pune 411057 </td>
			<td style="font-size: 25px; width: 650px;">&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 25px; width: 400px;">Company PAN Number:</td>
			<td style=" width: 30px;">&nbsp;</td>
			<td style="font-size: 25px; width: 600px;">AADCT4061E</td>
			<td style="font-size: 25px; width: 650px;">&nbsp;</td>
		</tr>
		<tr>
			<td style="font-size: 25px; width: 350px;">Company GST Number:</td>
			<td style=" width: 30px;">&nbsp;</td>
			<td style="font-size: 25px; width: 600px;">27AADCT4061E1ZJ</td>
			<td style="font-size: 25px; width: 650px;">&nbsp;</td>
		</tr>
		</tbody>
		</table>
                        </div>
                        <div style=\'margin-top: 20px; padding: 10px; border-top: 1px solid lightgrey\'>
                        </div>
                        <div style="font-size: 10px;">
                        	Thanking You..!!
                        </div>
                    </div>

                    </div>

                    </div> </div>';

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output($basePath . $fileName);
        try {
            if (!empty($data)) {
                $data['pdf_file'] = $pdfFileName;
                $purchaseModelObj = new PurchaseOrderModel();
                $mailResponse = AppUtility::sendEmail($subject, $poTemplate, $emailList, $hash, $pdfFileName . '.pdf');
                if ($mailResponse) {
                    if ($sendData['status'] == 'Test') {
                        $mailResponse = $purchaseModelObj->sendTestPurchaseOrder($data);
                    } else {
                        $mailResponse = $purchaseModelObj->sendPurchaseOrder($data);
                    }

                    if ($mailResponse) {
                        $response = array("message" => ApiConstant::PURCHASE_ADDED_SUCCESSFULLY);
                    } else {
                        $error = ApiConstant::ADD_PURCHASE_FAIL;
                    }
                } else {
                    $error = ApiConstant::ADD_PURCHASE_FAIL;
                }
            } else {
                $error = ApiConstant::ADD_PURCHASE_FAIL;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);

    }

    public function sendPurchaseOrder(Request $request)
    {
        $response = null;
        $message = null;
        $testMail = null;
        $hash = 1;
        $now = new DateTime();
        $TodayDate = $now->getTimestamp();
        $purchaseStatus = $request->input();
        $user['vendor_id'] = $purchaseStatus['vendor_id'];
        $user['purchase_id'] = $purchaseStatus['purchase_id'];
        $user['pdf'] = $purchaseStatus['pdf'];
        $subject = $purchaseStatus['subject'];
        $body = $purchaseStatus['template'];
        $emailList = $purchaseStatus['email'];
        $testMail = 'admin@tudip.com';
        $error = null;
        try {
            if ($user['pdf']) {
                $pdfSaved = null;
                $b64file = $user['pdf'];
                list($type, $b64file) = explode(';', $b64file);
                list(, $b64file) = explode(',', $b64file);
                $data = base64_decode($b64file);
                $pngFileName = 'image' . $TodayDate . '.png';
                $pdfFileName = 'pdf' . $TodayDate;
                file_put_contents($pngFileName, $data);
                $image = new Imagick($pngFileName);
                $image->setImageFormat('pdf');
                $pdfSaved = $image->writeImage(public_path('purchase_orders/' . $pdfFileName . '.pdf'));
                if ($pdfSaved) {
                    if (!empty($user)) {
                        $user['pdf_file'] = $pdfFileName;
                        $purchaseModelObj = new PurchaseOrderModel();
                        $mailResponse = AppUtility::sendEmail($subject, $body, $emailList, $hash, $pdfFileName . '.pdf');
                        if ($mailResponse) {
                            if ($emailList == $testMail) {
                                $mailResponse = $purchaseModelObj->sendTestPurchaseOrder($user);
                            } else {
                                $mailResponse = $purchaseModelObj->sendPurchaseOrder($user);
                            }

                            if ($mailResponse) {
                                $response = array("message" => ApiConstant::PURCHASE_ADDED_SUCCESSFULLY);
                            } else {
                                $error = ApiConstant::ADD_PURCHASE_FAIL;
                            }
                        } else {
                            $error = ApiConstant::ADD_PURCHASE_FAIL;
                        }

//                        if ($user['mail_status'] == 'Test') {
//                            $mailResponse = $purchaseModelObj->sendTestPurchaseOrder($user);
//                        } else {
//                            $mailResponse = $purchaseModelObj->sendPurchaseOrder($user);
//                        }
//                        if ($mailResponse) {
//                            $response = array("message" => ApiConstant::PURCHASE_ADDED_SUCCESSFULLY);
//                        } else {
//                            $error = ApiConstant::ADD_PURCHASE_FAIL;
//                        }
                    } else {
                        $error = ApiConstant::ADD_PURCHASE_FAIL;
                    }
                } else {
                    $error = ApiConstant::ADD_PURCHASE_FAIL;
                }
            }


        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function viewPurchaseOrders(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $response = null;
        $error = null;
        try {
            $purchaseModelObj = new PurchaseOrderModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['vendor_id'] = $inputData['vendor_id'];
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $purchaseModelObj->viewPurchaseOrder($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deletePurchaseOrder(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $userData = $request->input();
        $user['purchase_id'] = $userData['purchase_id'];
        $user['vendor_id'] = $userData['vendor_id'];

        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $purchaseModelObj = new PurchaseOrderModel();
                $response = $purchaseModelObj->deletePurchaseOrderById($user);
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewPurchaseOrderById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $response = null;
        $error = null;
        try {
            $purchaseModelObj = new PurchaseOrderModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['purchase_id'] = $inputData['purchase_id'];
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $purchaseModelObj->viewPurchaseOrderById($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewPurchaseDetailsByItemId(Request $request) {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $response = null;
        $error = null;
        try {
            $purchaseModelObj = new PurchaseOrderModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['purchase_id'] = $inputData['purchase_id'];
            $data['id_item'] = $inputData['id_item'];
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $purchaseModelObj->viewPurchaseDetailsByItemId($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function addTermsConditions(Request $request)
    {
        $user = null;
        $response = null;
        $message = null;
        $tagResponse = null;
        $userData = $request->input();
        $user['terms'] = $userData['terms'];
        $user['categories'] = $userData['categories'];
//        print_r($user);die;
        $error = null;
        try {
            DB::beginTransaction();
            if (!empty($user)) {
                $termModelObj = new TermModel();
                $termResponse = $termModelObj->saveTerms($user['terms']);
//                print_r($tagResponse);die;
                if ($termResponse == ApiConstant::ITEM_EXIST) {
                    $response = array("message" => ApiConstant::VENDOR_ITEM_ALREADY_EXIST);
                    $error = ApiConstant::ITEM_EXIST;
                } else {
                    //$termModelObj = new TagModel();
//                    DB::commit();
                    foreach ($user['categories'] as $tag) {
                        $tagModelObj = new TagModel();
                        $tagResponse = $tagModelObj->saveTermsDetails($tag, $termResponse->id);
                    }
                    if ($tagResponse) {
                        DB::commit();
                        $response = $tagResponse;
                    } else {
                        $error = ApiConstant::ADD_TERMS_FAIL;
                    }

                }

            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
            DB::rollback();
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function editTermsConditions(Request $request)
    {
        $user = null;
        $response = null;
        $message = null;
        $tagResponse = null;
        $userData = $request->input();
        $user['terms'] = $userData['terms'];
        $user['term_id'] = $userData['term_id'];
        $user['categories'] = $userData['categories'];
        $user['delete_categories'] = $userData['delete_categories'] ?? null;
//        print_r($user);die;
        $error = null;
        try {
            DB::beginTransaction();
            if (!empty($user)) {
                $termModelObj = new TermModel();
                $termResponse = $termModelObj->updateTerms($user);
//                print_r($tagResponse);die;
                if ($termResponse == ApiConstant::ITEM_EXIST) {
                    $response = array("message" => ApiConstant::VENDOR_ITEM_ALREADY_EXIST);
                    $error = ApiConstant::ITEM_EXIST;
                } else {
                    //$termModelObj = new TagModel();
//                    DB::commit();
                    if ($user['delete_categories']) {
                        foreach ($user['delete_categories'] as $deleteTag) {
                            $tagModelObj = new TagModel();
                            $tagResponse = $tagModelObj->updateTermsEntry($deleteTag);
                        }
                    }
                    foreach ($user['categories'] as $tag) {
                        $tagModelObj = new TagModel();
                        $tagResponse = $tagModelObj->saveTermsDetails($tag, $user['term_id']);
                    }
                    if ($tagResponse) {
                        DB::commit();
                        $response = $tagResponse;
                    } else {
                        $error = ApiConstant::ADD_TERMS_FAIL;
                    }

                }

            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
            DB::rollback();
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function viewTermsAndTagDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $inputData = $request->input();
        $data = null;
        $response = null;
        $error = null;
        $data['page_number'] = isset($inputData['page_number']) ? ($inputData['page_number'] - 1) : 0;
        $data['limit'] = isset($inputData['limit']) ? $inputData['limit'] : 10;
        try {
            $userModelObj = new TagModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $userModelObj->viewTerms($data);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteTermsAndTagDetails(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $userData = $request->input();
        $user['id_term'] = $userData['id_term'];
        $user['id_tag'] = $userData['id_tag'];
        try {
            DB::beginTransaction();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2) {
                $userModelObj = new TagModel();
                foreach ($user['id_tag'] as $tag) {
                    $response = $userModelObj->deleteTermsEntry($user['id_term'], $tag);
                }
                if ($response == ApiConstant::ID_NOT_FOUND) {
                    $error = ApiConstant::ID_NOT_FOUND;
                }
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
            DB::rollback();
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewTermsAndTagDetailsById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new VendorItemsModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            $inputData = $request->input();
            $data['id_term'] = $inputData['id_term'];
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $tagObj = new TagModel();
                $termObj = new TermModel();
                $response = $termObj->isTermExist($data['id_term']);
                if ($response != ApiConstant::TAG_NOT_EXIST) {
                    $response[0] = $tagObj->viewTagsInTermsById($data['id_term']);
                    $response[1] = $termObj->viewTermsDataById($data['id_term']);
                } else {
                    $response = ApiConstant::TAG_NOT_EXIST;
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function viewTermsDataByTagId(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $inputData = $request->input();
            $data['id_tag'] = $inputData['id_tag'];
            $userModelObj = new TagModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $response = $userModelObj->viewTermsByTag($data['id_tag']);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

}