<?php
/**
 * Created by PhpStorm.
 * User: lt-66
 * Date: 14/7/17
 * Time: 5:47 PM
 */

namespace App\Models;


use App\BaseModels\BaseCandidateFeedbackModel;
use App\BaseModels\BaseEmailModel;
use App\Helpers\ApiConstant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CandidateFeedbackModel extends BaseCandidateFeedbackModel
{
    public function saveCandidateFeedback($data)
    {

        $returnData = null;
        $this->feedback = $data['feedback'];
        $this->candidate_id = $data['candidate_id'];
        $this->grade = $data['grade']??'';
        $this->recruiter_id = $data['recruiter_id'];
        $this->feedback_type = $data['feedback_type'];
        if ($this->save()) {
            $returnData = ApiConstant::ADDED_FEEDBACK;
        } else {
            $returnData = ApiConstant::DATA_NOT_SAVED;
        }
        return $returnData;
    }

    public function getCandidateFeedback($id)
    {
        $response = $this::where('candidate_id', $id)
            ->join('users', 'users.id', '=', 'candidate_feedback.recruiter_id')
            ->select('candidate_feedback.id','candidate_feedback.feedback', 'candidate_feedback.candidate_id','candidate_feedback.grade','candidate_feedback.created_at','users.name as recruiter_name','users.image as recruiter_image','candidate_feedback.feedback_type')
            ->orderBy('candidate_feedback.feedback_type', 'desc')
            ->orderBy('candidate_feedback.id', 'desc')
            ->get();
        return $response;
    }

    public function getInterviewerWork($data)
    {
        $response = null;
        $total = null;
        $from_date = Carbon::parse($data['from_date']);
        $from_dateformate = date_format($from_date , 'Y-m-d');
        $to_date =  Carbon::parse($data['to_date']);
        $to_dateformate = date_format($to_date ,'Y-m-d');
        $response = $this::whereBetween(DB::raw("(DATE_FORMAT(candidate_feedback.created_at,'%Y-%m-%d'))"),[$from_dateformate , $to_dateformate])
            ->where('candidate_feedback.feedback_type','2')
            ->join('users', 'users.id', '=', 'candidate_feedback.recruiter_id')
            ->orderBy('candidate_feedback.recruiter_id', 'asc')
            ->groupBy('candidate_feedback.recruiter_id')
            ->select('candidate_feedback.recruiter_id', 'users.name as recruiter_name',DB::raw('count(candidate_feedback.recruiter_id) as count'))
            ->get();
        $total = $this::whereBetween(DB::raw("(DATE_FORMAT(candidate_feedback.created_at,'%Y-%m-%d'))"),[$from_dateformate , $to_dateformate])
            ->where('candidate_feedback.feedback_type','2')
            ->join('users', 'users.id', '=', 'candidate_feedback.recruiter_id')
            ->orderBy('candidate_feedback.recruiter_id', 'asc')
            ->select('candidate_feedback.recruiter_id', 'users.name as recruiter_name',DB::raw('count(candidate_feedback.recruiter_id) as count'))
            ->count();
        return array($response,$total);
    }

    public function deleteCandidateFeedback($id)
    {
        $response = $this::where('id', $id)->delete();
        return $response;
    }
}