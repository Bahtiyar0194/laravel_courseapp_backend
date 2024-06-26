<?php

namespace App\Http\Controllers;

use App\Models\FeedBack;

use Mail;
use App\Mail\FeedBackMail;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Validator;

class ContactController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_feedback(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'phone' => 'required|regex:/^((?!_).)*$/s',
            'data_agreement' => 'required|accepted',
        ]);

        if($validator->fails()){
            return $this->json('error', 'Send feedback error', 422, $validator->errors());
        }

        $new_feedback = new FeedBack();
        $new_feedback->first_name = $request->first_name;
        $new_feedback->email = $request->email;
        $new_feedback->phone = $request->phone;
        $new_feedback->question = $request->question;
        $new_feedback->ip_address = $request->ip();
        $new_feedback->save();

        $mail_body = new \stdClass();
        $mail_body->subject = 'Форма обратной связи';
        $mail_body->first_name = $request->first_name;
        $mail_body->email = $request->email;
        $mail_body->phone = $request->phone;
        $mail_body->question = $request->question;

        Mail::to('support@webteach.kz')->send(new FeedBackMail($mail_body));

        return response()->json('Your feedback successfully sent', 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
