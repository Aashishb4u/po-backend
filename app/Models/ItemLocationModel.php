<?php
/**
 * Created by PhpStorm.
 * User: lt-73
 * Date: 30/3/18
 * Time: 3:20 PM
 */

namespace App\Models;


use App\BaseModels\BaseItemLocationModel;

class ItemLocationModel extends BaseItemLocationModel
{

    public function getItemLocationById($data)
    {
        $response = null;
        $id = $data['id'];
        $response = $this::where('id', $id)
            ->select('items_location.id','items_location.name','items_location.description')
            ->get();
        return $response;
    }

    public function viewItemLocations()
    {
        $response = null;
        $response = $this::select('items_location.id','items_location.name','items_location.description')
            ->orderBy('items_location.id', 'desc')
            ->paginate(10);
        return $response;
    }

    public function viewAllItemLocations()
    {
        $response = null;
        $response = $this::select('items_location.id','items_location.name','items_location.description')
            ->orderBy('items_location.id', 'desc')
            ->get();
        return $response;
    }

    public function addItemLocation($data)
    {
        $response = null;
        $this->name = $data['location_name'];
        $this->description = $data['description'];
        if ($this->save()) {
            $response = $this;
        }
        return $response;
    }

    public function editItemLocation($data)
    {
        $response = null;
        $id = $data['id'];
        $locationName = $data['location_name'];
        $description = $data['description'];
        $response = $this::where('id', $id)->update(
            [
                'name' => $locationName,
                'description' => $description,
            ]
        );
        return $response;
    }

    public function deleteItemLocation($data)
    {
        $response = null;
        $id = $data['id'];
        $response = $this::where('id', $id)->delete();
        return $response;
    }
}