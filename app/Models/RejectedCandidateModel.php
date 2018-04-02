<?php

namespace App\Models;

use App\BaseModels\BaseRejectedCandidateModel;
use App\Helpers\ApiConstant;

class RejectedCandidateModel extends BaseRejectedCandidateModel
{
    public function saveUserDetails($user)
    {
        $returnData = null;
        $this->email = $user['email'];
        $this->name = $user['name'];
        $this->status = $user['status'];
        $this->resume = $user['resume'];
        $this->subject = $user['subject'];
        $this->source_id = $user['source_id'];
        if ($this->save()) {
            $returnData = $this;
        }else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function viewRejectedCandidate($data)
    {
        $searchInput = $data['search_input'];
        $response = null;
        if ($data['skip'] == null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orderBy('id','desc')
                    ->get();

                $dataCount =$this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->count();

            } else {
                $response = $this::limit(25)
                ->orderBy('id','desc')
                ->get();
                $dataCount = $this :: count();
            }
        }

        if ($data['skip'] != null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->limit(25)
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->limit(25)
                    -> count();

            } else {
                $response = $this::offset($data['skip'])
                    ->limit(25)
                    ->get();
                $dataCount = $this :: count();
            }
        }
        if ($data['skip'] != null && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->limit($data['limit'])
                    ->count();

            } else {
                $response = $this::offset($data['skip'])
                    ->limit($data['limit'])
                    ->get();
                $dataCount = $this ::limit($data['limit'])
                    ->count();
            }
        }
        if ($data['skip'] == 0 && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->count();
            } else {
                $response = $this::offset($data['skip'])
                    ->limit($data['limit'])
                    ->get();
                $dataCount =$this::offset($data['skip'])
                    ->limit($data['limit'])
                    ->count();
            }
        }
       // $response = $this::all();
        return array($response,$dataCount);
    }

    public function deleteRejectedCandidate($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::CANDIDATE_DELETED);
        }
        return $response;
    }

    public function getCandidateDetailsByMail($userEmail)
    {
        $response = null;

        $response = $this::where('email', $userEmail)->first();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }
    public function getCandidateDetailsById($userId)
    {
        $response = null;

        $response = $this::where('id', $userId)->first();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

}