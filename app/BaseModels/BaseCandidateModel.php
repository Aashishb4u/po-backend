<?php
/**
 * Created by PhpStorm.
 * User: lt-44
 * Date: 5/1/17
 * Time: 7:49 PM
 */

namespace App\BaseModels;


use Illuminate\Database\Eloquent\Model;

class BaseCandidateModel extends Model
{
    protected $table = 'candidates';

    public function getUser(){
        return $this->belongsTo('App\BaseModels\BaseUserModel', 'id_user');
    }
}