<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Campaign;
use stdClass;

class CampaignsController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('links')->orderBy('id', 'DESC')->get();
	    return response()->json($campaigns, 200)->header('Content-Type', 'application/vnd.api+json');
    }

    public function store(Request $request)
    {
        try
        {        	
        	//Objecto de respuesta
        	$response = new stdClass();
    		$response->status =  false;
    		$response->message = "";
    		$response->data = [];

            $request_data = $request->all();

    		//Se genera el objeto Link
            $campaign_object = new Campaign();
            $campaign_object->campaign_name = $request_data['campaign_name'];
            $campaign_object->campaign_description = $request_data['campaign_description'];
            $campaign_object->status = 1;

            if($campaign_object->save()){

                $campaign = Campaign::with('links')->where('id', $campaign_object->id)->orderBy('id', 'DESC')->first();
            	$response->status = true;
            	$response->status = "Campaign created successfull";
            	$response->data = $campaign;
            }
            else{
            	$response->status = false;
            	$response->status = "Error while creating campaign, try again";
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

    		//Se genera el objeto Campaign
            $campaign = Campaign::with('links')->where('id', $id)->first();
            
            if($campaign != null){
	    		$campaign->campaign_name = $request_data['campaign_name'];
	    		$campaign->campaign_description = $request_data['campaign_description'];
                $campaign->status = $request_data['status'];

	    		if($campaign->save()){

                    $this->modifyLinksByCampaign($campaign);

                    $updated_campaign = Campaign::with('links')->where('id', $campaign->id)->orderBy('id', 'DESC')->first();

	            	$response->status = true;
	            	$response->status = "Campaign updated successfull";
	            	$response->data = $updated_campaign;
	            }
	            else{
	            	$response->status = false;
	            	$response->status = "Error while updating campaign, try again";
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

            $campaign = Campaign::findorfail($id);

            if($campaign){

                foreach($campaign->links as $link){
                    foreach ($link->link_visits as $visit){
                        $visit->delete();
                    }

                    $link->delete();
                }

                if($campaign->delete()){
                    $response->status = true;
                    $response->message = "Campaign deleted succesfull";
                    $response->data['id'] = $campaign->id;
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
    function modifyLinksByCampaign($campaign = null){
        if($campaign != null){
            if($campaign->status == false){
                foreach($campaign->links as $link){
                    $link->status = false;
                    $link->save();
                }
            }
            else{
                foreach($campaign->links as $link){
                    $link->status = true;
                    $link->save();
                }
            }
        }
    }
}
