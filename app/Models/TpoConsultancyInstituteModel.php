<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 22/6/17
 * Time: 4:08 PM
 */

namespace App\Models;


use App\BaseModels\BaseTpoConsultancyInstituteModel;
use App\BaseModels\BaseExpectedJoinerTemplateModel;
use App\BaseModels\BaseTpoTemplateModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use File;

class TpoConsultancyInstituteModel extends BaseTpoConsultancyInstituteModel
{

    public function addTpoDetails($user)
    {
        $returnData = null;
        $this->email = $user['email'];
        $this->name = $user['name'];
        $this->status = $user['status'];
        $this->college_name = $user['college_name'];
        $this->phone_no = $user['phone_no'];
        $this->stage = $user['stage'];
        $this->website = $user['website'];
        $this->notes = $user['notes'];
        $this->representative_type = $user['representative_type'];
        $this->fresher_charges = $user['fresher_charges'];
        $this->experience_charges = $user['experience_charges'];
        $this->last_touch = $user['last_touch'];

        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
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

    public function getTpoDetailsById($user_id)
    {
        $response = $this:: where('tpo_details.id', $user_id)
            ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
            ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                'tpo_details.updated_at', 'tpo_details.website', 'rounds.id as stage')
            ->first();
        return $response;
    }

    public function getTpoDetailsByEmail($email)
    {
        $response = null;
        $response = $this::where('email', $email)
            ->first();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

    public function getTpoDetailsWithID($ID)
    {
        $response = null;
        $response = $this::where('id', $ID)->first();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

    public function editTpoDetails($data)
    {
        $result = null;
        $result = $this::where('id', $data['id'])->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'college_name' => $data['college_name'],
            'status' => $data['status'],
            'phone_no' => $data['phone_no'],
            'stage' => $data['stage'],
            'website' => $data['website'],
            'notes' => $data['notes'],
            'representative_type' => $data['representative_type'],
            'fresher_charges' => $data['fresher_charges'],
            'experience_charges' => $data['experience_charges'],
            'last_touch' => $data['last_touch'] ?? ''
        ]);

        return $result;
    }

    public function viewTpoDetails($data)
    {
        $type = $data['representative_type'];
        $searchInput = $data['search_input'];
        $response = null;
        if ($data['skip'] == null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->limit(20)
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->count();
            } else {
                $response = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->limit(20)
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->get();

                $dataCount = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->limit(20)
                    ->offset($data['skip'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->offset($data['skip'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->count();
            } else {
                $response = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->limit(20)
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->get();

                $dataCount = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')                    ->select('*')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->count();
            } else {
                $response = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->get();

                $dataCount = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->count();
            }
        }

        if ($data['skip'] == 0 && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('phone_no', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('representative_type', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('website', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->count();
            } else {
                $response = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->get();

                $dataCount = $this::where('representative_type', $type)
                    ->leftjoin('rounds', 'rounds.id', '=', 'tpo_details.stage')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('tpo_details.name', 'tpo_details.college_name', 'tpo_details.last_touch', 'tpo_details.email',
                        'tpo_details.experience_charges', 'tpo_details.fresher_charges', 'tpo_details.id', 'tpo_details.created_at',
                        'tpo_details.created_at', 'tpo_details.notes', 'tpo_details.phone_no', 'tpo_details.representative_type', 'tpo_details.status',
                        'tpo_details.updated_at', 'tpo_details.website', 'rounds.round_name as stage')
                    ->orderBy('tpo_details.updated_at', 'desc')
                    ->count();
            }
        }

        return array($response, $dataCount);
    }

    public function deleteTpoDetails($id)
    {
        $response = $this::where('id', $id)->delete();
        return $response;
    }

    public function isTemplateSend($template_id, $user_id)
    {
        $result = BaseTpoTemplateModel::where('id_template', $template_id)->where('id_tpo', $user_id)->first();
        if (empty($result)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function updateTpoStatus($id, $status)
    {
        $response = $this::where('id', $id)->update([
            'stage' => $status
        ]);
        return $response;
    }
}