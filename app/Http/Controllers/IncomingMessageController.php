<?php

namespace App\Http\Controllers;

use App\IncomingMessage;

use Illuminate\Http\Request;

use Twilio; 

//twilio request validator
use Services_Twilio\Services_Twilio_RequestValidator;

//this was necessary along with the next one
use Notification; 

//this was necessary + use Notfication
use App\Notifications\IncomingTextMessage; 





class IncomingMessageController extends Controller
{
    

	public function create(){

	 return view('layouts.partials.form'); 

	}

	public function IncomingMessage(Request $request){


		

      $requestValidator = new \Services_Twilio_RequestValidator(env('TWILIO_TOKEN'));

      $isValid = $requestValidator->validate(
        $request->header('X-Twilio-Signature'),
        $request->fullUrl(),
        $request->toArray()
      );

      if ($isValid) {

      	  if (!empty($from) && !empty($message)){

            $title = 'from: '.$from;
            $msg = 'Message: '.$message;
        }

		$admin = \App\User::find(1); 
		$admin->notify(new IncomingTextMessage($from, $message)); 

		IncomingMessageController::store($from);

		}

		else 
			echo 'You are not twilio';
	}
  

     public function store($from)

	{
		
		if (IncomingMessage::where('number', '=', $from)->exists()) {
   			echo 'Number already in DB'; 
		}

		else {


			Twilio::message($from, 'Welcome to MentorPhilly someone will be with you shortly');

			$storefrom = (string)$from; 

			// you have to pass an associative array of the correspnding table field when you call this
			IncomingMessage::create(['number' => $storefrom]);

		}

	}



}
