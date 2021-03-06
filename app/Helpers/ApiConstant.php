<?php
namespace App\Helpers;

class ApiConstant
{

    const AUTHENTICATION_FAILED = '-101';
    const PARAMETER_MISSING = '-102';
    const EXCEPTION_OCCURED = '-103';
    const TOKEN_EXPIRED = '-104';
    const ERROR_CODE_NOT_EXIST = '-105';
    const INVALID_REQUEST_TYPE = '-106';
    const DATA_NOT_SAVED = '-107';
    const DATA_NOT_FOUND = '-108';
    const STATE_NOT_FOUND = '-172';
    const EMPTY_FIRST_NAME = '-109';
    const EMPTY_LAST_NAME = '-110';
    const EMPTY_PASSWORD = '-111';
    const EMPTY_EMAIL = '-112';
    const EMPTY_STATE = '-113';
    const EMPTY_DISTRICT = '-114';
    const EMAIL_NOT_VALID = '-115';
    const FIRST_NAME_LENGTH_EXCEEDED = '-116';
    const LAST_NAME_LENGTH_EXCEEDED = '-117';
    const EMAIL_LENGTH_EXCEEDED = '-119';
    const EMPTY_BIRTHDATE = '-120';
    const WRONG_DATE_FORMAT = '-121';
    const INVALID_DATE = '-122';
    const EMAIL_ALREADY_EXIST = '-123';
    const CONTACT_ALREADY_EXIST = '-125';
    const INVALID_USERNAME_PASSWORD = '-124';
    const EMAIL_NOT_REGISTERED = '-126';
    const EMPTY_TOKEN = '-127';
    const RECORD_NOT_EXIST = '-130';
    const USER_HAS_NO_PRIVILEGES = '-131';
    const ROLE_NOT_ASSIGNED = '-132';
    const INVALID_BASE64 = '-133';
    const EMPTY_BASE64 = '-134';
    const EMPTY_TO_DATE='-144';
    const EMPTY_DESCRIPTION='-146';
    const INVALID_USERNAME = '-148';
    const FILE_NOT_FOUND = '-175';
    const SELECT_USERTYPE = '250';
    const MAIL_SENDING_IN_PROGRESS = '-252';
    const ERROR_EMAIL_UPDATE = '-256';
    const PASSWORD_WRONG = '-257';
    const ONLY_FOR_ADMIN='-258';
    const INVALID_PHONE_NUMBER_TYPE = '-259';
    const FAILED_TO_UPDATE_COMPANYID = '-260';
    const INVALID_ID='-261';
    const ERROR_NAME_NOT_SAVED='-264';
    const ID_NOT_FOUND='-266';
    const CONTACT_ADMIN='-268';
    const NOT_ABLE_TO_CREATE_RECRUTER ='-269';
    const APPLY_FAILED = '-270';
    const TEMPLATE_ALREADY_SENT = '-271';
    const INVALID_URL = '203';
    const FORMAT_NOT_SUPPORTED = '-272';
    const RESUME_FILE_FORMAT_NOT_SUPPORTED = '-273';
    const REJECTED_DUE_TO_DOMAIN = '102';
    const REJECTED_DUE_TO_NO_ATTACHMENT = '103';
    const REJECTED_DUE_TO_ALREADY_EMAIL = '104';
    const EMPTY_QUALIFICATION = '-274';
    const EMPTY_KEY_NAME = '-275';
    const EMPTY_VALUE = '-276';
    const DATA_ALREADY_EXIST= '-277';
    const TEMPLATE_NOT_DELETED= '-278';
    const LINK_ALREADY_USED= '-279';
    const EMPTY_DATE_TIME = '-280';
    const EMPTY_ROUND_ID = '-281';
    const EMPTY_ORGANISATION_NAME = '-282';
    const EMPTY_CITY_NAME = '-283';
    const EMPTY_REPRESENTATIVE_TYPE  = '-284';
    const EMPTY_LAST_INVITED_DATE = '-285';
    const EMPTY_BATCH= '-286';
    const EMPTY_SOURCE_NAME= '-287';
    const EMPTY_SOURCE_DISCRIPTION = '-288';
    const EMAIL_ALREADY_SENT = '-289';
    const TAG_NOT_EXIST = '-290';
    const ITEM_EXIST = '-291';
    const ITEM_UPDATE_FAILED = '-292';
    const TAG_EXIST = '-293';
    const ADD_PURCHASE_FAIL = '-294';
    const ADD_TERMS_FAIL = '-295';
    const VENDOR_UPDATE_FAIL = '-296';
    const EMAIL_NOT_FOUND = '-297';
    const CONTACT_EXIST = '-299';
    const ADD_PAYMENT_FAIL = '-298';
    const ITEM_USED_GREATER = '-299';
    const ITEM_Dependancy = '-300';
    const ITEM_Dependancy_VENDOR_ITEMS = '-301';
    const TAG_DEPENDENT_VENDOR = '-302';
    const TAG_DEPENDENT_ITEM = '-303';
    const VENDOR_DEPENDANCY = '-304';
    const NO_TERMS_ASSIGNED = '-305';

