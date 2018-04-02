<?php

namespace App\Models;

use App\BaseModels\BaseCandidateModel;
use App\BaseModels\BaseCandidateTagModel;
use App\BaseModels\BaseEmailModel;
use App\BaseModels\BaseMonthDifferenceModel;
use App\BaseModels\BaseTagModel;
use App\BaseModels\BaseUserModel;
use App\BaseModels\BaseUserRoleModel;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ApiConstant;
use App\Helpers\AppUtility;
use Illuminate\Support\Facades\DB;
use File;
use DateTime;

class CandidateModel extends BaseCandidateModel
{
    public function saveCandidateDetails($user)
    {
        $returnData = null;
        $isUserAlreadyExist = BaseUserModel::where('email', $user['email'])->first();
        if (!empty($isUserAlreadyExist)) {
            $returnData = ApiConstant::EMAIL_ALREADY_EXIST;
        } else {
            try {
                $email = isset($user['email']) ? AppUtility::check_email_address($user['email']) : null;
                if ($email) {
                    $role = 3;
                    $status = "Active";
                    $image = "default.png";
                    $this->email = $user['email'];
                    $this->name = $user['name'];
                    $this->phone_no = $user['phone_no'];
                    $this->status = $status;
                    $this->password = bcrypt($user['password']);
                    $this->image = $image;
                    if ($this->save()) {
                        $id_user = $this->id;
                        $role = BaseUserRoleModel::insert(['id_user' => $id_user, 'id_role' => $role]);
                        if ($role) {
                            if ($role) {
                                $returnData = ApiConstant::SUCCESSFULLY_ADD;
                            }
                        }
                    } else {
                        $returnData = ApiConstant::DATA_NOT_SAVED;
                    }
                }
            } catch (\Exception $e) {
                return ApiConstant::EXCEPTION_OCCURED;
            }
        }
        return $returnData;
    }

