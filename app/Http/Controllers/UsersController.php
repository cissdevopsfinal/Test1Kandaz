<?php

namespace App\Http\Controllers;

use App\users_event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;
use LaravelQRCode\Facades\QRCode;
use Swift_Attachment;

class UsersController extends Controller
{
    public $name;



    public function index()
    {
        $users_event = users_event::where('ticketState','=','Pending')->get()->toArray();
        return view('admin', compact('users_event'));
    }

    public function destroy($id)
    {


        $users_event = users_event::find($id);
        $current_id = users_event::where('id', $id)->pluck('email');

        if ($users_event != null) {
            Mail::send(['html'=>'rejectedmail'],['name','Gouna Event'],function ($message)use ($current_id){

                $message->to($current_id[0])->subject('Sorry, Your reservation was rejected');
                $message->from('cissdevops@gmail.com');




            });

            $users_event->ticketState="Rejected";
            $users_event->save();
            return redirect()->back()->with('success','User Rejected Successfully');




        }






    }
    public function edit($id)
    {
        $users_event = users_event::find($id);
        $current_id = users_event::where('id', $id)->pluck('email');
        $token=users_event::find($id)->token;

        if ($users_event != null) {
            Mail::send(['html' => 'acceptedmail'], ['name', 'Gouna Event'], function ($message) use ($current_id,$token) {

                $message->to($current_id[0])->subject('Your reservation has been Approved');
                $message->from('cissdevops@gmail.com');



            });

            DB::table('users_event')
                ->where('token', $token)
                ->update(['ticketState' => 'Accepted']);
            return redirect()->back()->with('success', 'User Accepted Successfully');


        }


    }

}