    const DATE_FORMAT = 'Y-m-d H:i:s';
    const DATE_ONLY_FORMAT = 'Y-m-d';
    const DATE_FORMAT_GRAMMAR = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/";
    const RESET_TOKEN_EXPIRY = 1440; //only minute value
    const UPLOAD_FILE_NAME = 'uploads';
    const NO_IMAGE_NAME = 'no_image.png';
    const DEFAULT_PAGE_NO = 1;
    const DEFAULT_LIMIT = 15;


    const HTTP_RESPONSE_CODE_SUCCESS = 200;
    const CREATED =201;
    const DEFAULT_ERROR_RESPONSE_CODE = 400;
    const HTTP_RESPONSE_CODE_FAILED_AUTHENTICATION = 401;
    const FIRST_NAME_LENGTH = 64;
    const LAST_NAME_LENGTH = 64;
    const PASSWORD_LENGTH = 128;
    const EMAIL_LENGTH = 128;
    const SUCCESS_CODE = 0;
    const ERROR_STATUS = -1;
    const NO_EMAIL_FOUND_FOR_SENDING_MAIL = '-251';
    const INVITATION_EMAIL_ALL_SENT = 'Invitation email already sent.';


    const RECRUITER_CREATED_SUCCESSFULLY = 'Recruiter added successfully.';
    const TAG_ADDED_SUCCESSFULLY = 'Tag Added Successfully.';
    const PAYMENT_ADDED_SUCCESSFULLY = 'Payment Details Added Successfully.';
    const PAYMENT_DELETED_SUCCESSFULLY = 'Payment Details Deleted Successfully.';
    const TAG_DOES_NOT_EXIST = 'Tag does not Exist';
    const ITEM_ADDED_SUCCESSFULLY = 'Item Added Successfully.';
    const TAG_ALREADY_EXIST = 'This Tag is Already Exist';
    const ITEM_ALREADY_EXIST = 'This Item is Already Exist';
    const VENDOR_ITEM_ALREADY_EXIST = ' is Already Exist';
    const INTERVIEWER_CREATED_SUCCESSFULLY = 'Interviewer added successfully.';
    const EXPECTED_JOINERS_CREATED_SUCCESSFULLY = 'Expected joiner added successfully.';
    const TPO_CREATED_SUCCESSFULLY = 'Tpo added successfully.';
    const LOGGED_IN_SUCCESSFULLY = 'Logged in successfully.';
    const CANDIDATE_DELETED ='Candidate deleted successfully.';
    const ITEM_QUANTITY_DELETED ='Item quantity deleted successfully.';
    const TERM_DELETED ='Term and Condition deleted successfully.';
    const EXPECTED_JOINER_DELETED ='Expected joiner deleted successfully.';
    const TPO_DELETED ='Tpo deleted successfully.';
    const CANDIDATE_FEEDBACK_DELETED ='Feedback deleted successfully.';
    const TEMPLATE_DELETED ='Template deleted successfully.';
    const LOGGED_OUT_SUCCESSFULLY = 'Logged out successfully.';
    const EMAIL_SENT_FOR_CHANGE_PASSWORD = 'Please check your mailbox.';
    const USER_CREATED_SUCCESSFULLY = 'User created successfully.';
    const PASSWORD_CHANGE_SUCCESSFULLY = 'Password changed successfully.';
    const UPDATED_SUCCESSFULLY = 'Information updated successfully.';
    const VENDOR_UPDATED_SUCCESSFULLY = 'Vendor updated successfully.';
    const VENDOR_ITEM_ADDED_SUCCESSFULLY = ' Added Successfully';
    const PURCHASE_ADDED_SUCCESSFULLY = ' Purchase Order Added Successfully';
    const PURCHASE_DOWNLOAD_SUCCESSFULLY = ' Purchase Order viewed Successfully';
    const PURCHASE_ADDED_FAILED = ' Purchase Order Added Successfully';
    const EXPECTED_JOINER_UPDATED_SUCCESSFULLY = 'Expected joiner details updated successfully.';
    const TPO_UPDATED_SUCCESSFULLY = 'Tpo details updated successfully.';
    const RECRUITER_UPDATED_SUCCESSFULLY = 'Recruiter updated successfully.';
    const INTERVIEWER_UPDATED_SUCCESSFULLY = 'Interviewer updated successfully.';
    const RECRUITER_DELETED_SUCCESSFULLY = 'Recruiter deleted successfully.';
    const INTERVIEWER_DELETED_SUCCESSFULLY = 'Interviewer deleted successfully.';
    const PROFILE_UPDATED_SUCCESSFULLY = 'Profile updated successfully.';
    const EMAIL_SENT = 'Invitation email sent.';
    const AMIGO = 'Amigo!';
    const CANDIDATE_TYPE ='candidate';
    const TPO_TYPE ='tpo';
    const EXPECTED_TYPE ='expectedJoiner';
    const ADDED_FEEDBACK = 'Feedback added successfully.';
    const UPDATED_FEEDBACK = 'Feedback updated successfully.';
    const CANDIDATE_MUTED_SUCCESSFULLY = 'Candidate muted successfully.';
//    const ITEM_ALREADY_EXIST = 'Item Already Exists';
    const ITEM_UPDATE_SUCCESSFULLY = 'Item Updated Successfully';