    public function viewCandidateProfileById($user_id)
    {
        $response = BaseUserModel::where('users.id', $user_id)
            ->join('candidates', 'users.id', '=', 'candidates.id_user')
            ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
            ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
            ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
            ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
            ->select('users.name', 'users.email', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.cover_letter', 'candidates.updated_at')
            ->get();
        return $response;
    }

    public function candidateApply($user)
    {
        $returnData = null;
        $id = $user['id'];
        $checkUser = BaseCandidateModel::where('id_user', $user['id'])->first();
        $month = BaseMonthDifferenceModel::select('month_difference')->first();
        $stage = 26;
        $qualification = $user['qualification'];
        $experience = $user['experience'];
        $position = $user['position'];
        $cover_letter = $user['cover_letter'];
        $certificate = $user['certificate'];
            if ($checkUser) {
                $days = $month->month_difference * 30;
                $oldDate = new DateTime($checkUser['updated_at']);
                $now = new DateTime(Date('Y-m-d'));
                $interval = $oldDate->diff(($now));
                if ($interval->days >= $days) {
                    $response = BaseCandidateModel::where('id_user', $user['id'])->update(
                        ['qualification' => $qualification,
                            'stage' => $stage,
                            'experience' => $experience,
                            'position' => $position,
                            'certificate' => $certificate,
                            'cover_letter' => $cover_letter
                        ]);
                } else {
                      if(empty($checkUser['qualification']))
                      {
                          $response = BaseCandidateModel::where('id_user', $user['id'])->update(
                              ['qualification' => $qualification,
                                  'stage' => $stage,
                                  'experience' => $experience,
                                  'position' => $position,
                                  'certificate' => $certificate,
                                  'cover_letter' => $cover_letter
                              ]);
                      }
                      else{
                          $response = ApiConstant::EMAIL_NOT_REGISTERED;
                      }
                }
            } else {
                $response = BaseCandidateModel::insert(
                    ['qualification' => $qualification,
                        'stage' => $stage,
                        'experience' => $experience,
                        'position' => $position,
                        'certificate' => $certificate,
                        'cover_letter' => $cover_letter,
                        'id_user' => $id
                    ]);
            }
        return $response;
    }

    public function updateCandidateDetails($data)
    {
        $result = $this::where('id_user', $data['id'])->update([
            'qualification' => $data['qualification'],
            'stage' => $data['stage'],
            'experience' => $data['experience'],
            'position' => $data['position'],
            'block_mail' => $data['block_mail']
        ]);
        return $result;
    }

//    public function deleteCandidate($id)
//    {
//        $response = $this::where('id_user', $id)->delete();
//        return $response;
//    }


    public function getCandidateByUserId($id)
    {
        $response = $this::where('id_user', $id)
            ->join('users','id_user','=','users.id')
            ->select('candidates.*','users.name as name')
            ->first();
        return $response;
    }

    public function updateCandidateStage($id,$status,$dateTime)
    {
      $response = $this::where('id_user',$id)->update([
          'stage'=> $status,
          'date_time'=>$dateTime
      ]);
        return $response;
    }

    public function insertDateTime($userId,$dateTime)
    {
        $response = $this::where('id_user',$userId)->update([
            'date_time'=> $dateTime
        ]);
        return $response;

    }
    public function getCandidateLogs($id)
    {
        $response = BaseEmailModel::where('emails.id_user', $id)
            ->join('rounds', 'rounds.id', '=', 'emails.status')
            ->select('rounds.round_name as status','emails.date_time')
            ->orderBy('emails.id', 'desc')
            ->get();

        return $response;
    }


    public function getCandidatesByFilters($data){
        $limit = $data['limit'] ?? 20;
        $searchInput = trim($data['search_input']);
        $response = null;
        $dataCount = null;
        $createDate1= date_create($data['from_date']);
        $createDate2= date_create($data['to_date']);
        $from_date = date_format($createDate1, 'Y-m-d');
        $to_date = date_format($createDate2 , 'Y-m-d');

            $response = $this;

            if (isset($data['from_date']) && isset($data['from_date']) && !empty($data['from_date']) && !empty($data['from_date'])) {
                $response = $response->whereBetween(DB::raw("STR_TO_DATE(candidates.date_time, '%D %M %Y')"), [$from_date, $to_date]);
            }

            if (isset($data['status']) && !empty($data['status'])) {
                $response = $response->whereIn('stage', $data['status']);
            }

            if (isset($data['id_tag']) && !empty($data['id_tag'])) {
                $response = $response->whereIn('id_tag', $data['id_tag']);
            }

            if (isset($data['search_input']) && !empty($data['search_input'])) {
                $response = $response->where(function($query) use ($searchInput){
                    $query->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%');
                });
            }

            $response = $response->join('users', 'users.id', '=', 'candidates.id_user')
                ->leftjoin('candidate_tags', 'candidates.id_user', '=', 'candidate_tags.candidate_id')
                ->leftjoin('candidate_sources', 'candidates.id_user', '=', 'candidate_sources.candidate_id')
                ->leftJoin('sources', 'candidate_sources.source_id', '=', 'sources.id')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id')
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position', 'candidates.created_at', 'candidates.updated_at', 'candidates.date_time', 'users.dob', 'users.subjects', 'candidate_feedback_table.grade','sources.name as source_name','sources.code' )
                ->distinct();

        $responseCount = $response;
        $dataCount = $responseCount->get()->count();

        if($data['skip'] == null){
            $response = $response
                ->limit($limit)
                ->orderBy('users.id', 'desc')
                ->get();

        }else{
            $response = $response
                ->offset($data['skip'])
                ->limit($limit)
                ->orderBy('users.id', 'desc')
                ->get();
        }

            return array($response, $dataCount);
    }

   public function viewCandidateByStatus($data)
   {
       $limit = $data['limit'] ?? 20;
       $searchInput = trim($data['search_input']);
       $response = null;
       $dataCount = null;
       if ($data['skip'] == null){
           if($searchInput){
               $response =  $this::whereIn('stage',$data['status']);
               $response = $response->where(function($query) use ($searchInput){
                   $query->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%');
               });

               $response = $response->join('users', 'users.id', '=', 'candidates.id_user')
                   ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                   ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                   ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                   ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                   ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
               $responseCount = $response;
               $dataCount = $responseCount->count();
               $response =  $response ->limit($limit)
                   ->orderBy('users.id', 'desc')
                   ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                   ->get();
           }else{
               $response = $this::whereIn('stage',$data['status'])
                   ->join('users', 'users.id', '=', 'candidates.id_user')
                   ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                   ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                   ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                   ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                   ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
               $responseCount = $response;
               $dataCount = $responseCount->count();
               $response =  $response ->limit($limit)
                   ->orderBy('users.id', 'desc')
                   ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                   ->get();
           }

       } else if($data['skip'] != null){
           if($searchInput){
               $response =  $this::whereIn('stage',$data['status']);
               $response = $response->where(function($query) use ($searchInput){
                   $query->orWhere('users.name', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('users.email', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('users.status', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('experiences.experience', 'LIKE', '%' . $searchInput . '%')
                       ->orWhere('positions.name', 'LIKE', '%' . $searchInput . '%');
               });

               $response = $response->join('users', 'users.id', '=', 'candidates.id_user')
                   ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                   ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                   ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                   ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                   ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
               $responseCount = $response;
               $dataCount = $responseCount->count();
               $response =  $response
                   ->offset($data['skip'])
                   ->limit($limit)
                   ->orderBy('users.id', 'desc')
                   ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                   ->get();
           }else{
               $response = $this::whereIn('stage',$data['status'])
                   ->join('users', 'users.id', '=', 'candidates.id_user')
                   ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                   ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                   ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                   ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                   ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
               $responseCount = $response;
               $dataCount = $responseCount->count();
               $response =  $response
                   ->orderBy('users.id', 'desc')
                   ->offset($data['skip'])
                   ->limit($limit)
                   ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                   ->get();
           }
       }



       return array($response,$dataCount);
   }
   public function viewCandidateBytags($data)
    {
        $response = null;
        $dataCount = null;
        $limit = $data['limit'] ?? 20;
        $searchInput = trim($data['search_input']);

        if ($data['skip'] == null) {
            if($searchInput){
                $response = BaseCandidateTagModel::whereIn('id_tag',$data['id_tag']);
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

                $response = $response
                    ->join('candidates', 'candidates.id_user', '=', 'candidate_tags.candidate_id')
                    ->join('users', 'users.id', '=', 'candidate_tags.candidate_id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
                $responseCount = $response;
                $dataCount = $responseCount->count();
                $response = $response->orderBy('users.id', 'desc')
                    ->limit($limit)
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get()
                    ->toarray();
            }else{
                $response = BaseCandidateTagModel::whereIn('id_tag',$data['id_tag'])
                    ->join('candidates', 'candidates.id_user', '=', 'candidate_tags.candidate_id')
                    ->join('users', 'users.id', '=', 'candidate_tags.candidate_id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
                $responseCount = $response;
                $dataCount = $responseCount->count();
                $response = $response
                    ->orderBy('users.id', 'desc')
                    ->limit($limit)
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get()
                    ->toarray();
            }

        } else if($data['skip'] != null){
            if($searchInput){
            $response = BaseCandidateTagModel::whereIn('id_tag',$data['id_tag']);
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

            $response = $response
                ->join('candidates', 'candidates.id_user', '=', 'candidate_tags.candidate_id')
                ->join('users', 'users.id', '=', 'candidate_tags.candidate_id')
                ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
            $responseCount = $response;
            $dataCount = $responseCount->count();
            $response = $response->orderBy('users.id', 'desc')
                ->offset($data['skip'])
                ->limit($limit)
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                ->get()
                ->toarray();
        }else{
                $response = BaseCandidateTagModel::whereIn('id_tag',$data['id_tag'])
                    ->join('candidates', 'candidates.id_user', '=', 'candidate_tags.candidate_id')
                    ->join('users', 'users.id', '=', 'candidate_tags.candidate_id')
                    ->leftJoin('positions', 'positions.id', '=', 'candidates.position')
                    ->leftJoin('experiences', 'experiences.id', '=', 'candidates.experience')
                    ->leftJoin('qualifications', 'qualifications.id', '=', 'candidates.qualification')
                    ->leftJoin('rounds', 'rounds.id', '=', 'candidates.stage')
                    ->leftJoin(DB::raw("(SELECT candidate_id,grade FROM candidate_feedback WHERE created_at in (SELECT MAX(created_at) from candidate_feedback GROUP BY candidate_id)) as candidate_feedback_table"), 'candidates.id_user', '=', 'candidate_feedback_table.candidate_id');
                $responseCount = $response;
                $dataCount = $responseCount->count();
                $response = $response
                    ->orderBy('users.id', 'desc')
                    ->offset($data['skip'])
                    ->limit($limit)
                    ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.password', 'users.phone_no', 'users.image', 'users.email', 'rounds.round_name as stage', 'candidates.cover_letter', 'qualifications.qualification', 'experiences.experience', 'positions.name as position','candidates.created_at', 'candidates.updated_at','candidates.date_time','users.dob','users.subjects','candidate_feedback_table.grade')
                    ->get()
                    ->toarray();
            }
        }
        return array($response,$dataCount);
    }


}
