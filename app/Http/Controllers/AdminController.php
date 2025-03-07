<?php

namespace App\Http\Controllers;

use App\Models\PackagePlan;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class AdminController extends Controller
{
    public function AdminDashboard()
    {
        return view('admin.index');
    }

    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message'=>'Admin Logout Successfully',
            'alert-type'=>'success'
        );

        return redirect('/admin/login')->with($notification);

    }

    public function AdminLogin()
    {
        return view('admin.admin_login');
    }

    public function AdminProfile()
    {
       $id= Auth::user()->id;
       $profileData = User::find($id);
        return view('admin.admin_profile_view',compact('profileData'));
    }

    public function AdminProfileStore(Request $request)
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
            @unlink(public_path('upload/admin_images'.$data->photo));
            $filename= date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo']=$filename;
        
        }
        $data->Save();
        $notification = array(
            'message'=>'Admin Profile Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);
    }

    public function AdminChangePassword(){

        $id= Auth::user()->id;
        $profileData = User::find($id);
 

        return view('admin.admin_change_password',compact('profileData'));
        
    }
    public function AdminUpdatePassword(Request $request) { 
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

    public function AllAgent(){

        $allagent=User::where('role','agent')->get();
        return view('backend.agentuser.all_agent',compact('allagent'));
    }

    public function AddAgent(){

        return view('backend.agentuser.add_agent');
    }
    
    public function StoreAgent(Request $request){
    
        User::Insert([
    
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'address'=>$request->address,
            'password'=>Hash::make($request->password),
            'role'=>'agent',
            'status'=>'active',
    
        ]);
        
        $notification = array(
            'message'=>'New Agent Added Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.agent')->with($notification);
    }

    public function EditAgent($id){
        $allagent=User::findOrFail($id);
        return view('backend.agentuser.edit_agent',compact('allagent'));
    }

    public function UpdateAgent(Request $request){

        $user_id= $request->id;

        User::findOrFail($user_id)->update([
    
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'address'=>$request->address,
            'password'=>Hash::make($request->password),
            'role'=>'agent',
            'status'=>'active',
    
        ]);
        
        $notification = array(
            'message'=>'Agent Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.agent')->with($notification);
    }

    public function DeleteAgent($id){

        User::findOrFail($id)->delete();

        $notification = array(
            'message'=>'Agent Deleted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.agent')->with($notification);

    }

    public function changestatus(Request $request){

        $user= User::find($request->user_id);
        $user->status=$request->status;
        $user->save();
        return response()->json(['success'=>'status change successfully']);
    }

    public function AdminPackageHistory(){
        $packagehistory=PackagePlan::latest()->get();
        return view('backend.package.package_history',compact('packagehistory'));
    }
    

}






?>

