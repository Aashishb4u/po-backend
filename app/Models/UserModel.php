<?php

namespace App\Models;

use App\BaseModels\BaseCandidateModel;
use App\BaseModels\BaseCandidateTagModel;
use App\BaseModels\BasePurchaseOrderModel;
use App\BaseModels\BaseTagModel;
use App\BaseModels\BaseUserModel;
use App\Helpers\ApiConstant;
use App\BaseModels\BaseUserRoleModel;
use App\Helpers\AppUtility;
use File;
use Carbon\Carbon;
use App\BaseModels\BaseEmailModel;
use Illuminate\Support\Facades\DB;


class UserModel extends BaseUserModel
{
    public function isUserAlreadyExist($user)
    {
        $isUserAlreadyExist = $this::where('email', $user['email'])->first();
        $isContactExist = $this::where('contact_number', $user['contact_number'])->first();
        $returnData = null;
        if (!empty($isUserAlreadyExist)) {
            $returnData = ApiConstant::EMAIL_ALREADY_EXIST;
        }

        if (!empty($isContactExist)) {
            $returnData = ApiConstant::EMAIL_ALREADY_EXIST;
        }
        return $returnData;
    }

    public function isUserValidWithEmail($user)
    {
        $returnData = true;
        $contact = isset($user['contact_number']) ? $user['contact_number'] : null;
        $isEmailExist = $this::where('email', $user['email'])->first();
        $isContactExist = $this::where('contact_number', $contact)->first();
        if (!empty($isEmailExist) && ($user['id'] != $isEmailExist['id'])) {
            $returnData = false;
        }

        if (!empty($isContactExist) && ($user['id'] != $isContactExist['id'])) {
            $returnData = false;
        }
        return $returnData;
    }

    public function isUserAlreadyExistByEmail($email)
    {
        $isUserAlreadyExist = $this::where('email', $email)->first();
        if (!empty($isUserAlreadyExist)) {
            return true;
        }
        return false;
    }

    public function saveUserDetails($user)
    {
        $password = $user['password'] ?? '';
        $auth_token = $user['auth_token'] ?? '';
        $contact = isset($user['contact_number']) ? $user['contact_number'] : null;
//        if($password){
//            $password = bcrypt($password);
//        }
        $returnData = null;
        $this->email = $user['email'];
        $this->name = $user['vendor_name'];
        $this->contact_number = $contact;
        $this->password = $password;
        $this->remember_token = $auth_token;
        if ($this->save()) {
            $returnData = $this;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function editUser($data)
    {
        $result = null;
        $result = $this::where('id', $data['id'])->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_no' => $data['phone_no'],
            'password' => $data['password'],
            'status' => $data['status'],

        ]);
        return $result;
    }

    public function viewUser()
    {
        $response = BaseUserRoleModel::where('user_roles.id_role', '2')
            ->join('users', 'user_roles.id_user', '=', 'users.id')
            ->orderBy('users.id', 'id')
            ->select('*')
            ->get();
        return $response;
    }

    public function viewUserById($user_id)
    {
        $response = $this::where('id', $user_id)->get();
        return $response;
    }

    public function viewUserDetailById($user_id)
    {
        $response = $this::where('id', $user_id)->first();
        return $response;
    }

    public function deleteUser($id)
    {
        $response = $this::where('id', $id)->delete();
        if ($response) {
            $response = true;
        }
        return $response;
    }

    public function editVendor($data)
    {
        $contact = ($data['contact_number']) ? $data['contact_number'] : '0';
        $result = null;
        $result = $this::where('id', $data['id'])->update([
            'name' => $data['vendor_name'],
            'email' => $data['email'],
            'contact_number' => $contact,
        ]);
        return $result;
    }

    public function userDetailsByEmail($user)
    {
        $response = null;
        try {
            $response = BaseUserModel::where('email', $user['email'])->first();
            if (!empty($response)) {
                return $response;
            } else {
                return ApiConstant::DATA_NOT_FOUND;
            }
        } catch (\Exception $e) {
            return ApiConstant::EXCEPTION_OCCURED;
        }
    }

