<?php

namespace App\Http\Controllers;

use App\IncomingMessage;

use Illuminate\Http\Request;

use Twilio; 

use DB; 

//for sending with Slackbot
use App\SlackBot;

//twilio request validator
use Services_Twilio\Services_Twilio_RequestValidator;

//this is related to Notifications and was necessary to use Notfication
use Notification; 
use App\Notifications\IncomingTextMessage; 



class IncomingMessageController extends Controller
{
    

    /**
     * Create the form to handle the incoming message post
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
	public function create(){

	 return view('layouts.partials.form'); 

	}

	 /**
     * Receiving incoming twilio message and validate that it is coming from twilio 
     * before processing the message
     *
     * @param  \Illuminate\Http\Request  $request
     *
     */
	// public function validateMessage(Request $request){


 //      $requestValidator = new \Services_Twilio_RequestValidator(env('TWILIO_TOKEN'));

 //      $isValid = $requestValidator->validate(
 //        $request->header('X-Twilio-Signature'),
 //        $request->fullUrl(),
 //        $request->toArray()
 //      );

 //      if ($isValid) {

 //         try {
 //            $this->prepareMessage($request);

 //        } catch (RequestException $e) {
 //            throw new \Exception($e->getMessage());

 //        }

	// 	}

	// 	else {
	// 		echo 'You are not twilio';
 //    }
	// }


	/**
     * Prepare the message to send to slack
     *
     * @param  \Illuminate\Http\Request  $request
     *
     */
	public function prepareMessage(Request $request){



		    $incoming_number = '[unknown]';
      	$message = '[empty]';
      	$outgoingMedia = ''; 
      	$outgoingCity = '[unknown]';
      	$outgoingZip = '[unknown]'; 
        $mentees = []; 

      	// if(null != $request->input('From')){
      	// 	$incoming_number = $request->input('From');
      	// 	$mentees = $this->checkForMentee($incoming_number);  
       //    if(!empty($mentees))
      	// 	  $mentee = $mentees[0]->smsname;
      	// }

        // if(!empty($mentees[0]->channel)){

            // $channel = $mentees[0]->channel;
          // } 
          // else 
            $channel = '#texts'; 
      	
      	if(null != $request->input('Body'))
      		$message = $request->input('Body'); 
		
      	if(null != $request->input('MediaUrl0'))
			$outgoingMedia = $request->input('MediaUrl0');
		
		if(null != $request->input('FromCity'))
			$outgoingCity = $request->input('FromCity');
		
		if(null != $request->input('FromZip'))
			$outgoingZip = $request->input('FromZip');



		// if(!empty($mentee)){      	
  //     		$title = 'From: '.$mentee.' at '.$incoming_number; 
  //     	}

  //     	else {

      // comment this out if you revert later
      $incoming_number = $request->input('From');

      		$title = 'From: '.$incoming_number;

      	// }
        $msg = 'Message: '.$message;

        try{

        $this->sendMessage($incoming_number, $title, $message, $outgoingMedia, $outgoingCity, $outgoingZip, $channel);
      }
      catch (Exception $e){

          Log::error('Something is really going wrong.');

      } 

	}

	public function checkForMentee($incoming_number){


			$mentee = DB::table('s_m_s_recipients')
				->join('phones','s_m_s_recipients.id','=','phones.s_m_s_recipient_id')
				->select('s_m_s_recipients.smsname')
				->where('phones.number',$incoming_number)
				->get();

				return $mentee; 


	}

	/**
     * Send the message to slack and then call the function to store it
     *
     * @param  \Illuminate\Http\Request  $request
     *
     */
	public function sendMessage($incoming_number, $title, $message, $outgoingMedia, $outgoingCity, $outgoingZip, $channel){

  //log all texts to webhook slack channel
  $admin = \App\User::find(1); 

  //call notification
	$admin->notify(new IncomingTextMessage($title, $message, $outgoingMedia, $outgoingCity, $outgoingZip)  ); 
       
  // // prepare attachment for Slack
  // $location = $outgoingCity.', '.$outgoingZip;
  
  // //json formatted attachment  
  // $attachment = '[
  //       {
  //           "fallback": "'.$message.'",
  //           "color": "#36a64f",
       
  //           "author_name": "Message Details",
            
  //           "title": "'.$title.'",
            
       
  //           "fields": [
  //               {
  //                   "title": "Location",
  //                   "value": "'.$location.'",
  //                   "short": false
  //               }
  //           ],
            
  //           "text": "'.$message.'",     
  //           "thumb_url": "'.$outgoingMedia.'",
  //           "footer": "MentorPhilly Text Service"
  //       }
  //   ]';

  //   //create new slackbot class to send using slackbot
  //   $bot = new SlackBot; 
  //   $bot->chatter($attachment, $channel); 
   
		//store sent message
			IncomingMessageController::store($incoming_number, $title, $message, $outgoingMedia, $outgoingCity, $outgoingZip);

	}
  
	/**
     * Store the message in the db and auto-reply if this is the first message from the 
     * number
     *
     * @param  \Illuminate\Http\Request  $request
     *
     */
     public function store($incoming_number, $title, $message, $outgoingMedia, $outgoingCity, $outgoingZip)

	{
		
		// if (IncomingMessage::where('number', '=', $incoming_number)->exists()) {
  //  			// echo 'Number already in DB'; 
  //  			$storefrom = (string)$incoming_number; 
  //  			IncomingMessage::create(['number' => $storefrom, 'title' => $title, 'message' => $message, 'outgoingMedia' => $outgoingMedia, 'city' => $outgoingCity, 'zip' => $outgoingZip ]);
		// }

		// else {


		// 	Twilio::message($incoming_number, 'Welcome to MentorPhilly! Someone will respond to you within 24 hours.');

		// 	$storefrom = (string)$incoming_number; 

		// 	// you have to pass an associative array of the correspnding table field when you call this
		// 	IncomingMessage::create(['number' => $storefrom, 'title' => $title, 'message' => $message, 'outgoingMedia' => $outgoingMedia, 'city' => $outgoingCity, 'zip' => $outgoingZip ]);

		// }

      IncomingMessage::create(['number' => $storefrom, 'title' => $title, 'message' => $message, 'outgoingMedia' => $outgoingMedia, 'city' => $outgoingCity, 'zip' => $outgoingZip ]);

	}



}
