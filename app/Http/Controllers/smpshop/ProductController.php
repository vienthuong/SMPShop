<?php

namespace App\Http\Controllers\smpshop;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;
use App\Category;
use App\Brand;
use App\Events\DetailPageAccess;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
	public function index(){
		$products = Product::orderBy('view','DESC')->paginate(9);
    	return view('public.product.index',['products'=>$products]);
	}
	public function cat($slugcat){
		// return "??";
		$slugcat = str_replace('-',' ',$slugcat);
		$cat = Category::where(DB::raw('LOWER(cat_name)'),$slugcat)->first();
		$brand = Brand::where(DB::raw('LOWER(brand_name)'),$slugcat)->first();
		if(!$cat AND !$brand){
			return abort(404);
		}else if($cat){
			$products = $cat->product()->paginate(9);
			return view('public.product.cat',['cat'=>$cat,'products'=>$products]);
		}else{
			$products=$brand->product()->paginate(9);
			return view('public.product.cat',['brand'=>$brand,'products'=>$products]);
		}
	}
	public function detail($slugcat,$slug,$id){
		$product = Product::findOrfail($id);
		event(new DetailPageAccess($product));
		$pname = str_slug($product->pname);
		$catname = str_slug($product->category->cat_name);
		if($pname!=$slug OR $catname!=$slugcat){
			return redirect()->route('public.product.detail',['slugcat'=>$catname,'slug'=>$pname,'id'=>$id]);
		}
		return view('public.product.detail',['product'=>$product,'cat_slug'=>$catname]);
	}
	public function suggestSearch(Request $request){
		$kw = trim($request->kw);
		$products = Product::where('pname','LIKE','%'.$kw.'%')->orWhere('tags','LIKE','%'.$kw.'%')->orderBy('view','DESC')->take(5)->get();
		return view('public.product.suggest',['products'=>$products]);
	}
	public function filter(Request $request){
		$minprice = ($request->minprice)?$request->minprice:0;
		$maxprice = ($request->maxprice)?$request->maxprice:999999999;
		$brand = ($request->brand)?$request->brand:'%';
		$cat = ($request->cat)?$request->cat:'%';
		$viewmore = $request->viewmore;
		$offset = $request->offset;
		$dataload = $offset + 9;
		// return $request->discount;
		if($request->price=='hightolow'){
			$orderPrice = 'DESC';
		}else{
			$orderPrice = 'ASC';
		}
		// return $request->discount==true;
		if($request->discount=='true'){
			$product = Product::orderBy('discount','DESC')->orderBy('price',$orderPrice)->whereBetween('price',[$minprice,$maxprice])->where(DB::raw('cat_id::TEXT'),'LIKE',$cat)->where(DB::raw('brand_id::TEXT'),'LIKE',$brand)->offset($offset)->limit(9999)->get();
		}else{
			$product = Product::orderBy('price',$orderPrice)->whereBetween('price',[$minprice,$maxprice])->where(DB::raw('cat_id::TEXT'),'LIKE',$cat)->where(DB::raw('brand_id::TEXT'),'LIKE',$brand)->offset($offset)->limit(99999)->get();
		}
		if($product->count()==0) return '<h1 class="pad-top" style="color:#fff">No result for these filter</h1>';
		return view('public.product.filter',['products'=>$product,'dataload'=>$dataload]);
	}
}