    public function getUserDetailsByEmail($email)
    {
        $response = null;
        try {
            $response = BaseUserModel::where('email', $email)->first();
            if (!empty($response)) {
                return $response;
            } else {
                return ApiConstant::DATA_NOT_FOUND;
            }
        } catch (\Exception $e) {
            return ApiConstant::EXCEPTION_OCCURED;
        }
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

    public function deleteCandidate($id)
    {
        $response = BaseUserModel::where('id', $id)->delete();
        return $response;
    }

    public function getUserDetails($user_id)
    {
        $user = BaseUserModel:: where('id', $user_id)->first();
        return $user;
    }

    public function searchCandidate($date)
    {
        $response = null;
        $toDate = $date['to_date'];
        $fromDate = $date['from_date'];
        try {
            $response = BaseUserModel::whereBetween('created_at', [$fromDate, $toDate])->get();
            return $response;
        } catch (\Exception $e) {
            return array('message' => ApiConstant::EXCEPTION_OCCURED);
        }
    }

    public function viewVendors()
    {
        $response = null;
        $responseCount = null;
        $dataTag = null;
        $limit = isset($data['limit']) ? $data['limit'] : 10;
        $skip = isset($data['skip']) ? $data['skip'] : 0;
        $response = BaseUserModel::join('vendors', 'users.id', '=', 'vendors.id_user')
            ->leftJoin('vendor_tags', 'vendors.id_user', '=', 'vendor_tags.vendor_id')
            ->leftjoin('tags', 'vendor_tags.id_tag', '=', 'tags.id')
            ->select('users.id', 'users.name', 'vendor_tags.vendor_id', DB::raw("GROUP_CONCAT(tags.name) as tag_name"), DB::raw("GROUP_CONCAT(vendor_tags.id_tag) as id_tag"), 'vendors.company_name', 'users.email', 'users.contact_number', 'vendors.city')
            ->orderBy('users.id', 'desc')
            ->groupBy('vendor_tags.vendor_id', 'vendors.company_name', 'users.email', 'users.contact_number', 'vendors.city')
            ->paginate(10);

        return $response;
    }

    public function getVendorsByFilters($data){
        $searchInput = trim($data['search_input']);
        $response = null;
        $dataCount = null;
        $response = $this;

        if (isset($data['search_input']) && !empty($data['search_input'])) {
            $response = $response->where(function($query) use ($searchInput){

                $query->orWhere('vendors.company_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.contact_number', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('tags.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('vendors.city', 'LIKE', '%' . $searchInput . '%');
            });
        }

        $response = $response->join('vendors', 'users.id', '=', 'vendors.id_user')
            ->leftJoin('vendor_tags', 'vendors.id_user', '=', 'vendor_tags.vendor_id')
            ->leftjoin('tags', 'vendor_tags.id_tag', '=', 'tags.id')
            ->select('users.id', 'users.name', 'vendor_tags.vendor_id', DB::raw("GROUP_CONCAT(tags.name) as tag_name"), DB::raw("GROUP_CONCAT(vendor_tags.id_tag) as id_tag"), 'vendors.company_name', 'users.email', 'users.contact_number', 'vendors.city')
            ->orderBy('users.id', 'desc')
            ->groupBy('vendor_tags.vendor_id', 'vendors.company_name', 'users.email', 'users.contact_number', 'vendors.city')
            ->paginate(10);

        return $response;
    }

    public function viewCandidate($data)
    {
        $searchInput = trim($data['search_input']);
        $response = null;
        $dataCount = null;
        if ($data['skip'] == null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'desc')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();

                $dataCount = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidate_feedback_table.recruiter_id', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback.grade','sources.name as source_name','sources.code' )
                    ->count();
            } else {
                $response = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'desc')
                    ->limit(20)
                    ->select('recruiter_name','candidate_feedback_table.recruiter_id','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->get();

                $dataCount = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'desc')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response =BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit(20)
                    ->offset($data['skip'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->get();

                $dataCount = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.position', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    -> count();
            } else {
                $response = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'desc')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->get();

                $dataCount = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'desc')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] != null) {
            if ($searchInput != null) {
                $response =  BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->get();

                $dataCount = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit($data['limit'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    -> count();


            } else {
                $response = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->get();

                $dataCount = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit($data['limit'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                    ->count();
            }
        }

        if ($data['skip'] == 0 && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get();

                $dataCount = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit($data['limit'])
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    -> count();


            } else {
                $response = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get();

                $dataCount = BaseUserModel::join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT users.name as 'recruiter_name',candidate_id,recruiter_id,grade FROM candidate_feedback left join users on users.id = candidate_feedback.recruiter_id WHERE candidate_feedback.created_at in (SELECT MAX(candidate_feedback.created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit(20)
                    ->select('recruiter_name','users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->count();
            }
        }

            return array($response,$dataCount);
    }

    public function viewCandidateBySearchInput($search,$data)
    {
        $searchKey = trim($search[0]);
        $searchValue = trim($search[1]);
        $response = null;
        $dataCount = null;

        if ($data['skip'] == null && $data['limit'] == null) {
            $response = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'desc')
                ->limit(20)
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                ->get();

            $dataCount = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'id')
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback.grade')
                ->count();
        }
        if ($data['skip'] != null && $data['limit'] == null) {
            $response = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'desc')
                ->limit(20)
                ->offset($data['skip'])
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                ->get();

            $dataCount = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'id')
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback.grade')
                ->count();
        }
        if ($data['skip'] != null && $data['limit'] != null) {
            $response = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'desc')
                ->offset($data['skip'])
                ->limit($data['limit'])
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                ->get();

            $dataCount = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'id')
                ->limit($data['limit'])
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback.grade')
                ->count();
        }
        if ($data['skip'] == 0 && $data['limit'] != null) {
            $response = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'desc')
                ->offset($data['skip'])
                ->limit($data['limit'])
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                ->get();

