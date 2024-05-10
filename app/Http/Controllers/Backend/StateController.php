<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;




class StateController extends Controller
{
    public function AllState()
    {
        $state= State::latest()->get();
        return view('backend.state.all_state',compact('state'));
    }

    public function AddState()
    {
        return view('backend.state.add_state');
    }

    public function StoreState(Request $request){

        if($request->file('state_image')){
            $manager = new ImageManager(new Driver());
            $name_gen=hexdec(uniqid()).'.'.$request->file('state_image')->getClientOriginalExtension();
            $image= $manager->read($request->file('state_image'));
            $image=$image->resize(370,275);
            $image->toJpeg(80)->Save(base_path(('public/upload/state/'.$name_gen)));
            $save_url= 'upload/state/'.$name_gen;

        }

        State::insert([

            'state_name' => $request->state_name,
            'state_image' => $save_url,


        ]);

        $notification = array(
            'message'=>'State Inserted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.state')->with($notification);


    }//end method

    public function EditState($id){

        $state= State::findOrFail($id);
        return view('backend.state.edit_state',compact('state'));
    }

    public function UpdateState(Request $request){

        $state_id= $request->id;

        if($request->file('state_image')){


            $manager = new ImageManager(new Driver());
            $name_gen=hexdec(uniqid()).'.'.$request->file('state_image')->getClientOriginalExtension();
            $image= $manager->read($request->file('state_image'));
            $image=$image->resize(370,275);
            $image->toJpeg(80)->Save(base_path(('public/upload/state/'.$name_gen)));
            $save_url= 'upload/state/'.$name_gen;

        

        State::findOrFail($state_id)->update([

            'state_name' => $request->state_name,
            'state_image' => $save_url,


        ]);

        $notification = array(
            'message'=>'State Updated with image Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.state')->with($notification);


        }

        else{

            State::findOrFail($state_id)->update([

                'state_name' => $request->state_name,
    
    
            ]);
    
            $notification = array(
                'message'=>'State Updated without image Successfully',
                'alert-type'=>'success'
            );
            return redirect()->route('all.state')->with($notification);
    

        }
    }  //end method

    public function DeleteState($id){

        $state= State::findOrFail($id);
        $img= $state->state_image;
        unlink($img);

        State::findOrFail($id)->delete();

        $notification = array(
            'message'=>'State deleted successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.state')->with($notification);

    } //end method


}
