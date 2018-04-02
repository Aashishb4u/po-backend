<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 13/6/17
 * Time: 8:43 PM
 */

namespace App\Models;
use App\BaseModels\BaseExpectedJoinerTemplateModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use File;
use App\BaseModels\BaseExpectedJoinersModel;

class ExpectedJoinersModel  extends BaseExpectedJoinersModel
{
    public function addExpectedJoinerDetails($user)
    {
        $returnData = null;
        $this->email = $user['email'];
        $this->name = $user['name'];
        $this->status = $user['status'];
        $this->college_name = $user['college_name'];
        $this->phone_no = $user['phone_no'];
        $this->stage = $user['stage'];
        $this->qualification = $user['qualification'];
        $this->batch = $user['batch'];

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

    public function getExpectedJoinerDetails($user_id)
    {
        $response = $this:: where('expected_joiners.id', $user_id)
            ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
            ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
            ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
            ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                'expected_joiners.status','expected_joiners.updated_at','rounds.id as stage'
                ,'qualifications.id as qualification','batch.id as batchId','expected_joiners.id')
            ->first();
        return $response;
    }

    public function getExpectedJoinerDetailsByEmail($email)
    {
        $response = null;
            $response = $this::where('email', $email)->first();
            if (!empty($response)) {
                return $response;
            } else {
                return ApiConstant::DATA_NOT_FOUND;
            }
    }

    public function getExpectedJoinerDetailsByID($ID)
    {
        $response = null;
        $response = $this::where('id', $ID)->first();
        if (!empty($response)) {
            return $response;
        } else {
            return ApiConstant::DATA_NOT_FOUND;
        }
    }

    public function editExpectedJoinerDetails($data)
    {
        $result = null;
        $result = $this::where('id', $data['id'])->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'college_name' => $data['college_name'],
            'status' => $data['status'],
            'phone_no' => $data['phone_no'],
            'stage' => $data['stage'],
            'qualification' => $data['qualification'],
            'batch' => $data['batch']
        ]);
        return $result;
    }

    public function viewExpectedJoiners($data)
    {
        $searchInput = $data['search_input'];
        $response = null;
        if ($data['skip'] == null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit(20)
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                         ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                     ->count();
            } else {
                $response = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->limit(20)
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] == null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit(20)
                    ->offset($data['skip'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount =  $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->offset($data['skip'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    -> count();
            } else {
                $response = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->limit(20)
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->limit(20)
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->count();
            }
        }

        if ($data['skip'] != null && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->limit($data['limit'])
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    -> count();
              } else {
                $response = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->count();
            }
        }

        if ($data['skip'] == 0 && $data['limit'] != null) {
            if ($searchInput != null) {
                $response = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::where('name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('status', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('college_name', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('qualifications.qualification', 'LIKE', '%' . $searchInput . '%')
                    ->orWhere('rounds.round_name', 'LIKE', '%' . $searchInput . '%')
                    ->join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    -> count();
            } else {
                $response = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->get();

                $dataCount = $this::join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification','expected_joiners.id')
                    ->count();
            }
        }

        return array($response,$dataCount);
    }

    public function viewExpectedJoinerDetailsByBatch($data)
    {
        $batchId = $data['batch'];
        $response = null;
        if ($data['skip'] == null && $data['limit'] == null) {
                $response = $this::where('expected_joiners.batch',$batchId)->
                     join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->limit(20)
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.id','expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->get();

                $dataCount = $this::where('expected_joiners.batch',$batchId)->
                     join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->count();

        }

        if ($data['skip'] != null && $data['limit'] == null) {

                $response = $this::where('expected_joiners.batch',$batchId)->
                join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->limit(20)
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->get();

                $dataCount = $this::where('expected_joiners.batch',$batchId)->
                join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->count();
        }

        if ($data['skip'] != null && $data['limit'] != null) {

                $response = $this::where('expected_joiners.batch',$batchId)->
                join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->get();

                $dataCount = $this::where('expected_joiners.batch',$batchId)->
                join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->count();

        }

        if ($data['skip'] == 0 && $data['limit'] != null) {

                $response = $this::where('expected_joiners.batch',$batchId)->
                join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->offset($data['skip'])
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->get();

                $dataCount = $this::where('expected_joiners.batch',$batchId)->
                     join('batch', 'expected_joiners.batch', '=', 'batch.id')
                    ->leftjoin('rounds', 'rounds.id', '=', 'expected_joiners.stage')
                    ->leftjoin('qualifications', 'qualifications.id', '=', 'expected_joiners.qualification')
                    ->orderBy('expected_joiners.id', 'desc')
                    ->limit($data['limit'])
                    ->select('expected_joiners.college_name','batch.batch','expected_joiners.created_at',
                        'expected_joiners.email','expected_joiners.name','expected_joiners.phone_no',
                        'expected_joiners.status','expected_joiners.updated_at','rounds.round_name as stage'
                        ,'qualifications.qualification')
                    ->count();
        }

        return array($response,$dataCount);
    }

    public function deleteExpectedJoinerDetails($id)
    {
        $response = $this::where('id', $id)->delete();
        return $response;
    }

    public function isTemplateSend($template_id, $user_id)
    {
        $result = BaseExpectedJoinerTemplateModel::where('id_template', $template_id)->where('id_expected_joiner', $user_id)->first();
        if (empty($result))
        {
            return 1;
        }else{
            return 0;
        }
    }

    public function updateExpectedJoinerStatus($id,$status)
    {
        $response = $this::where('id',$id)->update([
            'stage'=> $status
        ]);
        return $response;
    }
}