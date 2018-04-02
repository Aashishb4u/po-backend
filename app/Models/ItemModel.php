<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 5/12/17
 * Time: 8:41 PM
 */

namespace App\Models;
use App\BaseModels\BaseItemModel;
use App\BaseModels\BaseItemQuantityModel;
use App\BaseModels\BaseUserModel;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiConstant;

class ItemModel extends BaseItemModel
{
    // function is used to save Item details.
    public function saveItem($user)
    {
        $returnData = null;
        $item_name = ($user['item_name']) ? $user['item_name'] : '';
        $gst = ($user['gst']) ? $user['gst'] : null;
        $location = ($user['location']) ? $user['location'] : '' ;
        $description = ($user['description']) ? $user['description'] : '' ;

        $this->name = $item_name;
        $this->gst = $gst;
        $this->location_id = $location;
        $this->description = $description;
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }


    public function addItemQuantity($user)
    {
        $returnData = null;
        $response = null;
        $quantityAvailable = null;
        $response = $this::where('items.id',$user['id_item'])->first();
        if ($response) {
            $quantityAvailable = $response->quantity_available ?? 0;
            $response = $this::where('items.id',$user['id_item'])->update([
                'quantity_available'=> $quantityAvailable + $user['quantity']
            ]);
            $itemQuantityLogs = new BaseItemQuantityModel();
            $itemQuantityLogs->item_id = $user['id_item'];
            $itemQuantityLogs->purchase_id = $user['purchase_id'];
            $itemQuantityLogs->vendor_id = $user['vendor_id'];
            $itemQuantityLogs->quantity = $user['quantity'];
            $itemQuantityLogs->date = $user['date'];
            $itemQuantityLogs->price = $user['price'];
            $itemQuantityLogs->status = 1;
            $itemQuantityLogs->added_by = '';
            if ($itemQuantityLogs->save()) {
                $returnData = $this;
            }
        }
        return $returnData;
    }

    public function addUsedItemQuantity($user)
    {
        $returnData = null;
        $response = null;
        $quantityAvailable = null;
        $response = $this::where('items.id',$user['id_item'])->first();
        if ($response) {
            $quantityAvailable = $response->quantity_available ?? 0;
            $quantityUsed = $response->quantity_used ?? 0;
            if ($quantityAvailable >= $user['quantity']){
                $response = $this::where('items.id',$user['id_item'])->update([
                    'quantity_available'=> $quantityAvailable - $user['quantity'],
                    'quantity_used'=> $quantityUsed + $user['quantity']
                ]);
                $itemQuantityLogs = new BaseItemQuantityModel();
                $itemQuantityLogs->item_id = $user['id_item'];
                $itemQuantityLogs->purchase_id = 0;
                $itemQuantityLogs->vendor_id = 0;
                $itemQuantityLogs->quantity = $user['quantity'];
                $itemQuantityLogs->date = $user['date'];
                $itemQuantityLogs->price = 0;
                $itemQuantityLogs->status = 2;
                $itemQuantityLogs->added_by = '';
                if ($itemQuantityLogs->save()) {
                    $returnData = $this;
                }
            } else {
                $returnData = ApiConstant::ITEM_USED_GREATER;
            }

        }
        return $returnData;
    }

    public function getItemQuantityById($user) {
//        $response = null;
//        $response = $this::where('items.id',$user['id_item'])
        $response = $this::where('items.id', $user['id_item'])
            ->where('item_quantity_logs.status','=',1)
            ->leftJoin('item_quantity_logs','items.id','item_quantity_logs.item_id')
            ->join('users','item_quantity_logs.vendor_id','users.id')
            ->select('item_quantity_logs.id as log_id','item_quantity_logs.item_id as id_item','users.name as vendor_name','item_quantity_logs.vendor_id','item_quantity_logs.purchase_id','item_quantity_logs.quantity','item_quantity_logs.purchase_id','item_quantity_logs.date','item_quantity_logs.price')
            ->orderBy('item_quantity_logs.id','desc')
            ->paginate(10);
        return $response;
    }


