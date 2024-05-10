<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Compare;
use App\Models\PackagePlan;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CompareController extends Controller
{
    public function AddToCompare(Request $request, $property_id){

        if(Auth::check()){
            $exist= Compare::where('user_id',Auth::id())->where('property_id',$property_id)->first();
            if(!$exist){
                Compare::Insert([
                    'user_id' => Auth::id(),
                    'property_id' => $property_id,
                    'created_at' => Carbon::now()

                ]);
                return response()->json(['success' =>'Successfully Added On Your Compare']);
            }
            else{
                return response()->json(['error' =>'This Property Already On Your CompareList']);
            }
        }
        else{

            return response()->json(['error' =>'Please Login First']);
        }
    }//end method

    public function UserCompare(){

        
        return view('frontend.dashboard.compare');


    }//end method

    public function GetCompareProperty(){

        $compare = Compare::with('property')->where('user_id',Auth::id())->latest()->get();
        return response()->json($compare);


    }//end method

    public function CompareRemove($id){

        Compare::where('user_id',Auth::id())->where('id',$id)->delete();
        return response()->json(['success' => 'Successfully Property Removed']);


    }


}
