<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 5/12/17
 * Time: 8:40 PM
 */

namespace App\Http\Controllers;
use App\BaseModels\BaseItemModel;
use App\BaseModels\BaseItemTagModel;
use App\BaseModels\BaseVendorItemsModel;
use App\Models\ItemLocationModel;
use App\Models\ItemModel;
use App\Models\ItemTagModel;
use App\Models\TagModel;
use App\Models\UserRoleModel;
use App\Helpers\ApiConstant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends AppController
{

    // API is used to add tags related to items.
    public function addItemTags(Request $request)
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
                $tagResponse = $tagModelObj->isTagExist($trimTag);
                if($tagResponse){
                    $response = array("message" => ApiConstant::TAG_ADDED_SUCCESSFULLY);
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to delete items.
    public function deleteItem(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $deleteCandidate = null;
        $message = null;
        try {
            $data = $request->input();
            $id = $data['id'];
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $candidateModelObj = new ItemModel();
                    $itemDependancy = $candidateModelObj->checkItemDependancy($id);
                    if (!empty($itemDependancy)) {
                        $error = $error = ApiConstant::ITEM_Dependancy;
                        $message = array("message" => 'Item used in purchase Order');
                    } else {
                        $vendorItemsObject = new BaseVendorItemsModel();
                        $response = $vendorItemsObject::where('id_item',$id)->first();
                        if (empty($response)) {
                            $itemTagsObject = new BaseItemTagModel();
                            $response = $itemTagsObject::where('id_item',$id)->delete();
                            if ($response) {
                                $deleteCandidate = $candidateModelObj->deleteItem($id);
                            }

                            if ($deleteCandidate) {
                                $response = array("message" => ApiConstant::CANDIDATE_DELETED);
                            }
                        } else {
                            $error = $error = ApiConstant::ITEM_Dependancy_VENDOR_ITEMS;
                            $message = array("message" => 'Item used in Item Vendor');
                        }

                    }

            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $message, $error);
    }

    public function deleteItemQuantity(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $deleteData['item_id'] = $data['item_id'];
            $deleteData['log_id'] = $data['log_id'];
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $candidateModelObj = new ItemModel();
                    $deleteCandidate = $candidateModelObj->deleteItemQuantity($deleteData);
                    if ($deleteCandidate) {
                            $response = array("message" => ApiConstant::ITEM_QUANTITY_DELETED);
                    }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    public function deleteItemUsed(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $data = $request->input();
            $deleteData['item_id'] = $data['item_id'];
            $deleteData['log_id'] = $data['log_id'];
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $candidateModelObj = new ItemModel();
                    $deleteCandidate = $candidateModelObj->deleteItemUsed($deleteData);
                    if ($deleteCandidate) {
                            $response = array("message" => ApiConstant::ITEM_QUANTITY_DELETED);
                    }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to view items from Item table.
    public function viewItems(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        try {
            $userModelObj = new ItemModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $response = $userModelObj->viewItems();
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to view items by serach string from Item table.
    public function viewItemsBySearch(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $user = null;
        $userData = $request->input();
        $user['search_input'] = $userData['search_input'];
        try {
            $userModelObj = new ItemModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $response = $userModelObj->viewItemsBySearch($user);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to view items names from Item table.
    public function viewItemNames(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $itemData = $request->input();
        $user['vendor_id'] = $itemData['vendor_id'];
        try {
            $userModelObj = new ItemModel();
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                    $response = $userModelObj->viewItemNames($user);
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error);
    }

    // API is used to add items to Item table.
    public function addItem(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
            $user['item_name'] = $userData['item_name'];
            $user['gst'] = $userData['gst'];
            $user['location'] = $userData['location'];
            $user['description'] = $userData['description'];
            $user['categories'] = $userData['categories'];
        try {

            $userModelObj = new ItemModel();
            $itemName = $user;
            $itemExist = $userModelObj->isItemExist($itemName);
            if($itemExist){
                $error = ApiConstant::ITEM_EXIST;
                $response = array("message" => 'Item Already Exist');
            }else{
                $userDetails = $userModelObj->saveItem($user);
                $user['id_item'] = $userDetails['id'];
                foreach ($user['categories'] as $tag)
                {
                    $trimTag = trim($tag);
                    $tagModelObj = new TagModel();
                    $tagResponse = $tagModelObj->isTagExist($trimTag);
                    if($tagResponse['message'] == ApiConstant::TAG_NOT_EXIST ){
                        $error = ApiConstant::TAG_NOT_EXIST;
                        $response = array("message" => ApiConstant::TAG_NOT_EXIST);
                    } else {
                        $user['id_tag']= $tagResponse['id'];
                        $candidateTagModelObj  = new ItemTagModel();
                        $candidateTagResponse = $candidateTagModelObj->saveItemTag($user);
                        if($candidateTagResponse){
                            $response = $tagResponse;
                        }
                    }
                }
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }


    public function addItemQuantity(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
            $user['id_item'] = $userData['id_item'];
            $user['quantity'] = $userData['quantity'];
            $user['purchase_id'] = isset($userData['purchase_id']) ? $userData['purchase_id'] : 0 ;
            $user['vendor_id'] = $userData['vendor_id'];
            $user['date'] = $userData['date'];
            $user['price'] = $userData['price'];
        try {
            $itemModelObj = new ItemModel();
            $response = $itemModelObj->addItemQuantity($user);
            if (!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function addUsedItemQuantity(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
            $user['id_item'] = $userData['id_item'];
            $user['quantity'] = $userData['quantity'];
            $user['date'] = $userData['date'];
        try {
            $itemModelObj = new ItemModel();
            $response = $itemModelObj->addUsedItemQuantity($user);
            if ($response == ApiConstant::ITEM_USED_GREATER) {
                $error = ApiConstant::ITEM_USED_GREATER;
                $message = array("message" => "Items Used is greater than Item's Quantity");
            } else if(!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getItemQuantity(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
            $user['id_item'] = $userData['id_item'];
        try {
            $itemModelObj = new ItemModel();
            $response = $itemModelObj->getItemQuantityById($user);
            if (!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function addItemLocation(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
        $user['location_name'] = $userData['location_name'];
        $user['description'] = $userData['description'];
        try {
            $itemModelObj = new ItemLocationModel();
            $response = $itemModelObj->addItemLocation($user);
            if ($response) {
                $message = array("message" => "Item's location added successfully");
            } else if(!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function editItemLocation(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
        $user['id'] = $userData['id'];
        $user['location_name'] = $userData['location_name'];
        $user['description'] = $userData['description'];
        try {
            $itemModelObj = new ItemLocationModel();
            $response = $itemModelObj->editItemLocation($user);
            if ($response) {
                $message = array("message" => "Item's location edited successfully");
            } else if(!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getItemLocationById(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
        $user['id'] = $userData['id'];
        try {
            $itemModelObj = new ItemLocationModel();
            $response = $itemModelObj->getItemLocationById($user);
            if(!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function deleteItemLocation(Request $request)
    {
        DB::beginTransaction();
        $response = null;
        $error = null;
        $id_item = null;
        $userData = $request->input();
        $message = null;
        $user['id'] = $userData['id'];
        try {
            $itemBaseObj = new BaseItemModel();
            $itemModelObj = new ItemLocationModel();
            $response = $itemModelObj->deleteItemLocation($user);
            if ($response) {
                $response = $itemBaseObj::where('items.location_id',$user['id'])->first();
                if($response) {
                    $id_item = $response->id;
                    $response = $itemBaseObj::where('items.id', $id_item)->update([
                        'location_id' => null
                    ]);
                    if (!$response) {
                        $error = ApiConstant::EXCEPTION_OCCURED;
                    } else {
                        DB::commit();
                        $response = array("message" => 'Item location deleted successfully');
                    }
                } else {
                    DB::commit();
                    $response = array("message" => 'Item location deleted successfully');
                }
            }
            else {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getAllItemLocations(Request $request)
    {
        $response = null;
        $error = null;
        $message = null;
        try {
            $itemModelObj = new ItemLocationModel();
            $response = $itemModelObj->viewAllItemLocations();
            if(!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getItemLocations(Request $request)
    {
        $response = null;
        $error = null;
        $message = null;
        try {
            $itemModelObj = new ItemLocationModel();
            $response = $itemModelObj->viewItemLocations();
            if(!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getItemsReceived(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
            $user['vendor_id'] = $userData['vendor_id'];
            $user['purchase_id'] = $userData['purchase_id'];
        try {
            $itemModelObj = new ItemModel();
            $response = $itemModelObj->getItemReceivedDetails($user);
            if (!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    public function getUsedItemQuantity(Request $request)
    {
        $response = null;
        $error = null;
        $userData = $request->input();
        $message = null;
            $user['id_item'] = $userData['id_item'];
        try {
            $itemModelObj = new ItemModel();
            $response = $itemModelObj->getUsedItemQuantityById($user);
            if (!$response) {
                $error = ApiConstant::EXCEPTION_OCCURED;
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($response, $error, $message);
    }

    // API is used to edit items to Item table.
    public function editItem(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $UpdateData = array();
        $data = $request->input();
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                if ($data['id']) {
                    $UpdateData['id_item'] = $data['id'];
                    $UpdateData['gst'] = $data['gst'];
                    $UpdateData['location'] = $data['location'];
                    $UpdateData['item_name'] = $data['item_name'];
                    $UpdateData['description'] = $data['description'];
                    $UpdateData['categories'] = $data['categories'];
                    $UpdateData['delete_categories'] = $data['delete_categories'];
                    $userModelObj = new ItemModel();
                    $itemName = $UpdateData;
                    $itemExist = $userModelObj->isItemExist($itemName);
                    if($itemExist){
                        $error = ApiConstant::ITEM_EXIST;
                        $response = array("message" => 'Item Already Exist');
                    }else{
                        $result = $userModelObj->updateItemDetails($UpdateData);
                        if ($result) {
                            if(!empty($UpdateData['delete_categories']))
                            {
                                foreach ($UpdateData['delete_categories'] as $deleteTag)
                                {
                                    $UpdateData['id_tag'] = $deleteTag;
                                    $candidateTagModelObj  = new ItemTagModel();
                                    $candidateTagResponse = $candidateTagModelObj->deleteItemTag($UpdateData);
                                    if(!$candidateTagResponse){
                                        $error = ApiConstant::ERROR_EMAIL_UPDATE;
                                    }
                                }
                            }
                            foreach ($UpdateData['categories'] as $tag)
                            {
                                $tagModelObj = new TagModel();
                                $tagResponse = $tagModelObj->isTagIdExist($tag);
                                if(!$tagResponse){
                                    $response = array("message" => ApiConstant::TAG_NOT_EXIST);
                                } else {
                                    $UpdateData['id_tag']= $tagResponse['id'];
                                    $candidateTagModelObj  = new ItemTagModel();
                                    $candidateTagResponse = $candidateTagModelObj->saveItemTag($UpdateData);
                                    if($candidateTagResponse){
                                        $response = array("message" => ApiConstant::VENDOR_UPDATED_SUCCESSFULLY);
                                    }
                                }
                            }
                            $response = array("message" => ApiConstant::ITEM_UPDATE_SUCCESSFULLY);
                        }else{
                            $error = ApiConstant::ERROR_EMAIL_UPDATE;
                        }
                    }

                } else {
                    $error = ApiConstant::ID_NOT_FOUND;
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

    // API is used to view items from item_tags table.
    public function viewItemTags(Request $request)
    {
        $inputData = $request->input();
        $error = null;
        $candidateTagData = null;
        try {
            $candidateTagModelObj = new ItemTagModel();
            $candidateTagData = $candidateTagModelObj->viewItemTags($inputData);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }
        return $this->returnableResponseData($candidateTagData, $error);
    }

    // API is used to get item by Id.
    public function getItemById(Request $request)
    {
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $id = $request->id;
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $userModelObj = new ItemModel();
                $response = $userModelObj->viewItemById($id);
                if(!$response){
                    $error = ApiConstant::ITEM_UPDATE_FAILED;
                    $response = array("message" => ApiConstant::ERROR_ITEM_UPDATE);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getPurchaseOrdersByItemById(Request $request){
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $data = $request->input();
        $id = $data['id_item'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $userModelObj = new ItemModel();
                $response = $userModelObj->viewPurchaseOrdersByItemId($id);
                if(!$response){
                    $error = ApiConstant::ITEM_UPDATE_FAILED;
                    $response = array("message" => ApiConstant::ERROR_ITEM_UPDATE);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }

    public function getVendorsByItemCategories(Request $request){
        $authenticatedUser = $request->user->id_user;
        $response = null;
        $error = null;
        $data = $request->input();
        $id = $data['id_item'];
        try {
            $userRoleModelObj = new UserRoleModel();
            $userRole = $userRoleModelObj->getUserRole($authenticatedUser);
            if ($userRole->id_role == 1 || $userRole->id_role == 2 || $userRole->id_role == 3) {
                $userModelObj = new ItemModel();
                $response = $userModelObj->viewVendorsByItemId($id);
                if(!$response){
                    $error = ApiConstant::ITEM_UPDATE_FAILED;
                    $response = array("message" => ApiConstant::ERROR_ITEM_UPDATE);
                }
            } else {
                $error = ApiConstant::USER_HAS_NO_PRIVILEGES;
            }
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            $error = ApiConstant::EXCEPTION_OCCURED;
        }

        return $this->returnableResponseData($response, $error);
    }
}