<?php

namespace App\Http\Controllers;

use App\Helpers\ApiConstant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;


class MailController extends Controller
{
    public function myTestMail($saveEmail, Request $request)
    {
        $myEmail = $saveEmail;
        Mail::to($myEmail)->send(new TestMail($request));
        return ApiConstant::EMAIL_SENT;
    }
}
