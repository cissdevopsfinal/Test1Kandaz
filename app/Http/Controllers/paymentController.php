<?php

namespace App\Http\Controllers;

use App\pricing;
use App\users_event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;
use LaravelQRCode\Facades\QRCode;
use Swift_Attachment;

class paymentController extends Controller
{


    public $name;
    public function index(){

        $users_event = users_event::where('ticketState','=','Accepted')->where('Paid','=','0')->get()->toArray();
        return view('accepted', compact('users_event'));
    }
    public function edit($id)
    {
        $users_event = users_event::find($id);
        $current_id = users_event::where('id', $id)->pluck('email');
        $token=users_event::find($id)->token;
        QRCode::text($token)->setOutfile($token.".png")->png();

        if ($users_event != null) {
            Mail::send(['html' => 'QRmail'], ['name', 'Gouna Event'], function ($message) use ($current_id,$token) {

                $message->to($current_id[0])->subject('Your reservation has been Approved');
                $message->from('cissdevops@gmail.com');
                $message->attach("".$token.".png");



            });
            $priceID=users_event::find($id)->Price;
            $Bonus=users_event::find($id)->Bonus;
            $currentPrice=pricing::findprice($priceID);
            $Total=$currentPrice+$Bonus;
            File::delete("".$token.".png");
            DB::table('users_event')
                ->where('token', $token)
                ->update(['Paid' => $Total,'ticketState'=>'Paid']);
            return redirect()->back()->with('success', 'QR Code Sent');


        }


    }
}
