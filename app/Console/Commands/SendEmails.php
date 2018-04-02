<?php

namespace App\Console\Commands;

use App\BaseModels\BaseEmailModel;
use Illuminate\Console\Command;
use App\Helpers\AppUtility;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendMail:toCandidate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used for sending email to users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$isSent = 0;
      //  $response  =  DB::table('emails')->where('is_sent',$isSent)->get();
       /* $isSent = 0;
        $response  = BaseEmailModel::where('is_sent',$isSent)->get();
        foreach ($response as $list)
        {
            $body ='hi'.$list->from_name.'<br></br>'.$list->content.

'Thanks & Regards,</br>
Team Recruitment Team.</br><br>
<a href="recruitment@tudip.com" >recruitment@tudip.com </a>| Skype:<a href="recruitment@tudip.com" >
    recruitment@tudip.com</a>';


$id = $list->id;
          $body = $list->content;
            $fromName =$list->from_name;
            $fromEmail =$list->from_email;
            $from = new Email($fromName, "$fromEmail");
            $subject = $list->subject;
            $to = new Email(null, $list->to_email);
            $content = new Content("text/html", $body);
            $mail = new Mail($from, $subject, $to, $content);
            $apiKey = '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';
            $sg = new \SendGrid($apiKey);
            $response = $sg->client->mail()->send()->post($mail);
            if($response == 1)
            {
                DB::table('emails')->where('id', $id)->update([
                    'is_sent' => 1
                ]);
                Log::info('Mail send to Candiadte'.$to.' at '.\Carbon\Carbon::now());
            }
            else
            {
                Log::info('Mail not send to Candiadte'.$to.' at '.\Carbon\Carbon::now());
            }
        }*/
    }
}
