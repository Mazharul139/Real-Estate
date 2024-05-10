<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Models\BlogCategory;
use App\Models\User;




class BlogController extends Controller
{
    public function AllBlogCategory(){

        $category = BlogCategory::latest()->get();
        return view('backend.category.blog_category',compact('category'));

    }//end method

    public function StoreBlogCategory(Request $request) {


        BlogCategory::insert([

            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ','-',$request->category_name)),
            
        ]);

        $notification = array(
            'message'=>'Blog Category Created Successfully',
            'alert-type'=>'success'
        );
        return redirect()->route('all.blog.category')->with($notification);

        
    }//end method

}
