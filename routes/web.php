
<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('mailTest', 'WebhookController@sendEmail');
Route::post('WebhookMailTest', 'WebhookController@WebhookMailTest');
Route::post('api/queue/receiver', 'WebhookController@ironMqReceiver');
Route::post('api/auth/login', 'LoginController@login');
Route::post('/api/auth/signUp', 'CandidateController@signUp');
Route::post('/api/auth/forgotpassword','UserController@forgotPassword');
Route::post('/api/auth/resetPassword', 'UserController@resetPasswordDetails');

//Route::get('/api/auth/getVendors', 'UserController@viewVendors');

Route::post('/api/auth/addVendor', 'VendorController@createVendor');

Route::group(['prefix' => 'api/auth/', 'middleware' => ['App\Http\Middleware\authenticateUser']], function () {

    Route::post('/addVendor', 'VendorController@addVendor');
    Route::post('/addPurchaseOrder', 'VendorController@addPurchaseOrder');
    Route::post('/editPurchaseOrder', 'VendorController@editPurchaseOrder');
    Route::post('/editVendor', 'UserController@editVendor');
    Route::post('/deleteVendor', 'UserController@deleteVendor');
    Route::get('/getVendors', 'UserController@viewVendors');
    Route::post('/logout', 'LoginController@logout');
    Route::get('/getUserDetail', 'UserController@getUserDetail');
    Route::post('/viewVendorTags', 'UserController@viewVendorTags');
    Route::post('/addVendorTags', 'VendorController@addVendorTags');
    Route::post('/addVendorItems', 'VendorController@addVendorItems');
    Route::post('/getVendorItems', 'VendorController@viewVendorItems');
    Route::post('/getAllVendorsForItem', 'VendorController@viewAllVendorsForItem');
    Route::get('/viewAllTags', 'UserController@viewAllTags');
    Route::post('/deleteVendorTags', 'VendorController@deleteVendorTags');
    Route::post('/addItem', 'ItemController@addItem');
    Route::get('/viewItems', 'ItemController@viewItems');
    Route::post('/viewItemsBySearch', 'ItemController@viewItemsBySearch');
    Route::post('/viewItemNamesByVendor', 'ItemController@viewItemNames');
    Route::post('/editItem', 'ItemController@editItem');
    Route::post('/getVendorbyId', 'UserController@getVendorById');
    Route::post('/getItembyId', 'ItemController@getItemById');
    Route::post('/viewItemTags', 'ItemController@viewItemTags');
    Route::post('/deleteItem', 'ItemController@deleteItem');
    Route::post('/deleteVendorItem', 'VendorController@deleteVendorItems');
    Route::post('/deleteTermsConditions', 'VendorController@deleteTermsAndTagDetails');
    Route::get('/viewVendorNames', 'VendorController@viewVendorNames');
    Route::post('/viewVendorNamesByItemId', 'VendorController@viewVendorNamesByItemId');
    Route::post('/viewPurchaseOrders', 'VendorController@viewPurchaseOrders');
    Route::post('/viewPurchaseOrderById', 'VendorController@viewPurchaseOrderById');
    Route::post('/sendPurchaseOrderToVendor', 'VendorController@sendPurchaseOrder');
    Route::post('/sendPurchaseOrderBackend', 'VendorController@sendPurchaseOrderBackend');
    Route::post('/deletePurchaseOrder', 'VendorController@deletePurchaseOrder');
    Route::post('/addTermsConditions', 'VendorController@addTermsConditions');
    Route::post('/editTermsConditions', 'VendorController@editTermsConditions');
    Route::post('/viewTermsAndTagDetails', 'VendorController@viewTermsAndTagDetails');
    Route::post('/viewTermsConditionsById', 'VendorController@viewTermsAndTagDetailsById');
    Route::post('/buyItemsFromVendor', 'VendorController@buyItemsFromVendor');
    Route::post('/viewTermsDataByTagId', 'VendorController@viewTermsDataByTagId');
    Route::post('/downloadPurchaseOrder', 'VendorController@downloadPurchaseOrder');
    Route::post('/viewPurchaseDetailsByItemId', 'VendorController@viewPurchaseDetailsByItemId');
    Route::post('/getVendorsByFilters', 'UserController@getVendorsByFilters');
    Route::post('/addPaymentDetails', 'PaymentDetailsController@addPaymentDetails');
    Route::post('/editPaymentDetails', 'PaymentDetailsController@editPaymentDetails');
    Route::post('/deletePaymentDetail', 'PaymentDetailsController@deletePaymentDetails');
    Route::post('/getPaymentDetails', 'PaymentDetailsController@getPaymentDetails');
    Route::post('/getPurchaseOrdersByItemById', 'ItemController@getPurchaseOrdersByItemById');
    Route::post('/getVendorsByItemId', 'ItemController@getVendorsByItemCategories');
    Route::post('/addItemQuantity', 'ItemController@addItemQuantity');
    Route::post('/addUsedItemQuantity', 'ItemController@addUsedItemQuantity');
    Route::post('/getItemQuantity', 'ItemController@getItemQuantity');
    Route::post('/getUsedItemQuantity', 'ItemController@getUsedItemQuantity');
    Route::post('/deleteItemQuantity', 'ItemController@deleteItemQuantity');
    Route::post('/deleteItemUsed', 'ItemController@deleteItemUsed');
    Route::post('/getItemsReceived', 'ItemController@getItemsReceived');
    Route::post('/addItemLocation', 'ItemController@addItemLocation');
    Route::post('/editItemLocation', 'ItemController@editItemLocation');
    Route::post('/deleteItemLocation', 'ItemController@deleteItemLocation');
    Route::post('/getItemLocationById', 'ItemController@getItemLocationById');
    Route::get('/viewAllItemLocations', 'ItemController@getAllItemLocations');
    Route::get('/viewItemLocations', 'ItemController@getItemLocations');
//    Route::post('/viewTermsConditionsById', 'VendorController@viewTermsAndTagDetailsById');


    //Recruitment related api

    Route::post('/addUser', 'UserController@createUser');
    Route::post('/editUser', 'UserController@editUser');
    Route::post('/deleteUser', 'UserController@deleteUser');
    Route::post('/viewUser', 'UserController@viewUser');
    Route::post('/getUserById', 'UserController@viewUserById');
    Route::post('/searchCandidateDetail', 'UserController@searchCandidateDetails');
    Route::post('/updateProfile', 'UserController@updateProfileDetails');
    Route::post('/recruiterWork', 'UserController@recruiterWork');

    Route::post('barGraph', 'UserController@barGraph');
    Route::post('viewInterviewerWork', 'UserController@viewInterviewerWork');
    Route::post('viewBirthdayList', 'UserController@viewBirthdayList');
    Route::post('getCandidatesByDates', 'UserController@getCandidatesByDates');

    // Expected Joiners related api
    Route::post('/addExpectedJoiners', 'UserController@addExpectedJoiners');
    Route::post('/editExpectedJoiners', 'UserController@editExpectedJoiners');
    Route::post('/viewExpectedJoiners', 'UserController@viewExpectedJoiners');
    Route::post('/deleteExpectedJoiners', 'UserController@deleteExpectedJoiners');
    Route::post('/sendMailToExpectedJoiners', 'UserController@sendMailToExpectedJoiners');
    Route::post('/getExpectedJoinerDetailsById', 'UserController@getExpectedJoinerDetailsById');
    Route::post('/viewExpectedJoinersByBatch', 'UserController@viewExpectedJoinerDetailsByBatch');

    // Expected joiners Batch(Ex.2016-17)

    Route::post('/addBatch', 'SettingController@addBatch');
    Route::post('/editBatch', 'SettingController@editBatch');
    Route::post('/deleteBatch', 'SettingController@deleteBatch');
    Route::get('/viewBatch', 'SettingController@viewBatch');
    Route::post('/getBatchById', 'SettingController@getBatchById');

    // Tpo related api
    Route::post('/addTpoDetails', 'UserController@addTpoDetails');
    Route::post('/editTpoDetails', 'UserController@editTpoDetails');
    Route::post('/viewTpoDetails', 'UserController@viewTpoDetails');
    Route::post('/deleteTpoDetails', 'UserController@deleteTpoDetails');
    Route::post('/sendMailToTpo', 'UserController@sendMailToTpo');
    Route::post('/getTpoDetailsById', 'UserController@getTpoDetailsById');
    Route::post('/viewTpoTouchLogs', 'UserController@viewTpoTouchLogs');

    Route::post('/viewCandidateByStatus', 'UserController@viewCandidateByStatus');
    Route::post('/getCandidatesByFilters', 'UserController@getCandidatesByFilters');
    Route::post('/viewCandidateByDate', 'UserController@viewCandidateByDate');
    Route::post('/viewCandidateByTags', 'UserController@viewCandidateByTags');
    Route::post('/viewCandidateTags', 'UserController@viewCandidateTags');
    Route::get('/viewAllTags', 'UserController@viewAllTags');

    Route::post('/addSenderDetails', 'UserController@addSenderDetails');
    Route::get('/viewSenderDetails', 'UserController@viewSenderDetails');

    Route::post('/searchCandidateByInput', 'UserController@searchCandidateByInput');


    //Interviewer related api
    Route::post('/viewInterviewer', 'UserController@viewInterviewer');
    //Candidate related api
    Route::post('/editCandidate', 'UserController@editCandidate');
    Route::post('/deleteCandidate', 'UserController@deleteCandidate');
    Route::post('/viewCandidate', 'UserController@viewCandidate');
    Route::post('/getCandidateById', 'UserController@getCandidateById');
    Route::post('/getCandidateProfileById', 'CandidateController@getCandidateProfileById');
    Route::post('/updateCandidateProfile', 'CandidateController@updateCandidateProfile');
    Route::post('/candidateApplyDetails', 'CandidateController@candidateApplyDetails');
    Route::post('/addCandidate', 'CandidateController@addCandidate');
    Route::post('/uploadResume', 'CandidateController@uploadResume');
    Route::post('/renderEmail', 'CandidateController@renderTemplate');

    //Candidate Logs& Feedback

    Route::post('/viewCandidateLogs', 'CandidateController@showCandidateLogs');
    Route::post('/saveCandidateFeedback', 'UserController@saveCandidateFeedback');
    Route::post('/viewCandidateFeedback', 'UserController@viewCandidateFeedback');
    Route::post('/deleteCandidateFeedback', 'UserController@deleteCandidateFeedback');
    Route::post('/viewCandidateEmailLogs', 'CandidateController@viewCandidateEmailLogs');

    Route::post('/viewUnApprovedCandidate', 'UnApprovedCandidateController@viewUnApprovedCandidate');
    Route::post('/moveCandidate', 'UnApprovedCandidateController@moveCandidate');
    Route::post('/moveCandidateToUnapproved', 'RejectedCandidateController@moveCandidateToUnApproved');
    Route::post('/viewRejectedCandidate', 'RejectedCandidateController@viewRejectedCandidate');
    Route::post('/deleteRejectedCandidate', 'RejectedCandidateController@deleteRejectedCandidate');
    Route::post('/deleteMultipleRejectedCandidates', 'RejectedCandidateController@deleteMultipleRejectedCandidates');
    Route::post('/viewAttachments', 'UnApprovedCandidateController@viewAttachments');

    Route::post('/deleteUnApprovedCandidate', 'UnApprovedCandidateController@deleteUnApprovedCandidate');


    //Template related api
    Route::get('/template', 'TemplateController@view');
    Route::post('/viewTemplateByRound', 'TemplateController@viewTemplateByRound');
    Route::post('/getTemplateById', 'TemplateController@getTemplateById');
    Route::post('/template', 'TemplateController@add');
    Route::post('/editTemplate', 'TemplateController@add');
    Route::post('/sendmail', 'UserController@sendMail');
    Route::post('/sendTestEmail', 'UserController@sendTestMail');
    Route::post('/deleteTemplate', 'TemplateController@deleteTemplate');
    Route::post('/changePassword','UserController@changePassword');

    //Setting(FeedbackMock)
    Route::post('/addFeedbackMock', 'SettingController@addFeedbackMock');
    Route::post('/editFeedbackMock', 'SettingController@editFeedbackMock');
    Route::get('/viewFeedbackMock', 'SettingController@viewFeedbackMock');
    Route::post('/getFeedbackMockById', 'SettingController@getFeedbackMockById');

    //Setting(Job profile)
    Route::get('/viewJob', 'SettingController@viewJob');
    Route::post('/getJobById', 'SettingController@getJobById');
    Route::post('/addJob', 'SettingController@addJob');
    Route::post('/editJob', 'SettingController@editJob');
    Route::post('/deleteJob', 'SettingController@deleteJob');

    //Setting(Rounds)
    Route::get('/viewRounds', 'SettingController@viewRound');
    Route::post('/getRoundById', 'SettingController@getRoundById');
    Route::post('/addRound', 'SettingController@addRound');
    Route::post('/editRound', 'SettingController@editRound');
    Route::post('/deleteRound', 'SettingController@deleteRound');

    //Setting(Job Experience)
    Route::post('/addNoOfMonths', 'SettingController@addNoOfMonths');
    Route::get('/viewNoOfMonths', 'SettingController@viewNoOfMonths');
    Route::get('/viewJobExperience', 'SettingController@viewJobExperience');
    Route::post('/getJobExperienceById', 'SettingController@getJobExperienceById');
    Route::post('/addJobExperience', 'SettingController@addJobExperience');
    Route::post('/editJobExperience', 'SettingController@editJobExperience');
    Route::post('/deleteJobExperience', 'SettingController@deleteJobExperience');
    Route::get('/viewJobAndExperience', 'SettingController@viewJobAndExperience');

    //Setting(Application Stage)
    Route::get('/viewApplicationStage', 'SettingController@viewApplicationStage');
    Route::post('/getApplicationStageById', 'SettingController@getApplicationStageById');
    Route::post('/addApplicationStage', 'SettingController@addApplicationStage');
    Route::post('/editApplicationStage', 'SettingController@editApplicationStage');
    Route::post('/deleteApplicationStage', 'SettingController@deleteApplicationStage');

    //Setting(Rejected Domain)
    Route::get('/viewRejectedDomain', 'SettingController@viewRejectedDomain');
    Route::post('/getRejectedDomainById', 'SettingController@getRejectedDomainById');
    Route::post('/addRejectedDomain', 'SettingController@addRejectedDomain');
    Route::post('/editRejectedDomain', 'SettingController@editRejectedDomain');
    Route::post('/deleteRejectedDomain', 'SettingController@deleteRejectedDomain');

    //Feedback
    Route::post('/saveFeedback', 'FeedbackController@saveFeedback');
    Route::post('/viewAllFeedback', 'FeedbackController@viewAllFeedback');
    Route::post('/viewFeedbackByInterviewer', 'FeedbackController@viewFeedbackByInterviewer');
    Route::post('/viewFeedbackByCandidate', 'FeedbackController@viewFeedbackByCandidate');
    Route::post('/saveWrittenRoundFeedback', 'FeedbackController@saveWrittenRoundFeedback');
    Route::post('/viewWrittenRoundFeedback', 'FeedbackController@viewWrittenRoundFeedback');
    Route::post('/editWrittenRoundFeedback', 'FeedbackController@editWrittenRoundFeedback');

    //Setting(Qualification)
    Route::get('/viewQualification', 'SettingController@viewQualification');
    Route::post('/getQualificationById', 'SettingController@getQualificationById');
    Route::post('/addQualification', 'SettingController@addQualification');
    Route::post('/editQualification', 'SettingController@editQualification');
    Route::post('/deleteQualification', 'SettingController@deleteQualification');

    // Setting(Static Replacement)
    Route::post('/addStaticReplacement', 'SettingController@addStaticReplacement');
    Route::post('/editStaticReplacement', 'SettingController@editStaticReplacement');
    Route::post('/deleteStaticReplacement', 'SettingController@deleteStaticReplacement');
    Route::get('/viewStaticReplacement', 'SettingController@viewStaticReplacement');
    Route::post('/getStaticReplacementById', 'SettingController@getStaticReplacementById');

    //Setting(Date Time )
    Route::post('/addDateTime', 'SettingController@addDateTime');
    Route::post('/editDateTime', 'SettingController@editDateTime');
    Route::post('/deleteDateTime', 'SettingController@deleteDateTime');
    Route::get('/viewDateTime', 'SettingController@viewDateTime');
    Route::post('/getDateTimeById', 'SettingController@getDateTimeById');
    Route::post('/viewDateTimeByRound', 'SettingController@viewDateTimeByRound');

    //Setting(Organisation Details )
    Route::post('/addOrganisationDetails', 'SettingController@addOrganisationDetails');
    Route::post('/editOrganisationDetails', 'SettingController@editOrganisationDetails');
    Route::post('/deleteOrganisationDetails', 'SettingController@deleteOrganisationDetails');
    Route::get('/viewOrganisationDetails', 'SettingController@viewOrganisationDetails');
    Route::post('/getOrganisationDetailsById', 'SettingController@getOrganisationDetailsById');
    Route::post('/viewOrganisationDetailsByType', 'SettingController@viewOrganisationDetailsByType');

    // Setting(Representative Type)

    Route::post('/addRepresentativeType', 'SettingController@addRepresentativeType');
    Route::post('/editRepresentativeType', 'SettingController@editRepresentativeType');
    Route::post('/deleteRepresentativeType', 'SettingController@deleteRepresentativeType');
    Route::get('/viewRepresentativeType', 'SettingController@viewRepresentativeType');
    Route::post('/getRepresentativeTypeById', 'SettingController@getRepresentativeTypeById');



        Route::post('/testcsv', 'UserController@save');

});
