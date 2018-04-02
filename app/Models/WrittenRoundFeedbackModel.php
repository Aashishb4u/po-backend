<?php

namespace App\Models;

use App\BaseModels\BaseWrittenRoundFeedbackModel;

class WrittenRoundFeedbackModel extends BaseWrittenRoundFeedbackModel
{
    public function saveWrittenRoundFeedback($feedbackData)
    {
        $this->email = $feedbackData['email'];
        $this->mathematics = $feedbackData['mathematics'];
        $this->english = $feedbackData['english'];
        $this->data_structure = $feedbackData['data_structure'];
        $this->c_programming = $feedbackData['c_programming'];
        $this->java = $feedbackData['java'];
        $this->data_base = $feedbackData['data_base'];
        $this->total = $feedbackData['total'];
        $this->feedback = $feedbackData['feedback'];
        return $this->save();
    }

    public function viewWrittenRoundFeedback()
    {
        return $this::all();
    }

    public function editWrittenRoundFeedback($feedbackData)
    {
        $result = $this::where('id', $feedbackData['id'])->update([
            'email' => $feedbackData['email'],
            'mathematics' => $feedbackData['mathematics'],
            'english' => $feedbackData['english'],
            'data_structure' => $feedbackData['data_structure'],
            'c_programming' => $feedbackData['c_programming'],
            'java' => $feedbackData['java'],
            'data_base' => $feedbackData['data_base'],
            'total' => $feedbackData['total'],
            'feedback' => $feedbackData['feedback']
        ]);
        return $result;
    }

    public function getWrittenRoundFeedbackById($id)
    {
        return $this::where('id',$id)->first();
    }

}
