<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
  public function createContact(Request $request){
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'email' => 'required',
      'message' => 'required',
  ]);

  if ($validator->fails()) {
      return back()
                  ->withErrors($validator)
                  ->withInput();
  }
       $data = $this->requestUserData($request);
         Contact::create($data);
         return back()->with(['contactSuccess'=>'Message Send!']);
  }
    public function contactList(){

      if(Session::has('CONTACT_SEARCH')){
        Session::forget('CONTACT_SEARCH');
     }

       $data = Contact::orderBy('contact_id','desc')->paginate(7);
       if(count($data) == 0){
        $emptyStatus = 0;
       }else{
         $emptyStatus = 1;
     } 
       return view('admin.contact.list')->with(['contact'=>$data,'status'=>$emptyStatus]);
    }
    public function contactSearch(Request $request){
      $searchData = Contact::orWhere('name','like','%'.$request->searchData.'%')
                  ->orWhere('email','like','%'.$request->searchData.'%') 
                  ->orWhere('message','like','%'.$request->searchData.'%') 
                  ->paginate(7);

        $searchData->appends($request->all());
          
      Session::put('CONTACT_SEARCH',$request->searchData);

         if(count($searchData) == 0){
            $emptyStatus = 0;
           }else{
             $emptyStatus = 1;
         } 
        return view('admin.contact.list')->with(['contact'=>$searchData,'status'=>$emptyStatus]);
   }

   //Contact Download
   public function contactDownload(){
    
    if(Session::has('CONTACT_SEARCH')){
      $contact = Contact::orWhere('name','like','%'.Session::get('CONTACT_SEARCH').'%')
                  ->orWhere('email','like','%'.Session::get('CONTACT_SEARCH').'%') 
                  ->orWhere('message','like','%'.Session::get('CONTACT_SEARCH').'%') 
                  ->get();
       }else{
      $contact = Contact::get();
       }

       $csvExporter = new\laracsv\Export();

       $csvExporter->build($contact, [  
            'name' => 'Name',
            'email' => 'Email',
            'message' => 'Message',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
       ]);
       $csvReader = $csvExporter->getReader();

       $csvReader->setOutputBOM(\League\Csv\Reader::BOM_UTF8);

       $filename = 'categoryList.csv';

       return response((string) $csvReader)
           ->header('Content-Type', 'text/csv; charset=UTF-8')
           ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
  }


  private function requestUserData($request){
    return [
        'user_id' => auth()->user()->id,
        'name' => $request->name,
        'email' => $request->email,
        'message' => $request->message
    ];
    }
}