    //Email config
    const FROM_EMAIL = 'recruitment_team@tudip.com';
    const FROM_NAME = 'Recruitment Team';
    const PASSWORD_REQUEST_SUBJECT = 'Recruitment Process Reset Password request.';
    const FILE_TYPE = array("pdf","docx","doc","odt");

    //Error messages
    Const ERROR_PASSWORD_UPDATE = 'Unable to update user password, please try again.';
    Const ERROR_ITEM_UPDATE = 'Item Update Failed';
    const ERROR_LOGIN = 'Login failed, please try again.';
    Const ERROR_LOGOUT = 'Request timeout, please try again.';
    const TEMPLATE_CREATED_SUCCESSFULLY = 'Template Created Successfully.';
    const TEMPLATE_UPDATED_SUCCESSFULLY = 'Template Updated Successfully.';
    const SUCCESSFULLY_ADD = 'Successfully Signup.';
    const EMPTY_SUBJECT = 'Subect is empty.';
    const EMPTY_CONTENT = 'Content is empty.';
    const JOB_PROFILE_ADDED = 'Job profile added successfully.';
    const ROUND_ADDED = 'Round added successfully.';
    const JOB_PROFILE_DELETED = 'Job profile deleted successfully.';
    const ROUND_DELETED = 'Round deleted successfully.';
    const REJECTED_DOMAIN_DELETED = 'Rejected domain deleted successfully.';
    const JOB_EXPERIENCE_DELETED = 'Job experience deleted successfully.';
    const APPLY_SUCCESSFULLY = 'Applied successfully.';
    const VENDOR_CREATE_SUCCESS = 'Vendor created successfully.';
    const DELETED_SUCCESSFULLY = 'Deleted successfully.';
    const QUALIFICATION_NOT_FOUND ='Please Select Qualification.';
    const EXPERIENCE_NOT_FOUND ='Please Select Experience.';
    const POSITION_NOT_FOUND ='Please Select Position For Job.';
    const RESUME_NOT_FOUND ='Please Upload Resume.';
    const APPLICATION_STAGE_ADDED = 'Application Stage Addded Successfully.';
    const APPLICATION_STAGE_DELETED = 'Application Stage Deleted Successfully.';
    const UNAPPROVED_STATUS = 'PreActive';
    const REJECTED_DOMAIN_ADDED = 'Rejected Domain Added Successfully.';
    const QUALIFICATION_ADDED = 'Qualification added successfully.';
    const QUALIFICATION_DELETED = 'Qualification deleted successfully.';
    const TAG_DELETED = 'Tag Deleted Successfully.';
    const ITEM_DELETED = 'Item Deleted Successfully.';
    const PURCHASE_DELETED = 'Purchase Order Deleted Successfully.';
    const ITEM_ADDED = 'Item Added Successfully.';
    const DATA_ADDED = 'Data added successfully.';
    const DATA_DELETED = 'Data deleted successfully.';
    const EXPERIENCE_ADDED = 'Experience added successfully.';
    const DEFAULT_PAGINATION = 20;