            $dataCount = BaseUserModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
                ->join('candidates', 'users.id', '=', 'candidates.id_user')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->orderBy('users.id', 'id')
                ->limit($data['limit'])
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback.grade')
                ->count();
        }
        return array($response,$dataCount);
    }

//    public function viewCandidateById($id)
//    {
//        $response = BaseUserModel::where('users.id', $id)
//            ->join('candidates', 'users.id', '=', 'candidates.id_user')
//            ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
//            ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
//            ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
//            ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
//            ->select('users.id', 'users.name', 'users.email','candidates.block_mail','users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.id as round_id ', 'candidates.cover_letter', 'qualifications.id as qualification_id', 'experiences.id as experience_id', 'positions.id as position_id', 'candidates.updated_at','users.dob','users.subjects')
//            ->get();
//        return $response;
//    }


    public function viewVendorById($id)
    {
        $purchaseObj = null;
        $purchaseObj = new BasePurchaseOrderModel();
        $response = BaseUserModel::where('users.id', $id)
            ->join('vendors', 'users.id', '=', 'vendors.id_user')
            ->select('users.*','vendors.*')
            ->get();

        $isDraft = $purchaseObj::where('purchase_orders.vendor_id', $id)
            ->select(DB::raw("Exists(select status from purchase_orders pod where pod.status = 'Draft' ) as isDraftExist"))
                ->first();

        $draftPO = $purchaseObj::where('purchase_orders.vendor_id', $id)
            ->select(DB::raw("(select id from purchase_orders pod where pod.status = 'Draft' ) as draftedPO"))
                ->first();

        return array($response, $isDraft, $draftPO);
    }


    /* update profile details*/

    public function updateProfile($data, $filePath, $imgData)
    {
        $response = null;
        $destinationPath = public_path('upload/');
        try {
            $response = BaseUserModel::where('id', $data['id'])->first();
            if (!empty($response)) {
                $response->name = $data['name'];
                $response->email = $data['email'];
                $response->phone_no = $data['phone_no'];
                if ($response->image == "default.png" && $data['image'] != null) {
                    file_put_contents($filePath, $imgData);
                    chmod($filePath, 0777);
                    $response->image = $data['image'];
                } elseif ($data['image'] != null && $response->image != "default.png") {
                    file_put_contents($filePath, $imgData);
                    chmod($filePath, 0777);
                    File::delete($destinationPath . '/' . $response->image);
                    $response->image = $data['image'];
                }
                elseif($data['image'] == null)
                {
                    $response->image = 'default.png';
                }
                if ($response->save()) {
                    return array($data, "message" => ApiConstant::PROFILE_UPDATED_SUCCESSFULLY);
                }
            } else {
                return ApiConstant::RECORD_NOT_EXIST;
            }
        } catch (\Exception $e) {
            return ApiConstant::EXCEPTION_OCCURED;
        }
    }

    public function resetPasswordDetails($userDetails)
    {
        $response = null;

            if (!empty( $userDetails)) {
                $user = BaseUserModel::where('id',  $userDetails['id'])->first();
                $updatePassword = BaseUserModel::where('id',  $userDetails['id'])->update(['password' => bcrypt($userDetails['password'])]);
                if (!empty($updatePassword)) {
                    return $response = array('userData' => $user->email, 'message' => ApiConstant::PASSWORD_CHANGE_SUCCESSFULLY);
                } else {
                    return $response = array('message' => ApiConstant::ERROR_PASSWORD_UPDATE);
                }
            } else {
                $response = array('message' => ApiConstant::RECORD_NOT_EXIST);
            }
        return $response;
    }

    public function changePassword($userData)
    {
        $returnData = null;
        $inputData['password'] = $userData['password'];
        $inputData['id'] = $userData['id'];
        if (!empty($inputData)) {
            try {
                $userDbEmail = BaseUserModel::where('id', $inputData['id'])->first();
                if (!empty($userDbEmail)) {
                        $updatePassword = BaseUserModel::where('id', $inputData['id'])->update(['password' => bcrypt($inputData['password'])]);
                        if (!empty($updatePassword)) {
                            $returnData = array('message' => ApiConstant::PASSWORD_CHANGE_SUCCESSFULLY);
                        } else {
                            $returnData = array('message' => ApiConstant::ERROR_PASSWORD_UPDATE);
                        }
                        return $returnData;
                }
            } catch (\Exception $e) {
                return array('message' => ApiConstant::EXCEPTION_OCCURED);
            }
            $returnData = array('message' => ApiConstant::RECORD_NOT_EXIST);
        }
        return $returnData;
    }

    public function barGraph()
    {
        $todayDate = Carbon::now();
        $date = Carbon::parse($todayDate);
        $now = date_format($date , 'Y-m-d');

        $response = BaseEmailModel::where(DB::raw("(DATE_FORMAT(emails.created_at,'%Y-%m-%d'))"), '=', $now)
                ->where('emails.is_sent','=','1')
                ->join('templates', 'templates.id', '=', 'emails.template_id')
                ->select('emails.template_id', DB::raw('count(emails.template_id) as total'), 'templates.name')
                ->groupBy('emails.template_id')
                ->get();

            $dataCount = BaseEmailModel::where(DB::raw("(DATE_FORMAT(emails.created_at,'%Y-%m-%d'))"), '=', $now)
                ->where('emails.is_sent','=','1')
                ->join('templates', 'templates.id', '=', 'emails.template_id')
                ->select('emails.template_id', DB::raw('count(emails.template_id) as total'), 'templates.name')
                ->count();
          return array($response,$dataCount);
    }

    public function barGraphWithinDate($graphData)
    {
        $result = null;
        $from_date = Carbon::parse($graphData['from_date']);
        $from_dateformate = date_format($from_date , 'Y-m-d');
        $to_date =  Carbon::parse($graphData['to_date']);
        $to_dateformate = date_format($to_date ,'Y-m-d');

        $response = BaseEmailModel::whereBetween(DB::raw("(DATE_FORMAT(emails.created_at,'%Y-%m-%d'))"),[$from_dateformate , $to_dateformate])
            ->where('emails.is_sent','=','1')
            ->join('templates', 'templates.id', '=', 'emails.template_id')
            ->select('emails.template_id', DB::raw('count(emails.template_id) as total'), 'templates.name')
            ->groupBy('emails.template_id')
            ->get();

        $dataCount = BaseEmailModel::whereBetween(DB::raw("(DATE_FORMAT(emails.created_at,'%Y-%m-%d'))"),[$from_dateformate , $to_dateformate])
            ->where('emails.is_sent','=','1')
            ->join('templates', 'templates.id', '=', 'emails.template_id')
            ->select('emails.template_id', DB::raw('count(emails.template_id) as total'), 'templates.name')
            ->count();
        return array($response,$dataCount);
    }

    public function saveRemeberId($bcryptId,$id)
    {
        $result = null;
        $result = $this::where('id',$id)->update([
            'rememberId' => $bcryptId
        ]);
        if($result)
        {
            return true;
        }

        return false;
    }

    public function getUserDetailsByRememberId($rememberId)
    {   $response = null;
        $response = BaseUserModel::where('rememberId', $rememberId)->first();
        if (!empty($response)) {
            return $response;
        }else {
            return ApiConstant::LINK_ALREADY_USED;
        }
    }

    public function removeRememberId($user)
    {
        $response = null;
        $response= $this::where('id',$user['id'])->update([
            'rememberId' => null
        ]);
        if($response){
            return true;
        }
        return false;
    }

    public function searchCandidateByInput($data)
    {
        $search = explode(":", $data['search_input']);
        $searchKey = trim($search[0]);
        $searchValue = $search[1];
        $response = null;
        $createDate1= date_create($data['from_date']);
        $createDate2= date_create($data['to_date']);
        $date1 = date_format($createDate1, 'Y-m-d');
        $date2 = date_format($createDate2 , 'Y-m-d');
        $response = BaseCandidateModel::where($searchKey, 'LIKE', '%' . $searchValue . '%')
        ->whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
            ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
            ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
            ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
            ->orderBy('candidates.stage', 'desc')
            ->select('users.id', 'users.name', 'users.email','rounds.round_name as stage','candidates.date_time')
            ->get();
        return $response;
    }

    public function viewBirthdayList($data)
 {
     $response = null;
     $dataCount = null;
     $todayDate = Carbon::now();
     $date = Carbon::parse($todayDate);
     $now = date_format($date , 'd-m');
     $nowString = (string)$now;
     if ($data['skip'] == null && $data['limit'] == null) {
         $response = BaseUserModel::where('dob', 'LIKE',  $nowString.'%' )
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit(20)
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->get();
         $dataCount = BaseUserModel::where('dob', 'LIKE', $nowString.'%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit(20)
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->count();
     }
     if ($data['skip'] != null && $data['limit'] == null) {
         $response = BaseUserModel::where('dob', 'LIKE', $nowString.'%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit(20)
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->get();

         $dataCount = BaseUserModel::where('dob', 'LIKE', $nowString.'%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit(20)
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->count();

     }
     if ($data['skip'] != null && $data['limit'] != null) {
         $response = BaseUserModel::where('dob', 'LIKE', $nowString.'%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->offset($data['skip'])
             ->limit($data['limit'])
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->get();

         $dataCount = BaseUserModel::where('dob', 'LIKE', $nowString. '%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit($data['limit'])
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->count();
     }
     if ($data['skip'] == 0 && $data['limit'] != null) {
         $response = BaseUserModel::where('dob', 'LIKE', $nowString. '%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit(20)
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->get();

         $dataCount = BaseUserModel::where('dob', 'LIKE', $nowString. '%')
             ->leftJoin('candidates','users.id','=','candidates.id_user')
             ->leftJoin('rounds','candidates.stage','=','rounds.id')
             ->limit(20)
             ->orderBy('users.name','asc')
             ->select('users.id','users.name','users.email','rounds.round_name as stage')
             ->count();
     }


         return array($response,$dataCount);
 }

    public function viewCandidateByDate($data)
    {
        $response = null;
        $createDate1= date_create($data['from_date']);
        $createDate2= date_create($data['to_date']);
        $date1 = date_format($createDate1, 'Y-m-d');
        $date2 = date_format($createDate2 , 'Y-m-d');
        $searchInput = trim($data['search_input']);
        $dataCount = null;
        if ($data['skip'] == null && $data['limit'] == null) {
            if ($searchInput != null) {

                $response =BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();

                $dataCount = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->count();


            } else {
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();

                $dataCount = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response =BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->offset($data['skip'])
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();

                $dataCount =BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->count();

            } else {
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();


                $dataCount = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();

                $dataCount = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit($data['limit'])
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    -> count();


            } else {
                $response =BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();


                $dataCount = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit($data['limit'])
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->count();
            }
        }

        if ($data['skip'] == 0 && $data['limit'] != null) {
            if ($searchInput != null) {
                $response =BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();
                $dataCount = BaseUserModel::where('users.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%')
                    ->join('candidates', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->orderBy('users.id', 'id')
                    ->limit($data['limit'])
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    -> count();


            } else {
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->get();

                $dataCount = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $date1 , $date2])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                    ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                    ->limit(20)
                    ->orderBy('rounds.round_name', 'asc')
                    ->orderBy('users.name','asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade','sources.name as source_name','sources.code')
                    ->count();
            }
        }

        return array($response,$dataCount);
    }

    public function getCandidatesByDates($data)
    {
        $response = null;
        $status = $data['status']?? '';
        $createDate1= date_create($data['from_date']);
        $createDate2= date_create($data['to_date']);
        $searchInput = trim($data['search_input']);
        $from_date = date_format($createDate1, 'Y-m-d');
        $to_date = date_format($createDate2 , 'Y-m-d');
        $dataCount = null;
        $limit = $data['limit']?? 20;

        if($data['skip'] == null) {
            if ($searchInput) {
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [$from_date, $to_date]);
                     $response = $response->where(function($query) use ($searchInput){
                         $query->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                             ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%');

                     });
                 $response = $response->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                     ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                     ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                     ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                     ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                     ->leftJoin(DB::raw("(SELECT candidate_id,grade  FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');

                $responseCount = $response;
                $dataCount = $responseCount->count();
                $response =  $response ->limit($limit)
                    ->orderBy('users.id', 'desc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get();

            } else {
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [$from_date, $to_date])
                    ->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade  FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');

                $responseCount = $response;
                $response = $response->limit($limit)
                    ->orderBy('users.name', 'asc')
//                    ->orderBy('users.id', 'desc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.created_at', 'candidates.updated_at', 'candidates.date_time', 'users.dob', 'users.subjects', 'candidate_feedback_table.grade')
                    ->get();

                $dataCount = $responseCount->count();
            }
        }else if($data['skip'] != null){
            if($searchInput){
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [$from_date, $to_date]);
                $response = $response->where(function($query) use ($searchInput){
                    $query->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('candidates.date_time', 'LIKE', '%' . $searchInput . '%');

                });
                $response = $response->leftJoin('users', 'users.id', '=', 'candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade  FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');

                $responseCount = $response;
                $dataCount = $responseCount ->count();
                $response =  $response ->offset($data['skip'])
                    ->limit($limit)
                    ->orderBy('users.id', 'desc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get();
            }else{
                $response = BaseCandidateModel::whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [ $from_date , $to_date])
                    ->leftJoin('users',  'users.id', '=','candidates.id_user')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade  FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');

                $responseCount = $response;
                $dataCount = $responseCount->count();

                $response = $response->limit($limit)
                    ->offset($data['skip'])
                    ->orderBy('users.name', 'asc')
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.created_at', 'candidates.updated_at', 'candidates.date_time', 'users.dob', 'users.subjects', 'candidate_feedback_table.grade')
                    ->get();
            }

        }
        return array($response,$dataCount);
    }

}

