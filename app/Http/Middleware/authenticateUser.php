<?php
namespace App\Http\Middleware;

use App\Helpers\ApiConstant;
use App\Http\Controllers\AppController;
use App\Models\User;
use App\Models\UserAuthModel;
use Closure;

class authenticateUser extends AppController
{
    public function handle($request, Closure $next){
        $headerInfo = $request->header();
        $authorization = $headerInfo['authorization'][0] ?? '';
//        print_r($authorization);die;
        $token = explode(' ', $authorization);
        $token = $token[1] ?? '';
        $user = new UserAuthModel();
        $authenticatedUser = $user->getUserByAuthToken($token);
        if (!$authenticatedUser) {
            return $this->returnableResponseData(array(), ApiConstant::AUTHENTICATION_FAILED);
        }
        $request->auth_token = $token;
        $uri = $request->getBaseUrl();
        $host = $request->getHost();
        $basePath = 'http://' . $host . $uri;
        $request->rootUrl = $basePath;
        $request->user = $authenticatedUser;
        return $next($request);
    }



}