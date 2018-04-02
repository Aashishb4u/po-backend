<?php
/**
 * Created by PhpStorm.
 * User: supravat
 * Date: 28/12/15
 * Time: 5:56 PM
 */

namespace App\Helpers;
//use Mailgun\Mailgun;
use App\Models\SenderModel;
use SendGrid\Mail;
use App\Models\StaticReplacementModel;
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Carbon\Carbon;




class AppUtility {

    /**
     * @param $str
     * @return bool
     */

    public static function sendTestMail($template = null, $subject = null, $body = null, $to = null, $fromName = null, $fromEmail = null, $attachment =null, $path=null){

        Mail::send($template, array('data' => $body), function ($message) use ($to,$subject,$fromEmail,$fromName,$attachment,$path){
            $message->from($fromEmail, $fromName);
            $message->to($to)->subject($subject);
            if($attachment){
                $message->attach($attachment);
            }
        });
        if(count(Mail::failures()) > 0){
            $result  = false;
        }
        else{
            $result  = true;
        }

        return $result;
    }

    public static function getConstants( $text , $start, $end){
        $regex = "/".$start."([a-zA-Z0-9_.@-]*)".$end."/";
        preg_match_all($regex,$text, $matches);
        return $matches[1];
    }

    public static function dateDifference($createdDate)
    {
        $todayDate = Carbon::now();
        $currentDate = Carbon::parse($todayDate);
        $now = date_format($currentDate , 'Y-m-d H:i:s');
        $date1 = strtotime($now);
        $date2 = strtotime($createdDate);
        $dateDiff = ($date1 - $date2)/60;
        return round($dateDiff);
    }

    public static function extract_unit($string, $start, $end)
    {
        $pos = stripos($string, $start);
        if($pos > 0)
        {
            $str = substr($string, $pos);
            $str_two = substr($str, strlen($start));
            $second_pos = stripos($str_two, $end);

            $str_three = substr($str_two, 0, $second_pos);

            $unit = trim($str_three);
            return $unit;
        }
        else{
            return 0;
        }
    }
    public static function renderEmail($templateContent){
        $mailText = $templateContent;
        $constants = AppUtility::getConstants($mailText, '__', '__');
        foreach ($constants as $index=>$constant ) {
            $settingModelObj = new StaticReplacementModel();
            $result = $settingModelObj->isStaticDataAlreadyExist($constant);
            if(!empty($result)){
                $constantsArray[$constant] = $result['value'];
            }else{
                $constantsArray[$constant] = null;
            }
            $newMail = str_replace( '__'.$constant.'__', $constantsArray[$constant] ,$mailText);
            $mailText = $newMail;
        }
        return $mailText;
    }

    public static function renderTemplate($templateData,$constantValues)
    {
        $mailText = $templateData;
        $values['USER_NAME'] = $constantValues['USER_NAME'] ?? '';
        $values['USER_PASSWORD'] = $constantValues['USER_PASSWORD']?? '';
        $values['USER_EMAIL'] = $constantValues['USER_EMAIL']?? '';
        $values['DATE_TIME_INFROMATION'] = $constantValues['DATE_TIME_INFROMATION']?? '';
        $values['BCRYPT_ID'] = $constantValues['BCRYPT_ID'] ?? '';
        $constants = AppUtility::getConstants($mailText, '%%', '%%');
        foreach ($constants as $index=>$constant ) {
            if(!empty($values[$constant])){
                $newMail = str_replace( '%%'.$constant.'%%', $values[$constant] ,$mailText);
            }else{
                $newMail = str_replace( '%%'.$constant.'%%', null ,$mailText);
            }
            $mailText = $newMail;
        }
        return $mailText;
    }

     // send mail using Sparkpost
     public static function sendEmail( $subject = null, $body = null, $to_email = null,$hash, $fileName)
     {
         $senderModelObj = new SenderModel();
         $basePath = public_path('purchase_orders/');
         $filename = $fileName;
         $fileType = mime_content_type($basePath.$filename);
         $fileData = base64_encode(file_get_contents($basePath.$filename));
         $senderDetails['sender_name'] = 'Tudip Technologies';
         $senderDetails['sender_email'] = 'shital.mahajan@tudip.com';
         $httpClient = new GuzzleAdapter(new Client());
         $sparky = new SparkPost($httpClient, ['key' => '75f3c0b79b63514454bc72c3fcdee31369d2180e']);
         $sparky->setOptions(['async' => false]);
         $results = $sparky->transmissions->post([
             'content' => [
                 'from' => [
                     'name' => $senderDetails['sender_name'],
                     'email' => $senderDetails['sender_email'],
                 ],
                 'attachments' => [
                     [
                         'name' => $filename,
                         'type' => $fileType,
                         'data' => $fileData,
                     ],
                 ],
                 'subject' => $subject,
                 'html' => $body,
                 'reply_to'  => '<joinus+'.$hash.'@tudip.com>'
             ],
             'recipients' => [
                 ['address' => ['email' => $to_email]]
             ],
             'bcc' => [
                 [
                     'address' => [
                         'name' => 'Purchase Order',
                         'email' => 'shital.mahajan@tudip.com',
                     ],
                 ],
             ],
             'cc' => [
                 [
                     'address' => [
                         'name' => 'Tudip Technologies',
                         'email' => 'shital.mahajan@tudip.com',
                     ],
                 ],
             ],
         ]);
         if ($results) {
             return true;
         }
     }




