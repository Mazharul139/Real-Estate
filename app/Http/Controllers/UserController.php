<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function Index(){

        return view('frontend.index');
        
    }

    public function UserProfile() {

        $id=Auth::user()->id;
        $userData= User::find($id);
        return view('frontend.dashboard.edit_profile',compact('userData'));
        
    }

    public function UserProfileStore(Request $request)
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
            @unlink(public_path('upload/user_images'.$data->photo));
            $filename= date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/user_images'),$filename);
            $data['photo']=$filename;
        
        }
        $data->Save();
        $notification = array(
            'message'=>'User Profile Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);
    }

    public function UserLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message'=>'User Logout Successfully',
            'alert-type'=>'success'
        );

        return redirect('/login')->with($notification);
    }

    public function UserChangePassword()
    {
        return view('frontend.dashboard.change_password');
    }



    public function UserPasswordUpdate(Request $request) { 
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
