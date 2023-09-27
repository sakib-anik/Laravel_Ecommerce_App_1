<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCodeController extends Controller
{
    // Will be used for listing
    public function index(Request $request){
        $discountCoupons = DiscountCoupon::latest();
        if(!empty($request->get('keyword'))){
            $discountCoupons = $discountCoupons->where('name','like','%'.$request->get('keyword').'%');
            $discountCoupons = $discountCoupons->orWhere('code','like','%'.$request->get('keyword').'%');
        }
        $discountCoupons = $discountCoupons->paginate(10);
        return view('admin.coupon.list',compact('discountCoupons'));
    }

    // For displaying create form
    public function create(){
        return view('admin.coupon.create');
    }

    // For storing discount coupon code in database
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->passes()){

            // starting date must be greater than current date
            if(!empty($request->starts_at)){
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if($startAt->lte($now) == true){        // lte = less than equal
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Start date can not be less than current date time'],
                    ]);
                }
            }

            // expiry date must be greater than start date
            if(!empty($request->expires_at) && !empty($request->starts_at)){
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if($expiresAt->gt($startAt) == false){      // gt = greater than
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expiry date must be greater than start date'],
                    ]);
                }
            }

            $discountCode = new DiscountCoupon;
            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->type = $request->type;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->status = $request->status;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            $message = 'Discount coupon added successfully.';

            session()->flash('success',$message);
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    // For editing discount coupon
    public function edit(Request $request, $id){
        // echo $id;

        $coupon = DiscountCoupon::find($id);

        if($coupon == null){
            session()->flash('error','Record not found');
            return redirect()->route('coupons.index');
        }

        $data['coupon'] = $coupon; 

        return view('admin.coupon.edit', $data);
    }

    public function update(Request $request, $id){
        $discountCode = DiscountCoupon::find($id);

        if($discountCode == null){
            session()->flash('error','Record not found');
            return response()->json([
                'status' => true,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->passes()){

            

            // expiry date must be greater than start date
            if(!empty($request->expires_at) && !empty($request->starts_at)){
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if($expiresAt->gt($startAt) == false){      // gt = greater than
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expiry date must be greater than start date'],
                    ]);
                }
            }

            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->type = $request->type;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->status = $request->status;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            $message = 'Discount coupon updated successfully.';

            session()->flash('success',$message);
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request, $id){
        $discountCode = DiscountCoupon::find($id);

        if($discountCode == null){
            session()->flash('error','Record not found');
            return response()->json([
                'status' => true,
            ]);
        }

        $discountCode->delete();
        session()->flash('success','Discount Coupon deleted successfully');
        return response()->json([
            'status' => true,
        ]);
    }
}