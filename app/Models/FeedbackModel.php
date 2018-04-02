<?php

namespace App\Models;

use App\BaseModels\BaseCandidateModel;
use App\BaseModels\BaseFeedbackModel;
use App\BaseModels\BaseUserModel;

class FeedbackModel extends BaseFeedbackModel
{
    public function saveFeedbackData($feedbackData)
    {
        $this->email = $feedbackData['email'];
        $this->mathematics = $feedbackData['mathematics'];
        $this->mathematics_nots = $feedbackData['mathematics_nots'];
        $this->basic_science = $feedbackData['basic_science'];
        $this->basic_science_nots = $feedbackData['basic_science_nots'];
        $this->communication = $feedbackData['communication'];
        $this->communication_nots = $feedbackData['communication_nots'];
        $this->logical_thinking = $feedbackData['logical_thinking'];
        $this->logical_thinking_nots = $feedbackData['logical_thinking_nots'];
        $this->problem_solving_capability = $feedbackData['problem_solving_capability'];
        $this->problem_solving_capability_nots = $feedbackData['problem_solving_capability_nots'];
        $this->ds_and_algo = $feedbackData['ds_and_algo'];
        $this->ds_and_algo_nots = $feedbackData['ds_and_algo_nots'];
        $this->programming = $feedbackData['programming'];
        $this->programming_nots = $feedbackData['programming_nots'];
        $this->database = $feedbackData['database'];
        $this->database_nots = $feedbackData['database_nots'];
        $this->learning_ability = $feedbackData['learning_ability'];
        $this->learning_ability_nots = $feedbackData['learning_ability_nots'];
        $this->can_work_with_us = $feedbackData['can_work_with_us'];
        $this->can_work_with_us_nots = $feedbackData['can_work_with_us_nots'];
        $this->feedback = $feedbackData['feedback'];
        $this->id_interviewer = $feedbackData['id_interviewer'];
        $this->id_candidate = $feedbackData['id_candidate'];

        if($this->save())
        {
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function viewAllFeedback($id_candidate)
    {
        $response1 = BaseFeedbackModel::where('id_candidate', $id_candidate)
            ->join('users', 'feedbacks.id_candidate', '=', 'users.id')
            ->select('feedbacks.*', 'users.name')
            ->first();
        if($response1)
        {
            $response2 = BaseFeedbackModel::where('id_candidate', $id_candidate)
                ->join('users', 'feedbacks.id_interviewer', '=', 'users.id')
                ->select('users.name')
                ->first();
        }
        $response1['interviewer_name'] = $response2->name;

        return $response1;
    }

}
