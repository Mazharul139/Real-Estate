<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\PropertyType;
use App\Models\Amenities;
use App\Models\User;
use App\Models\PackagePlan;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class WishlistController extends Controller
{
    public function AddToWishList(Request $request, $property_id){

        if(Auth::check()){
            $exist= Wishlist::where('user_id',Auth::id())->where('property_id',$property_id)->first();
            if(!$exist){
                Wishlist::Insert([
                    'user_id' => Auth::id(),
                    'property_id' => $property_id,
                    'created_at' => Carbon::now()

                ]);
                return response()->json(['success' =>'Successfully Added On Your Wishlist']);
            }
            else{
                return response()->json(['error' =>'This Property Already On Your Wishlist']);
            }
        }
        else{

            return response()->json(['error' =>'Please Login First']);
        }
    }

    public function UserWishlist(){

        $id=Auth::user()->id;
        $userData=User::find($id);
        return view('frontend.dashboard.wishlist',compact('userData'));
    }

    public function GetWishlistProperty(){

        $wishlist = Wishlist::with('property')->where('user_id',Auth::id())->latest()->get();
        $wishQty = Wishlist::count();
        return response()->json(['wishlist'=>$wishlist , 'wishQty'=> $wishQty]);


    }//end method

    public function WishlistRemove($id){

        Wishlist::where('user_id',Auth::id())->where('id',$id)->delete();
        return response()->json(['success' => 'Successfully Property Removed']);


    }
}
