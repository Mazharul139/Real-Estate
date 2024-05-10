<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TestimonialController extends Controller
{
    public function AllTestimonials()
    {
        $testimonial= Testimonial::latest()->get();
        return view('backend.testimonial.all_testimonial',compact('testimonial'));
    }//end method

    public function AddTestimonials(){
        return view('backend.testimonial.add_testimonial');
    }//end method

    public function StoreTestimonials(Request $request){

        if($request->file('image')){
            $manager = new ImageManager(new Driver());
            $name_gen=hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $image= $manager->read($request->file('image'));
            $image=$image->resize(100,100);
            $image->toJpeg(80)->Save(base_path(('public/upload/testimonial/'.$name_gen)));
            $save_url = 'upload/testimonial/'.$name_gen;

        }

        Testimonial::insert([

            'name' => $request->name,
            'position' => $request->position,
            'message' => $request->message,
            'image' => $save_url,


        ]);

        $notification = array(
            'message'=>'Testimonial Inserted Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.testimonials')->with($notification);


    }//end method

    public function EditTestimonials($id){

        $testimonial= Testimonial::findOrFail($id);
        return view('backend.testimonial.edit_testimonial',compact('testimonial'));
    }//end method

    public function UpdateTestimonials(Request $request){

        $testimonial_id= $request->id;

        if($request->file('image')){


            $manager = new ImageManager(new Driver());
            $name_gen=hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $image= $manager->read($request->file('image'));
            $image=$image->resize(100,100);
            $image->toJpeg(80)->Save(base_path(('public/upload/testimonial/'.$name_gen)));
            $save_url= 'upload/testimonial/'.$name_gen;

        

        Testimonial::findOrFail($testimonial_id)->update([

            'name' => $request->name,
            'position' => $request->position,
            'image' => $save_url,
            'message' => $request->message,
            


        ]);

        $notification = array(
            'message'=>'Testimonial Updated with image Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.testimonials')->with($notification);


        }

        else{

            Testimonial::findOrFail($testimonial_id)->update([

                'name' => $request->name,
                'position' => $request->position,
                'message' => $request->message,
                
    
    
            ]);
    
            $notification = array(
                'message'=>'Testimonial Updated without image Successfully',
                'alert-type'=>'success'
            );
            return redirect()->route('all.testimonials')->with($notification);
    

        }
    }  //end method

    public function DeleteTestimonals($id){

        $testimonial= Testimonial::findOrFail($id);
        $img= $testimonial->image;
        unlink($img);

        Testimonial::findOrFail($id)->delete();

        $notification = array(
            'message'=>'Testimonial deleted successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.testimonials')->with($notification);

    } //end method









}