    public function getItemReceivedDetails($user) {
//        $response = null;
//        $response = $this::where('items.id',$user['id_item'])
        $itemQuantityLogs = new BaseItemQuantityModel();
        $response = $itemQuantityLogs::where('item_quantity_logs.status','=',1)
            ->where('item_quantity_logs.vendor_id',$user['vendor_id'])
            ->where('item_quantity_logs.purchase_id',$user['purchase_id'])
            ->join('items','item_quantity_logs.item_id','items.id')
            ->select('item_quantity_logs.id as log_id','item_quantity_logs.item_id as id_item','items.name as item_name','item_quantity_logs.quantity','item_quantity_logs.date','item_quantity_logs.price')
            ->orderBy('item_quantity_logs.id','desc')
            ->groupBy('item_quantity_logs.id','item_quantity_logs.item_id','items.name','item_quantity_logs.quantity','item_quantity_logs.date','item_quantity_logs.price')
            ->paginate(10);
        return $response;
    }

    public function getUsedItemQuantityById($user) {
//        dd($user);
//        $response = null;
//        $response = $this::where('items.id',$user['id_item'])
        $response = $this::where('items.id', $user['id_item'])
            ->where('item_quantity_logs.status','=',2)
            ->leftJoin('item_quantity_logs','items.id','item_quantity_logs.item_id')
            ->select('item_quantity_logs.id as log_id','item_quantity_logs.item_id as id_item','item_quantity_logs.vendor_id','item_quantity_logs.purchase_id','item_quantity_logs.quantity','item_quantity_logs.purchase_id','item_quantity_logs.date','item_quantity_logs.price')
            ->orderBy('item_quantity_logs.id','desc')
            ->paginate(10);
        return $response;

    }

    // function is used to check Item in Items table.
    public function isItemExist($tagData)
    {
        $tagId = null;
        $tagName['id_item'] = null;
        $tagId = $tagData['id_item'] ?? null;
        $tagName = $tagData['item_name'];
        $response = null;
        $response = $this::where('id',$tagId)
        ->where('name',$tagName)->first();
        if($response) {
            $response = null;
        } else {
            $response = $this::where('name',$tagName)->first();
            $response = ($response) ? true : false;
        }
        return $response;
    }

