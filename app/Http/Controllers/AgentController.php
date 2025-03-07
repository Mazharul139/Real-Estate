<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;



use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function AgentDashboard()
    {
        return view('agent.index');
    }

    public function AgentLogin(){

        return view('agent.agent_login');
        
    }

    public function AgentRegister(Request $request){

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' =>$request->phone,
            'password' => Hash::make($request->password),
            'role' =>'agent',
            'status' =>'Inactive',

        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::AGENT);
    }

    public function AgentLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message'=>'Agent Logout Successfully',
            'alert-type'=>'success'
        );

        return redirect('/agent/login')->with($notification);

    }

    public function AgentProfile()
    {
       $id= Auth::user()->id;
       $profileData = User::find($id);
        return view('agent.agent_profile_view',compact('profileData'));
    }

    public function AgentProfileStore(Request $request)
    {
        $id= Auth::user()->id;
        $data = User::find($id);
        $data->username = $request ->username;
        $data->name = $request ->name;
        $data->email = $request ->email;
        $data->phone = $request ->phone;
        $data->address = $request ->address;

        if($request->file('photo'))
        {
            $file= $request->file('photo');
            @unlink(public_path('upload/agent_images/'.$data->photo));
            $filename= date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/agent_images/'),$filename);
            $data['photo']=$filename;
        
        }
        $data->Save();
        $notification = array(
            'message'=>'Agent Profile Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);
    }

    public function AgentChangePassword(){

        $id= Auth::user()->id;
        $profileData = User::find($id);
 

        return view('agent.agent_change_password',compact('profileData'));
        
    }

    public function AgentUpdatePassword(Request $request) { 
        //validation
    $request->validate([
        'old_password' => 'required',
         'new_password' => 'required'
    ]);

   // match the old password

    if(!Hash::check($request->old_password, auth::user()->password)){

        $notification = array(
            'message'=>'old password does not match!',
            'alert-type'=>'error'
        );
        return back()->with($notification);
    }

    //update the new password
    User::whereId(auth()->user()->id)->update([

        'password'=>Hash::make($request->new_password)

    ]);

    $notification = array(
        'message'=>'Password Change Successfully',
        'alert-type'=>'success'
        
    );
    return back()->with($notification);
    
}




}
