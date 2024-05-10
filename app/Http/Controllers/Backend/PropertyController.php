<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\PropertyType;
use App\Models\Amenities;
use App\Models\User;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PackagePlan;
use App\Models\PropertyMessage;
use App\Models\State;






use function Ramsey\Uuid\v1;

class PropertyController extends Controller
{
    public function AllProperty()
    {
        $property=Property::latest()->get();
        return view('backend.property.all_property',compact('property'));

    }

    public function AddProperty()
    {
        $property_type=PropertyType::latest()->get();
        $pstate=State::latest()->get();

        $amenities=Amenities::latest()->get();
        $active_agent=User::where('status','active')->where('role','agent')->latest()->get();

        return view('backend.property.add_property',compact('property_type','amenities','active_agent','pstate'));
    }

    public function StoreProperty(Request $request){

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


            'agent_id'=> $request->agent_id,
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

        $notification = array(
            'message'=>'Property Inserted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.property')->with($notification);

    }//end method


    public function EditProperty($id) {

        $facilities=Facility::where('property_id',$id)->get();

        $property=Property::findOrFail($id);
        $pstate=State::latest()->get();


        $type= $property->amenities_id;
        $property_ami= explode(',', $type);
        $multiImage = MultiImage::where('property_id',$id)->get();


        $property_type=PropertyType::latest()->get();
        $amenities=Amenities::latest()->get();
        $active_agent=User::where('status','active')->where('role','agent')->latest()->get();

        return view('backend.property.edit_property',compact('property','property_type','amenities','active_agent','property_ami','multiImage','facilities','pstate'));

        
    }

    public function UpdateProperty(Request $request){

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


            'agent_id'=> $request->agent_id,
            'updated_at'=> Carbon::now(),



        ]);

        $notification = array(
            'message'=>'Property updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.property')->with($notification);

    }

    public function UpdatePropertyThambnail(Request $request){

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
            'message'=>'Property Main Thambnail Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);



    }

    public function UpdatePropertyMultiimage(Request $request){

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

    public function PropertyMultiImageDelete($id){

        $old_img= MultiImage::findOrFail($id);
        unlink($old_img->photo_name);
        MultiImage::findOrFail($id)->delete();

        $notification = array(
            'message'=>'Property Multi Image Deleted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);

    }

    public function StoreNewMultiimage(Request $request){

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

    public function UpdatePropertyFacilities(Request $request){

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
            'message'=>'Property Facility Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);



    }

    public function DeleteProperty($id){

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
            'message'=>'Property Deleted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.property') ->with($notification);

    }

    public function DetailsProperty($id) {

        $facilities=Facility::where('property_id',$id)->get();

        $property=Property::findOrFail($id);

        $type= $property->amenities_id;
        $property_ami= explode(',', $type);
        $multiImage = MultiImage::where('property_id',$id)->get();


        $property_type=PropertyType::latest()->get();
        $amenities=Amenities::latest()->get();
        $active_agent=User::where('status','active')->where('role','agent')->latest()->get();

        return view('backend.property.details_property',compact('property','property_type','amenities','active_agent','property_ami','multiImage','facilities'));

        
    }

    public function InactiveProperty(Request $request){
        $pid=$request->id;
        Property::findOrFail($pid)->update([
            'status'=>0,
        ]);
        $notification = array(
            'message'=>'Property Inactive Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.property') ->with($notification);

    }

    public function ActiveProperty(Request $request){
        $pid=$request->id;
        Property::findOrFail($pid)->update([
            'status'=>1,
        ]);
        $notification = array(
            'message'=>'Property activated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.property') ->with($notification);

    }

    public function AdminPackageHistory(){
        $packagehistory=PackagePlan::latest()->get();
        return view('backend.package.package_history',compact('packagehistory'));
    }
    public function PackageInvoice($id){
        $packagehistory=PackagePlan::where('id',$id)->first();
        $pdf = Pdf::loadView('backend.package.package_history_invoice', compact('packagehistory'))->setPaper('a4')->setOption([
            'tempDir'=> public_path(),
            'chroot'=>  public_path(),
        ]);
        return $pdf->download('invoice.pdf');
    }


    public function AdminPropertyMessage(){

        
        $userMsg=PropertyMessage::latest()->get();
        return view('backend.message.all_message',compact('userMsg'));

    }



}

?>