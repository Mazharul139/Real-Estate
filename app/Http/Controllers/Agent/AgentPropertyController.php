<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\PropertyType;
use App\Models\Amenities;
use App\Models\PackagePlan;
use App\Models\PropertyMessage;
use App\Models\User;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\State;

class AgentPropertyController extends Controller
{
    public function AgentAllProperty() {

        $id=Auth::user()->id;
        $property= Property::where('agent_id',$id)->latest()->get();
        return view('agent.property.all_property',compact('property'));
        
    }

    public function AgentAddProperty()
    {

        $property_type=PropertyType::latest()->get();
        $pstate=State::latest()->get();

        $amenities=Amenities::latest()->get();

        $id=Auth::user()->id;
        $property=User::where('role','agent')->where('id',$id)->first();
        $pcount=$property->credit;
        if($pcount==1 || $pcount==7){
            return redirect()->route('buy.package');
        }
        else{
            return view('agent.property.add_property',compact('property_type','amenities','pstate'));

        }

    }


    public function AgentStoreProperty(Request $request){

        $id=Auth::user()->id;
        $uid=User::findOrFail($id);
        $nid=$uid->credit;

        $amen= $request->amenities_id;
        $amenities= implode(",", $amen);
        $pcode=IdGenerator::generate(['table'=>'properties','field'=>'property_code','length'=>'5','prefix'=>'PC']); 
 
        if($request->file('property_thambnail')){
            $manager = new ImageManager(new Driver());
            $name_gen=hexdec(uniqid()).'.'.$request->file('property_thambnail')->getClientOriginalExtension();
            $image= $manager->read($request->file('property_thambnail'));
            $image=$image->resize(370,250);
            $image->toJpeg(80)->Save(base_path(('public/upload/property/thambnail/'.$name_gen)));
            $save_url= 'upload/property/thambnail/'.$name_gen;



        }
       // $image=$request->file('property_thambnail');
       // $name_gen=hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        //Image::make($image)->resize(370,250)->save('upload/property/thambnail'.$name_gen);
        //$save_url= 'upload/property/thambnail'.$name_gen;

        $property_id= Property::insertGetId([

            'ptype_id'=> $request->ptype_id,
            'amenities_id' =>$amenities,
            'property_name'=> $request->property_name,
            'property_slug'=> strtolower(str_replace('','-',$request->property_name)),
            'property_code'=> $pcode,
            'property_status'=> $request->property_status,
            'lowest_price'=> $request->lowest_price,
            'max_price'=> $request->max_price,
            'short_descp'=> $request->short_descp,
            'long_descp'=> $request->long_descp,
            'bedrooms'=> $request->bedrooms,
            'bathrooms'=> $request->bathrooms,
            'garage'=> $request->garage,
            'garage_size'=> $request->garage_size,

            
            'property_size'=> $request->property_size,
            'property_video'=> $request->property_video,
            'address'=> $request->address,
            'city'=> $request->city,
            'state'=> $request->state,
            'postal_code'=> $request->postal_code,


            'neighbourhood'=> $request->neighbourhood,
            'latitude'=> $request->latitude,
            'longitude'=> $request->longitude,
            'featured'=> $request->featured,
            'hot'=> $request->hot,


            'agent_id'=> Auth::user()->id,
            'status'=> 1,
            'property_thambnail'=> $save_url,
            'created_at'=> Carbon::now(),

        ]);


        //multi img //

        $images=$request->file('multi_img');
        foreach($images as $img){

//if($request->file('property_thambnail')){
                $manager = new ImageManager(new Driver());
                $make_name=hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
                $image= $manager->read($img);
                $image=$image->resize(370,250);
                $image->toJpeg(80)->Save(base_path(('public/upload/property/multi-image/'.$make_name)));
                $uploadPath= 'upload/property/multi-image/'.$make_name;
    
    
    
//}

     //   $make_name=hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
       // Image::make($img)->resize(770,520)->save('upload/property/multi-image'.$make_name);
       // $uploadPath= 'upload/property/multi-image'.$make_name;

        MultiImage::Insert([

            'property_id'=>$property_id,
            'photo_name'=> $uploadPath,
            'created_at'=>Carbon::now(),
        ]);


        }
        //end multi-img

        //facilities//

        $facilities=count($request->facility_name);

        if($facilities!=Null){
            for($i=0;$i<$facilities; $i++){
                $fcount=new Facility();
                $fcount->property_id= $property_id;
                $fcount->facility_name=$request->facility_name[$i];
                $fcount->distance=$request->distance[$i];
                $fcount->save();
            }
        }

        User::where('id',$id)->update([
            'credit'=>DB::raw('1 + '.$nid),
        ]);


        $notification = array(
            'message'=>'Agent Property Inserted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('agent.all.property')->with($notification);

    }//end method

    public function AgentEditProperty($id) {

        $facilities=Facility::where('property_id',$id)->get();

        $property=Property::findOrFail($id);

        $pstate=State::latest()->get();


        $type= $property->amenities_id;
        $property_ami= explode(',', $type);
        $multiImage = MultiImage::where('property_id',$id)->get();


        $property_type=PropertyType::latest()->get();
        $amenities=Amenities::latest()->get();
        

        return view('agent.property.edit_property',compact('property','property_type','amenities','property_ami','multiImage','facilities','pstate'));

        
    }

    public function AgentUpdateProperty(Request $request){

        $property_id=$request->id;
        $amen= $request->amenities_id;
        $amenities= implode(",", $amen);

        Property::findOrFail($property_id)->update([

            'ptype_id'=> $request->ptype_id,
            'amenities_id' =>$amenities,
            'property_name'=> $request->property_name,
            'property_slug'=> strtolower(str_replace('','-',$request->property_name)),
            'property_status'=> $request->property_status,
            'lowest_price'=> $request->lowest_price,
            'max_price'=> $request->max_price,
            'short_descp'=> $request->short_descp,
            'long_descp'=> $request->long_descp,
            'bedrooms'=> $request->bedrooms,
            'bathrooms'=> $request->bathrooms,
            'garage'=> $request->garage,
            'garage_size'=> $request->garage_size,

            
            'property_size'=> $request->property_size,
            'property_video'=> $request->property_video,
            'address'=> $request->address,
            'city'=> $request->city,
            'state'=> $request->state,
            'postal_code'=> $request->postal_code,


            'neighbourhood'=> $request->neighbourhood,
            'latitude'=> $request->latitude,
            'longitude'=> $request->longitude,
            'featured'=> $request->featured,
            'hot'=> $request->hot,


            'agent_id'=> Auth::user()->id,
            'updated_at'=> Carbon::now(),



        ]);

       
        $notification = array(
            'message'=>'Agent Property updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('agent.all.property')->with($notification);

    }

    public function AgentUpdatePropertyThambnail(Request $request){

        $pro_id=$request->id;
        $oldImage=$request->old_img;

        if($request->file('property_thambnail')){
            $manager = new ImageManager(new Driver());
            $name_gen=hexdec(uniqid()).'.'.$request->file('property_thambnail')->getClientOriginalExtension();
            $image= $manager->read($request->file('property_thambnail'));
            $image=$image->resize(370,250);
            $image->toJpeg(80)->Save(base_path(('public/upload/property/thambnail/'.$name_gen)));
            $save_url= 'upload/property/thambnail/'.$name_gen;

            if(file_exists($oldImage)){
                unlink($oldImage);
            }
        }

        Property::findOrFail($pro_id)->update([

            'property_thambnail'=>$save_url,
            'updated_at'=> Carbon::now(),
        ]);

        $notification = array(
            'message'=>'Agent Property Main Thambnail Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);
    }

    public function AgentUpdatePropertyMultiimage(Request $request){

        $imgs=$request->multi_img;
        foreach($imgs as $id=>$img){
            $imgDel= MultiImage::findOrFail($id);
            unlink($imgDel->photo_name);

            $manager = new ImageManager(new Driver());
            $make_name=hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
            $image= $manager->read($img);
            $image=$image->resize(370,250);
            $image->toJpeg(80)->Save(base_path(('public/upload/property/multi-image/'.$make_name)));
            $uploadPath= 'upload/property/multi-image/'.$make_name;

            MultiImage::where('id',$id)->update([
                'photo_name'=>$uploadPath,
                'updated_at'=>Carbon::now(),
            ]);

        }

        $notification = array(
            'message'=>'Property Multi Image Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);


    }

    public function AgentPropertyMultiImageDelete($id){

        $old_img= MultiImage::findOrFail($id);
        unlink($old_img->photo_name);
        MultiImage::findOrFail($id)->delete();

        $notification = array(
            'message'=>'Property Multi Image Deleted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);

    }

    public function AgentStoreNewMultiimage(Request $request){

        $new_multi = $request->imageid;
        $image= $request->file('multi_img');

        $manager = new ImageManager(new Driver());
        $make_name=hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        $image= $manager->read($image);
        $image=$image->resize(370,250);
        $image->toJpeg(80)->Save(base_path(('public/upload/property/multi-image/'.$make_name)));
        $uploadPath= 'upload/property/multi-image/'.$make_name;

        MultiImage::insert([
            'property_id'=>$new_multi,
            'photo_name'=>$uploadPath,
            'created_at'=>Carbon::now(),
        ]);

        $notification = array(
            'message'=>'Property Multi Image Added Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);
    }

    public function AgentUpdatePropertyFacilities(Request $request){

        $pid= $request->id;
        if($request->facility_name==null){
            return redirect()->back();
        }
        else{

            Facility::where('property_id',$pid)->delete();

            $facilities=count($request->facility_name);

        
                for($i=0;$i<$facilities; $i++){
                    $fcount=new Facility();
                    $fcount->property_id= $pid;
                    $fcount->facility_name=$request->facility_name[$i];
                    $fcount->distance=$request->distance[$i];
                    $fcount->save();
                }

        }
        $notification = array(
            'message'=>'Agent Property Facility Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);



    }

    public function AgentDetailsProperty($id) {

        $facilities=Facility::where('property_id',$id)->get();

        $property=Property::findOrFail($id);

        $type= $property->amenities_id;
        $property_ami= explode(',', $type);
        $multiImage = MultiImage::where('property_id',$id)->get();


        $property_type=PropertyType::latest()->get();
        $amenities=Amenities::latest()->get();

        return view('agent.property.details_property',compact('property','property_type','amenities','property_ami','multiImage','facilities'));

        
    }

    public function AgentDeleteProperty($id){

        $property=Property::findOrFail($id);
        unlink($property->property_thambnail);
        Property::findOrFail($id)->delete();

        $image=MultiImage::where('property_id',$id)->get();
        foreach($image as $img){
            unlink($img->photo_name);
            MultiImage::where('property_id',$id)->delete();
        }

        $facilities= Facility::where('property_id',$id)->get();
        foreach($facilities as $item){
            $item->facility_name;
            Facility::where('property_id',$id)->delete();
        }

        $notification = array(
            'message'=>'Agent Property Deleted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('agent.all.property') ->with($notification);

    }


    public function BuyPackage(){
        return view('agent.package.buy_package');
    }

    public function BuyBusinessPlan()
    {
        $id=Auth::user()->id;
        $data=User::find($id);
        return view('agent.package.buy_business_plan',compact('data'));
    }

    public function StoreBusinessPlan(Request $request){

        $id=Auth::user()->id;
        $uid=User::findOrFail($id);
        $nid=$uid->credit;

        PackagePlan::insert([
            'user_id'=>$id,
            'package_name'=>'Business',
            'package_credits'=>'3',
            'invoice'=>'ERS'.mt_rand(10000000,99999999),
            'package_amount'=>'20',
            'created_at'=>Carbon::now(),
        ]);
        User::where('id',$id)->update([
            'credit'=>DB::raw('3 + '.$nid),
        ]);
        $notification = array(
            'message'=>'You have purchase basic package Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('agent.all.property') ->with($notification);




    }

    public function BuyProfessionalPlan()
    {
        $id=Auth::user()->id;
        $data=User::find($id);
        return view('agent.package.professional_plan',compact('data'));
    }

    public function StoreProfessionalPlan(Request $request){

        $id=Auth::user()->id;
        $uid=User::findOrFail($id);
        $nid=$uid->credit;

        PackagePlan::insert([
            'user_id'=>$id,
            'package_name'=>'professional',
            'package_credits'=>'10',
            'invoice'=>'ERS'.mt_rand(10000000,99999999),
            'package_amount'=>'50',
            'created_at'=>Carbon::now(),
        ]);
        User::where('id',$id)->update([
            'credit'=>DB::raw('10 + '.$nid),
        ]);
        $notification = array(
            'message'=>'You have purchase professional package Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('agent.all.property') ->with($notification);

    }

    public function PackageHistory(){

        $id=Auth::user()->id;
        $packagehistory=PackagePlan::where('user_id',$id)->get();
        return view('agent.package.package_history',compact('packagehistory'));
    }

    public function AgentPackageInvoice($id){
        $packagehistory=PackagePlan::where('id',$id)->first();
        $pdf = Pdf::loadView('agent.package.package_history_invoice', compact('packagehistory'))->setPaper('a4')->setOption([
            'tempDir'=> public_path(),
            'chroot'=>  public_path(),
        ]);
        return $pdf->download('invoice.pdf');
    }

    //show message to agent

    public function AgentPropertyMessage(){

        $id=Auth::user()->id;
        $userMsg=PropertyMessage::where('agent_id',$id)->get();
        return view('agent.message.all_message',compact('userMsg'));

    }

    public function AgentMessageDetails($id){

        $uid=Auth::user()->id;
        $userMsg=PropertyMessage::where('agent_id',$uid)->get();
        $msgDetails= PropertyMessage::findOrFail($id);
        return view('agent.message.message_details',compact('userMsg','msgDetails'));

    }








    



}
