<?php

namespace App\Models;

use App\BaseModels\BaseAttachmentModel;
use App\BaseModels\BaseUnApprovedCandidates;
use App\Helpers\ApiConstant;
use App\BaseModels\BaseUserRoleModel;


class UnApprovedCandidates extends BaseUnApprovedCandidates
{
    public function saveUnApprovedCandidateDetails($user)
    {
        $returnData = null;
        $this->email = $user['email'];
        $this->name = $user['name'];
        $this->status = $user['status'];
        $this->resume = $user['resume'];
        $this->subject = $user['subject'];
        $this->source_id = $user['source_id']??null;
        $this->source_info = $user['source_info']??'';
        $this->written_feedback = $user['feedback']??'';

        return $this->save();
    }

    public function getCandidateDetailsByMail($userEmail)
    {
        $response = null;

        $response = BaseUnApprovedCandidates::where('email', $userEmail)->first();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

    public function viewUnApprovedCandidate($data)
    {
        $searchInput = $data['search_input'];
        $response = null;
        if ($data['skip'] == null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->get();

                $dataCount =$this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->count();

            } else {

                $response = $this::limit(25)
                ->orderBy('un_approved_candidates.id', 'desc')
               ->get();
                $dataCount = $this :: count();
            }
        }
        if ($data['skip'] != null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->limit(25)
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->limit(25)
                    -> count();

            } else {
                $response = $this::offset($data['skip'])
                    ->limit(25)
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->get();
                $dataCount = $this :: count();
            }
        }
        if ($data['skip'] != null && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->limit($data['limit'])
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->limit($data['limit'])
                    ->count();

            } else {
                $response = $this::offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->get();
                $dataCount = $this ::limit($data['limit'])
                ->count();
            }
        }

        if ($data['skip'] == 0 && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->limit($data['limit'])
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('subject', 'LIKE', '%' . $searchInput . '%')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                     ->count();
            } else {
                $response = $this::offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('un_approved_candidates.id', 'desc')
                    ->get();
                $dataCount =$this::offset($data['skip'])
                    ->limit($data['limit'])
                    ->count();
            }
        }
        return array($response,$dataCount);
    }

    public function deleteByEmail($email)
    {
        $response = $this::where('email', $email)->delete();
        return $response;
    }

    public function isUserAlreadyExist($email)
    {
        $isUserAlreadyExist = $this::where('email', $email)->first();
        $returnData = null;
        if (!empty($isUserAlreadyExist)) {
            $returnData = ApiConstant::EMAIL_ALREADY_EXIST;
        }
        return $returnData;
    }

    public function deleteUnApprovedCandidate($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = array("message" => ApiConstant::CANDIDATE_DELETED);
        }
        return $response;
    }


}