    // function is used to view Item by search string from Items table.
    public function viewItemsBySearch ($data) {
        $searchInput = trim($data['search_input']);
        $response = null;
        $dataCount = null;
        $response = $this;
        if (isset($data['search_input']) && !empty($data['search_input'])) {
            $response = $response->where(function($query) use ($searchInput){

                $query->orWhere('items.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('items.description', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('tags.name', 'LIKE', '%' . $searchInput . '%');
            });
        }
        $response = $response->leftJoin('item_tags', 'items.id', '=', 'item_tags.id_item')
            ->leftJoin('tags', 'item_tags.id_tag', '=', 'tags.id')
            ->select('items.description',DB::raw("GROUP_CONCAT(tags.name) as tag_name"),'items.id','items.name',DB::raw("GROUP_CONCAT(item_tags.id_tag) as id_tag"))
            ->orderBy('id','desc')
            ->groupBy('items.description','items.id','items.name')
            ->paginate(10);
        return $response;
    }

    // function is used to view Items from Items table.
    public function viewItems()
    {
        $response = null;
        $response = $this::leftJoin('item_tags', 'items.id', '=', 'item_tags.id_item')
            ->leftJoin('tags', 'item_tags.id_tag', '=', 'tags.id')
            ->select('items.description',DB::raw("GROUP_CONCAT(tags.name) as tag_name"),'items.id','items.name',DB::raw("GROUP_CONCAT(item_tags.id_tag) as id_tag"))
            ->orderBy('id','desc')
            ->groupBy('items.description','items.id','items.name')
            ->paginate(10);
        return $response;
    }

    // function is used to view Items names and Id related to Vendor Id.
    public function viewItemNames($data)
    {
        $vendorItems = new VendorTagModel();
        $vendor_id = $data['vendor_id'];
        $response = null;
        $response = $vendorItems::where('vendor_tags.vendor_id', $vendor_id)
            ->join('item_tags','vendor_tags.id_tag','item_tags.id_tag')
            ->join('items','item_tags.id_item','items.id')
            ->select('item_tags.id_item as id_item','items.name')
            ->groupBy('id_item')
            ->orderBy('items.name','asc')
            ->get();
        return $response;

    }

    // function is used to delete Item by Id.
    public function deleteItem($id)
    {
        $response = $this::where('id', $id)->delete();
        return $response;
    }

    public function checkItemDependancy($id)
    {
        $response = $this::where('items.id', $id)
            ->where('purchase_order_details.id_item',$id)
            ->join('purchase_order_details','items.id','purchase_order_details.id_item')
            ->first();
        return $response;
    }

    public function deleteItemQuantity($data)
    {
        $item_id = $data['item_id'];
        $log_id = $data['log_id'];
        $response = null;
        $itemQuantityLogs = new BaseItemQuantityModel();
        $logResponse = $itemQuantityLogs::where('id',$log_id)->first();
        $itemResponse = $this::where('id',$item_id)->first();
        $deleteQuantityCount = $logResponse->quantity;
        $quantityAvailable = $itemResponse->quantity_available;
        $response = $itemQuantityLogs::where('item_quantity_logs.id',$log_id)->delete();
        if($response) {
            $response = $this::where('items.id',$item_id)->update([
                'quantity_available'=> $quantityAvailable - $deleteQuantityCount
            ]);
        }
        return $response;
    }

    public function deleteItemUsed($data)
    {
        $item_id = $data['item_id'];
        $log_id = $data['log_id'];
        $response = null;
        $itemQuantityLogs = new BaseItemQuantityModel();
        $logResponse = $itemQuantityLogs::where('id',$log_id)->first();
        $itemResponse = $this::where('id',$item_id)->first();
        $deleteQuantityCount = $logResponse->quantity;
        $quantityUsed = $itemResponse->quantity_used;
        $quantityAvailable = $itemResponse->quantity_available;
        $response = $itemQuantityLogs::where('item_quantity_logs.id',$log_id)->delete();
        if($response) {
            $response = $this::where('items.id',$item_id)->update([
                'quantity_used'=> $quantityUsed - $deleteQuantityCount
            ]);
            if ($response) {
                $response = $this::where('items.id',$item_id)->update([
                    'quantity_available'=> $quantityAvailable + $deleteQuantityCount
                ]);
            }
        }
        return $response;
    }

    // function is used to get Item by Id.
    public function viewItemById($id)
    {
        $response = $this::where('id', $id)
            ->select('items.id','items.name','items.location_id as location','items.description','items.gst','items.quantity_available','items.quantity_used')
            ->get();
        return $response;
    }

    public function viewPurchaseOrdersByItemId($id)
    {

        $response = $this::where('items.id', $id)
            ->where('purchase_order_details.id_item',$id)
            ->where('purchase_orders.status','=','Sent')
            ->leftJoin('purchase_order_details','items.id','purchase_order_details.id_item')
            ->leftJoin('purchase_orders','purchase_order_details.purchase_id','purchase_orders.id')
            ->join('vendors','purchase_orders.vendor_id','vendors.id_user')
            ->select('purchase_order_details.purchase_id','purchase_order_details.price','vendors.company_name','vendors.id_user as vendor_id')
            ->get();

        return $response;

    }

    public function viewVendorsByItemId($id)
    {

        $response = $this::where('items.id', $id)
            ->join('item_tags','items.id','item_tags.id_item')
            ->join('vendor_tags','item_tags.id_tag','vendor_tags.id_tag')
            ->join('vendors','vendor_tags.vendor_id','vendors.id_user')
            ->select('vendors.company_name','vendors.id_user')
            ->get();

        return $response;

    }

    // function is used to update item_name, description in Items table.
    public function updateItemDetails($data)
    {
        $itemName = ($data['item_name']) ? $data['item_name'] : '';
        $gst = ($data['gst']) ? $data['gst'] : null;
        $location = ($data['location']) ? $data['location'] : null;
        $descriptionName = ($data['description']) ? $data['description'] : '';
        $result = $this::where('id', $data['id_item'])->update(
            [
                'name' => $itemName,
                'gst' => $gst,
                'location_id' => $location,
                'description' => $descriptionName,
            ]
        );
        return $result;
    }

    // function is used to count user by email.
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

    // function is used to get Item by Id.
    public function isUserAlreadyExist($user)
    {
        $isUserAlreadyExist = $this::where('email', $user['email'])->first();
        $returnData = null;
        if (!empty($isUserAlreadyExist)) {
            $returnData = ApiConstant::EMAIL_ALREADY_EXIST;
        }
        return $returnData;
    }

    // function is used to save user details.
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

}