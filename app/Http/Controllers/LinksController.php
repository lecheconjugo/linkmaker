<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use App\Link;
use App\LinkVisit;
use stdClass;

class LinksController extends Controller
{
    var $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    var $length = 6;
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $links = Link::with('campaign', 'link_visits')->orderBy('id', 'DESC')->get();
	    return response()->json($links, 200)->header('Content-Type', 'application/vnd.api+json');
    }

    public function store(Request $request)
    {
        try
        {
        	//Validar que no existe el mismo codigo para otro link
        	$short_code = '';
        	for($x = 0; $x < 1000; $x++){
        		
        		$short_code = $this->generateRandomCode();
        		$exist_link = DB::table('links')->whereRaw("links.short_code = ?", array($short_code))->count();

	        	if($exist_link == 0){
	        		break;
	        	}
        	}
        	
        	//Objecto de respuesta
        	$response = new stdClass();
    		$response->status =  false;
    		$response->message = "";
    		$response->data = [];

            $request_data = $request->all();

    		//Se genera el objeto Link
            $link_object = new Link();
            $link_object->campaign_id = $request_data['campaign_id'];
            $link_object->link_name = $request_data['link_name'];
            $link_object->link_long_url = $request_data['link_long_url'];
            $link_object->short_code = $short_code;
            $link_object->status = 1;

            if($link_object->save()){

                $link = Link::with('campaign', 'link_visits')->where('id', $link_object->id)->orderBy('id', 'DESC')->first();

            	$response->status = true;
            	$response->status = "Link created successfull";
            	$response->data = $link;

                Cache::forever('link_'.$short_code, $link);
            }
            else{
            	$response->status = false;
            	$response->status = "Error while creating link, try again";
            }

            throw new HttpResponseException(response()->json($response, 200));
        }
        catch (Exception $e)
        {
            $object_response["descripcion"] = $e;
            throw new HttpResponseException(response()->json($object_response, 402));
        }
    }

    public function update(Request $request, $id){

    	try
        {
        	//Objecto de respuesta
        	$response = new stdClass();
    		$response->status =  false;
    		$response->message = "";
    		$response->data = [];

    		$request_data = $request->all();

    		//Se genera el objeto Link
            $link = Link::where('id', $id)->first();
            
            if($link != null){
                $link->campaign_id = $request_data['campaign_id'];
	    		$link->link_name = $request_data['link_name'];
	    		$link->link_long_url = $request_data['link_long_url'];
	    		$link->status = $request_data['status'];

	    		if($link->save()){

                    $updated_link = Link::with('campaign', 'link_visits')->where('id', $link->id)->orderBy('id', 'DESC')->first();

	            	$response->status = true;
	            	$response->status = "Link updated successfull";
	            	$response->data = $updated_link;

                    Cache::forget('link_'.$link->short_code);
                    Cache::forever('link_'.$link->short_code, $updated_link);
	            }
	            else{
	            	$response->status = false;
	            	$response->status = "Error while updating link, try again";
	            }
	    	}

            throw new HttpResponseException(response()->json($response, 200));
        }
        catch (Exception $e)
        {
            $object_response["descripcion"] = $e;
            throw new HttpResponseException(response()->json($object_response, 402));
        }
    }

    public function destroy($id)
    {
        try
        { 
            $response = new stdClass();
            $response->status =  false;
            $response->message = "";
            $response->data = [];

            $link = Link::findorfail($id);

            if($link){

                foreach ($link->link_visits as $visit)
                {
                    $visit->delete();
                }

                if($link->delete()){
                    $response->status = true;
                    $response->message = "Link deleted succesfull";
                    $response->data['id'] = $link->id;

                    Cache::forget('link_'.$link->short_code);
                }
            }

            throw new HttpResponseException(response()->json($response, 200));
        }
        catch (Exception $e)
        {
            $object_response["descripcion"] = $e;
            throw new HttpResponseException(response()->json($object_response, 402));
        }

    }

    function generateRandomCode() {
        $characters = $this->characters;
        $length = $this->length;

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function searchLink($short_code = null){

        if (Cache::has('link_'.$short_code)){
            
            $link = Cache::get('link_'.$short_code);

            $this->validateIfActiveLink($link);

            $this->addVisit($link->id);

            return redirect()->away((is_null(parse_url($link->link_long_url, PHP_URL_HOST)) ? '//' : '').$link->link_long_url);
        }
        else {

            $link = Link::with('campaign')->select('id', 'campaign_id', 'short_code', 'link_long_url', 'status')->where('short_code', $short_code)->first();

            if($link != null){

                $this->validateIfActiveLink($link);

                Cache::forever('link_'.$short_code, $link);

                $this->addVisit($link->id);

                return redirect()->away((is_null(parse_url($link->link_long_url, PHP_URL_HOST)) ? '//' : '').$link->link_long_url);
            }
            else{
                return view('base.error_link');
            }
        }
    }

    function validateIfActiveLink($link = null){
        if($link != null){
            if($link->status == 0){
                return view('base.error_link');
            }

            if($link->campaign->status == 0){
                return view('base.error_link');
            }

            return true;
        }
        return false;
    }

    function addVisit($link_id = null){
        if($link_id != null){
            $visit = new LinkVisit();
            $visit->link_id = $link_id;
            $visit->ip = request()->ip();
            
            if($visit->save()){
                return true;
            }
            else{
                return false;
            }
        }   
        else{
            return falsa;
        }
    }
}