    public static $english = array(
        ApiConstant::PARAMETER_MISSING => 'Missing parameter in method call.',
        ApiConstant::TOKEN_EXPIRED => 'Sorry, provided auth token code is expired.',
        ApiConstant::AUTHENTICATION_FAILED => 'User Authentication Failed.',
        ApiConstant::ERROR_CODE_NOT_EXIST => 'Provided Error Code Does Not Exist.',
        ApiConstant::INVALID_REQUEST_TYPE => 'Invalid request type.',
        ApiConstant::DATA_NOT_SAVED => 'Data not da',
        ApiConstant::DATA_NOT_FOUND => 'Record not found.',
        ApiConstant::STATE_NOT_FOUND => 'State not found.',
        ApiConstant::EMPTY_FIRST_NAME => 'Please enter first name.',
        ApiConstant::EMPTY_LAST_NAME => 'Please enter last name.',
        ApiConstant::EMPTY_PASSWORD => 'Please enter password.',
        ApiConstant::EMPTY_EMAIL => 'Please enter email address.',
        ApiConstant::EMAIL_NOT_VALID => 'Please enter valid email.',
        ApiConstant::WRONG_DATE_FORMAT => 'Invalid date format.',
        ApiConstant::INVALID_DATE => 'Invalid date, please enter past date.',
        ApiConstant::EMAIL_ALREADY_EXIST => 'This email is already registered.',
        ApiConstant::INVALID_USERNAME_PASSWORD => 'Invalid email id and password combination.',
        ApiConstant::INVALID_USERNAME => 'Email not found.',
        ApiConstant::EMAIL_NOT_REGISTERED => 'This email is not registered.',
        ApiConstant::EMPTY_TOKEN => 'Please enter reset token.',
        ApiConstant::RECORD_NOT_EXIST => 'Record not found.',
        ApiConstant::TAG_NOT_EXIST => 'these tags not found',
//        ApiConstant::ITEM_ALREADY_EXIST => 'Item already Exist',
        ApiConstant::USER_HAS_NO_PRIVILEGES => 'You have not privileges to perform this action.',
        ApiConstant::ROLE_NOT_ASSIGNED => 'You have not assigned any role.',
        ApiConstant::INVALID_BASE64 => 'Invalid image base64 string.',
        ApiConstant::EMPTY_BASE64 => 'Please add base64 string.',
        ApiConstant::EMPTY_TO_DATE => 'Please enter to date.',
        ApiConstant::EMPTY_DESCRIPTION => 'Please enter description.',
//        ApiConstant::
        ApiConstant::FILE_NOT_FOUND =>'Please upload files.',
        ApiConstant::SELECT_USERTYPE =>'Please select user type.',
        ApiConstant::NO_EMAIL_FOUND_FOR_SENDING_MAIL =>'No email found.',
        ApiConstant::MAIL_SENDING_IN_PROGRESS =>'Invitation email sending in progress.',
        ApiConstant::ERROR_EMAIL_UPDATE =>'Failed to update the email please try again.',
        ApiConstant::PASSWORD_WRONG =>'Current password is wrong, please enter current password',
        ApiConstant::ONLY_FOR_ADMIN => 'Only Admin can perform this action.',
        ApiConstant::INVALID_PHONE_NUMBER_TYPE =>'Invalid phone number type.',
        ApiConstant::INVALID_ID => 'This is not your entry.',
        ApiConstant::ERROR_NAME_NOT_SAVED => 'Name not saved.',
        ApiConstant::ID_NOT_FOUND => 'Id not found.',
        ApiConstant::CONTACT_ADMIN =>'Contact admin for login.',
        ApiConstant::NOT_ABLE_TO_CREATE_RECRUTER =>'You cannot create recruiter.',
        ApiConstant::INVALID_URL => 'Invalid url.',
        ApiConstant::APPLY_FAILED =>'You are not eligible at this time.',
        ApiConstant::TEMPLATE_ALREADY_SENT =>'Template already sent',
        ApiConstant::FORMAT_NOT_SUPPORTED => 'Please upload image with jpeg, jpg, bmp, png, gif format.',
        ApiConstant::RESUME_FILE_FORMAT_NOT_SUPPORTED => 'File format not supported, please upload doc,docx,pdf files only.',
        ApiConstant::EXCEPTION_OCCURED => 'Exception occured.',
        ApiConstant::EMPTY_QUALIFICATION => 'Please enter qualification.',
        ApiConstant::EMPTY_KEY_NAME => 'Please enter key name.',
        ApiConstant::EMPTY_VALUE => 'Please enter value.',
        ApiConstant:: DATA_ALREADY_EXIST => 'This data is already present.',
        ApiConstant:: TEMPLATE_NOT_DELETED => 'This template cannot be deleted',
        ApiConstant::LINK_ALREADY_USED => 'This link is already used',
        ApiConstant::EMPTY_DATE_TIME =>'Please enter date time.',
        ApiConstant::EMPTY_ROUND_ID =>'Please select round',
        ApiConstant::EMPTY_ORGANISATION_NAME => 'Please enter organisation name.',
        ApiConstant::EMPTY_CITY_NAME => 'Please enter city name.',
        ApiConstant::EMPTY_LAST_INVITED_DATE => 'Please enter last invited date.',
        ApiConstant::EMPTY_REPRESENTATIVE_TYPE => 'Please enter representative type.',
        ApiConstant::EMPTY_BATCH => 'Please enter batch.',
        ApiConstant::EMPTY_SOURCE_NAME => 'Please enter source name.',
        ApiConstant::EMPTY_SOURCE_DISCRIPTION => 'Please enter source description.',
        ApiConstant::EMAIL_ALREADY_SENT => 'Mail already sent.'

    );

    public static $httpErrorCodeMap = array(
        ApiConstant::EXCEPTION_OCCURED => 500,
        ApiConstant::DATA_NOT_FOUND => 204,
        ApiConstant::AUTHENTICATION_FAILED => 401
    );

    //Stripe Test Secret Key
    Const STRIPE_API_TEST_SECRET_KEY = 'sk_test_h1bt0eNLxLkJuAvJbCPDbWeu';

    //Base path of the upload folder
    Const BASE_PATH_OF_UPLOAD_FOLDER = '/Applications/XAMPP/htdocs/api/public/uploads/';
}