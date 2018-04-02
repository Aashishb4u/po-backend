<?php

namespace App\Models;

use App\BaseModels\BaseTemplateModel;
use App\Helpers\ApiConstant;
use Illuminate\Support\Facades\DB;

class TemplateModel extends BaseTemplateModel
{
    public function add($template)
    {
        $returnData = null;
        try {
            if ($template['id'] != null) {
                $templateId = $template['id'];
                $subject = $template['subject'];
                $content = $template['content'];
                $name = $template['name'];
                $id_round = $template['id_round'];
                $description = $template['description'];
                $update = BaseTemplateModel::where('id', $templateId)->update([
                    'name' => $name,
                    'content' => $content,
                    'subject' => $subject,
                    'description' => $description,
                    'id_round' => $id_round,
                ]);

                if ($update) {
                    $returnData = ApiConstant::TEMPLATE_UPDATED_SUCCESSFULLY;
                } else {
                    $returnData = ApiConstant::DATA_NOT_SAVED;
                }
            } else {
                $this->subject = $template['subject'];
                $this->content = $template['content'];
                $this->name = $template['name'];
                $this->id_round = $template['id_round'];
                $this->description = $template['description'];
                if ($this->save()) {
                    $returnData = ApiConstant::TEMPLATE_CREATED_SUCCESSFULLY;
                } else {
                    $returnData = ApiConstant::DATA_NOT_SAVED;
                }
            }
        } catch (\Exception $e) {
        }
        return $returnData;
    }

    public function viewTemplate()
    {
        $response = BaseTemplateModel::join('rounds','rounds.id','=','templates.id_round')
             ->orderBy('templates.id', 'desc')
            ->select('templates.*','rounds.round_name')->get();
        return $response;
    }

    public function viewTemplateForGraph()
    {   $totalCount = 0;
        $response = BaseTemplateModel::join('rounds','rounds.id','=','templates.id_round')
            ->orderBy('templates.id', 'desc')
            ->select('templates.name', DB::raw('0 as total'))
            ->groupBy('templates.id')
            ->get();
        return array($response,$totalCount);
    }


    public function viewTemplateByRound($templateData)
    {
        $roundId = $templateData;
        $response = BaseTemplateModel::orderBy('templates.id', 'desc')->where('id_round', $roundId)->select('*')->get();
        return $response;
    }

    public function getTemplateById($templateId)
    {
        $response = BaseTemplateModel::where('templates.id', $templateId)->join('rounds','rounds.id','=','templates.id_round')
            ->select('templates.*','rounds.round_name')->first();
        return $response;

    }

    public function getTemplateDetailsById($templateId)
    {
        $response = BaseTemplateModel::where('id', $templateId)->first();
        return $response;
    }

    public function deleteTemplate($id)
    {

        $response = BaseTemplateModel::where('id', $id)->delete();
        if ($response) {
            $response = ApiConstant::TEMPLATE_DELETED;
        }

        return $response;
    }

}