    public static function isNotSetOrEmpty($str)
    {
        $return = false;
        if($str == null)
        {
            $return = true;
        }
        return $return;
    }

    /**
     * @param $firstName
     * @param $length
     * @return int
     */
    public static function compareStringLength($firstName, $length)
    {
        $returnVal = 0;
        if(self::checkStringLength($firstName) < $length)
        {
            $returnVal = -1;
        }
        elseif(self::checkStringLength($firstName) > $length)
        {
            $returnVal = 1;
        }
        return $returnVal;
    }

    /**
     * @param $str
     * @return int
     */
    public static function checkStringLength($str)
    {
        $length = strlen($str);
        return $length;
    }

    /**
     * @param $email
     * @return bool
     */
    public static function check_email_address($email) {
        //$status = true;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $status = true;
        } else {
            $status = false;
        }

//        $space_pos = strpos($email,' ');
//        $at_pos = strpos($email,'@');
//        $stop_pos = strpos($email,'.', $at_pos);
//        $more_stop = strpos($email, '.', ($stop_pos + 1));
//
//        if(($at_pos === false) || ($stop_pos === false) || $more_stop || $space_pos) {
//            $status = false;
//        }
//        else {
//            if($stop_pos < $at_pos) {
//                $status = false;
//            }
//            else {
//                if (($stop_pos - $at_pos) == 1) {
//                    $status = false;
//                }
//            }
//        }
        return $status;
    }

    /**
     * @param $errorCode
     * @return int
     */
    public static function getHTTPStatusCodeForErrorCode($errorCode)
    {
        $statusCode = ApiConstant::HTTP_RESPONSE_CODE_SUCCESS;
        if (isset(ApiConstant::$httpErrorCodeMap[$statusCode]))
        {
            $statusCode = ApiConstant::$httpErrorCodeMap[$errorCode];
        }
        return $statusCode;
    }

    /**
     * @param $errorCode
     * @return mixed
     */
    public static function getMessageForErrorCode($errorCode)
    {
        $returnData = $errorCode;
        if (isset(ApiConstant::$english[$errorCode]))
        {
            $returnData = ApiConstant::$english[$errorCode];
        }
        return $returnData;
    }

    public static function createSalt($email)
    {
        $salt = md5($email);
        return $salt;

    }

    public static function getPasswordHash($salt, $password)
    {
        $hash = md5($salt.$password);
        return $hash;
    }

    public static function isValidPreviousDate($date)
    {
        $returnVal = false;
        $date = date_create($date);
        $preDate = date_format($date, ApiConstant::DATE_FORMAT);
        $currentDate = date(ApiConstant::DATE_FORMAT);
        if($preDate < $currentDate)
        {
            $returnVal = true;
        }
        return $returnVal;
    }

    public static function isValidDateFormat($date)
    {
        $returnVal = false;
        if (preg_match(ApiConstant::DATE_FORMAT_GRAMMAR, $date))
        {
            $returnVal = true;
        }
        return $returnVal;
    }

    public static function strPosWithNeedleArrray($haystack, $needle) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $what) {
            if(($pos = strpos($haystack, $what))!==false) return $pos;
        }
        return false;
    }

    public static function forbiddenDomains(){
        return array("tudip.nl", "tudip.com");
    }

    public static function getInbetweenStrings($start, $end, $str){
        $matches = array();
        $regex = "/$start([a-zA-Z0-9_.@-]*)$end/";
        preg_match_all($regex, $str, $matches);
        return $matches[1];
    }

    public static function extract_emails_from($string){
        preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);
        return $matches[0];
    }
    /**
     * @return string
     */
    public static function createUserAuthToken()
    {
        $startTime = microtime();
        $userAuthToken = md5($startTime);
        return $userAuthToken;
    }

    public static function trimContent($str)
    {
        return trim($str);
    }

    public static function getDateFromDateObject($date)
    {
        if(is_object($date)){
            $date = (array)$date;
            $date = $date['date'];
            $date = explode('.', $date);
            $date = $date[0];
        }
        return $date;
    }

    public static function getResetToken()
    {
        return strtolower(str_random(8,'1234567890abcdefghijklmnopqrstuvwxyz'));
    }

    public static function dateFormat($date)
    {
        $date = date_create($date);
        $formattedDate = date_format($date, ApiConstant::DATE_FORMAT);
        return $formattedDate;
    }

    public static function mergeArray($primaryArray, $secondaryArray)
    {
        foreach($secondaryArray as $key => $data)
        {
            $primaryArray[$key] = $data;
        }
        return $primaryArray;
    }

    public static function prepareUserTableData($inputs)
    {
        $returnArray = array();
        $columns = array('first_name', 'last_name', 'birth_date', 'gender');
        foreach($inputs as $key => $input)
        {
            if(in_array($key, $columns))
            {
                $returnArray[$key] =  $input;
            }
        }
        if(isset($inputs['birth_date']))
        {
            $inputs['birth_date'] = AppUtility::reverseDateUsFormat($inputs['birth_date']);
            $returnArray["age"] = date_diff(date_create($inputs['birth_date']), date_create('today'))->y;
            $returnArray["birth_date"] = AppUtility::reverseDateUsFormat($returnArray['birth_date']);
        }

        return $returnArray;
    }

    public static function prepareAddressTableData($inputs)
    {
        $returnArray = array();
        $columns = array('contact', 'street', 'city', 'district', 'state', 'country', 'landmark', 'zip');
        foreach($inputs as $key => $input)
        {
            if(in_array($key, $columns))
            {
                $returnArray[$key] =  $input;
            }
        }
        return $returnArray;
    }

    public static function prepareProfileTableData($inputs)
    {
        $returnArray = array();
        $columns = array('fitness_interest', 'bmi', 'body_fat', 'do_smoke', 'exercise_per_week');
        foreach($inputs as $key => $input)
        {
            if(in_array($key, $columns))
            {
                $returnArray[$key] =  $input;
            }
        }
        return $returnArray;
    }

    public static function isValidBase64($imageBase64)
    {
        $returnVal = false;
        if (base64_encode(base64_decode($imageBase64, true)) === $imageBase64)
        {
            $returnVal = true;
        }
        return $returnVal;
    }

    public static function reverseDateUsFormat($date)
    {
        if($date)
        {
            $array = explode('-', $date);
            $newFormat = $array[1].'-'.$array[0].'-'.$array[2];
            $dateObj = date_create($newFormat);
            $formattedDate = date_format($dateObj, ApiConstant::DATE_FORMAT);
            return $formattedDate;
        }
    }

    public static function dateUsFormat($date)
    {
        if($date)
        {
            $array = explode(' ', $date);
            $dateOnly = explode('-', $array[0]);
            $newFormat = $dateOnly[1].'-'.$dateOnly[2].'-'.$dateOnly[0].' '.$array[1];
            return $newFormat;
        }
    }

    public static function calculateTimePassed($date)
    {
        $currentTime = new \DateTime();
        $pastDate = date_create($date);

        $postedBefore = date_diff($pastDate, $currentTime);
        $dateDiff = array('year' => $postedBefore->y,
            'month' => $postedBefore->m,
            'day' => $postedBefore->d,
            'hour' => $postedBefore->h,
            'minute' => $postedBefore->i,
            'second' => $postedBefore->s,
            'total_days' => $postedBefore->days,
        );
        return $dateDiff;
    }

    public static function dump($data)
    {
        echo'<pre>';
        print_r($data);
        echo'</pre>';
        die;
    }

    public static function validBase64Params($imageBase64)
    {
        $error = false;
        if(AppUtility::isNotSetOrEmpty($imageBase64))
        {
            $error = ApiConstant::EMPTY_BASE64;
        }
        if(!AppUtility::isValidBase64($imageBase64)){
            $error = ApiConstant::INVALID_BASE64;
        }
        return $error;
    }

    public static function formatPhoneNumber($phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);

        if(strlen($phoneNumber) > 10) {
            $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
            $areaCode = substr($phoneNumber, -10, 3);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
        }
        else if(strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);

            $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
        }
        else if(strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);

            $phoneNumber = $nextThree.'-'.$lastFour;
        }

        return $phoneNumber;
    }
    public static function getCoordinates($address){
        $address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern
        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";
        try{$response = file_get_contents($url);
            if(!empty($response)){
                $json = json_decode($response,TRUE); //generate array object from the response from the web
//        print_r($json['results'][0]['geometry']['location']);
                return ($json['results'][0]['geometry']['location']);
            }
        }
        catch(\Exception $e){
            return ApiConstant::UNABLE_TO_RETRIEVE_ADDRESS;
        }
    }

    public static function byzipcode($zip){
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.$zip;
        try{
            $response = file_get_contents($url);
            if(!empty($response)){

                $completeAddress['zipcode'] = $zip;
                $json = json_decode($response,TRUE); //generate array object from the response from the web
                if($json['status']=='OK'){
                    foreach($json as $data){
                        if($data[0]['address_components']){

                            $address = $data[0]['address_components'];
                            foreach($address as $break){
                                if($break['types'][0] == "administrative_area_level_1" ){
                                    $completeAddress['state'] = $break['long_name'];
                                }
                                if($break['types'][0] == "locality"){
                                    $completeAddress['city'] = $break['long_name'];
                                }else {
                                    if ($break['types'][0] == "administrative_area_level_2") {
                                        $completeAddress['city'] = $break['long_name'];
                                    }
                                }
                                if($break['types'][0] == "country" ){
                                    $completeAddress['country']  = $break['long_name'];
                                }
                            }
                            return $completeAddress;
                        }
                    }
                }else{
                    return null;
                }


            }
        }
        catch(\Exception $e){
            return ApiConstant::UNABLE_TO_RETRIEVE_ADDRESS;
        }
    }

    public static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' kB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}