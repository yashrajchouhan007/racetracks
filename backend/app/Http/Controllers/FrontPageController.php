<?php

namespace App\Http\Controllers;

// Modules
use App\Models\Contacts;
use App\Models\Equipments;
use App\Models\Racetracks;
use App\Models\Servicemans;
use App\Models\Types;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// Models
use Mail;

class FrontPageController extends Controller
{

    /**
     * Racetrack Search on Landing Page
     * @return [array] racetracks array according to search criteria
     */
    public function index(Request $request)
    {
        $page_url_endpoint = "";
        if ($request->has('type')) {
            $type = $request->type;
            $page_url_endpoint .= "&type=" . $type;
            switch ($type) {
                case 'Racetrack':
                    $racetracks = Racetracks::with(['type', 'owner', 'racetrack_equipments', 'racetrack_equipments.equipments', 'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans']);
                    if ($request->has('name') && !empty($request->name)) {
                        $racetracks->where('name', 'LIKE', "%{$request->name}%");
                        $page_url_endpoint .= "&name=" . $request->name;
                    }
                    if ($request->has('location') && !empty($request->location)) {
                        $racetracks->where('city', $request->location);
                        $page_url_endpoint .= "&location=" . $request->location;
                    }
                    if ($request->has('rating') && !empty($request->rating)) {
                        $racetracks->where('rating', $request->rating);
                        $page_url_endpoint .= "&rating=" . $request->rating;
                    }
                    if ($request->has('start_date') && $request->has('end_date')) {
                        if (!empty($request->start_date) && !empty($request->start_date)) {
                            $racetracks->whereDate('allocation_start_date', '>=', "{$request->start_date}");
                            $racetracks->whereDate('allocation_end_date', '<=', "{$request->end_date}");
                            $page_url_endpoint .= "&start_date=" . $request->start_date . "&end_date=" . $request->end_date;
                        }
                    }
                    if ($request->has('distance') && !empty($request->distance)) {
                        $racetracks->where('distance','<', $request->distance);
                        $page_url_endpoint .= "&distance=" . $request->distance;
                    }

                    $data = $racetracks->orderBy("id", "desc")->paginate(set_limit());
                    $typesArray = array();        
                    foreach ($data as $row) {
                            $rset = Types::find(json_decode($row->type_id,true));
                            foreach ($rset as $set)
                            {
                                array_push($typesArray,array('id'=>$set->id,'name'=>$set->name));
                            }
                            $row->type_id = json_encode($typesArray);
                        }         
                    break;
                case 'Equipment':
                    $equipments = Equipments::with(['category', 'type']);
                    if ($request->has('name') && !empty($request->name)) {
                        $equipments->where('name', 'LIKE', "%{$request->name}%");
                        $page_url_endpoint .= "&name=" . $request->name;
                    }
                    if ($request->has('location') && !empty($request->location)) {
                        $equipments->where('city', $request->location);
                        $page_url_endpoint .= "&location=" . $request->location;
                    }
                    if ($request->has('rating') && !empty($request->rating)) {
                        $equipments->where('rating', $request->rating);
                        $page_url_endpoint .= "&rating=" . $request->rating;
                    }
                    if ($request->has('start_date') && $request->has('end_date')) {
                        if (!empty($request->start_date) && !empty($request->start_date)) {
                            $equipments->whereDate('allocation_start_date', '>=', "{$request->start_date}");
                            $equipments->whereDate('allocation_end_date', '<=', "{$request->end_date}");
                            $page_url_endpoint .= "&start_date=" . $request->start_date . "&end_date=" . $request->end_date;
                        }
                    }
                    $data = $equipments->orderBy("id", "desc")->paginate(set_limit());
                    break;
                case "Serviceman":
                    $servicemans = Servicemans::with([]);
                    if ($request->has('name') && !empty($request->name)) {
                        $servicemans->where('name', 'LIKE', "%{$request->name}%");
                        $page_url_endpoint .= "&name=" . $request->name;
                    }
                    if ($request->has('location') && !empty($request->location)) {
                        $servicemans->where('city', $request->location);
                        $page_url_endpoint .= "&location=" . $request->location;
                    }
                    if ($request->has('rating') && !empty($request->rating)) {
                        $servicemans->where('rating', $request->rating);
                        $page_url_endpoint .= "&rating=" . $request->rating;
                    }
                    if ($request->has('start_date') && $request->has('end_date')) {
                        if (!empty($request->start_date) && !empty($request->start_date)) {
                            $servicemans->whereDate('allocation_start_date', '>=', "{$request->start_date}");
                            $servicemans->whereDate('allocation_end_date', '<=', "{$request->end_date}");
                            $page_url_endpoint .= "&start_date=" . $request->start_date . "&end_date=" . $request->end_date;
                        }
                    }
                    $data = $servicemans->orderBy("id", "desc")->paginate(set_limit());
                    break;
                case "All":
                    $final_data = [];
                    // Racetracks
                    $racetracks = Racetracks::with(['type', 'owner', 'racetrack_equipments', 'racetrack_equipments.equipments', 'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans']);
                    if ($request->has('name') && !empty($request->name)) {
                        $racetracks->where('name', 'LIKE', "%{$request->name}%");
                    }
                    if ($request->has('location') && !empty($request->location)) {
                        $racetracks->where('city', $request->location);
                    }
                    if ($request->has('rating') && !empty($request->rating)) {
                        $racetracks->where('rating', $request->rating);
                    }
                    if ($request->has('start_date') && $request->has('end_date')) {
                        if (!empty($request->start_date) && !empty($request->start_date)) {
                            $racetracks->whereDate('allocation_start_date', '>=', "{$request->start_date}");
                            $racetracks->whereDate('allocation_end_date', '<=', "{$request->end_date}");
                        }
                    }
                    $racetrack_data = $racetracks->orderBy("id", "desc")->get();
                    if ($racetrack_data) {
                        foreach ($racetrack_data as $keyOne => $valueOne) {
                            $valueOne->entity_type = "Racetrack";
                            array_push($final_data, $valueOne);
                        }
                    }

                    // Equipments
                    $equipments = Equipments::with(['category', 'type']);
                    if ($request->has('name') && !empty($request->name)) {
                        $equipments->where('name', 'LIKE', "%{$request->name}%");
                    }
                    if ($request->has('location') && !empty($request->location)) {
                        $equipments->where('city', $request->location);
                    }
                    if ($request->has('rating') && !empty($request->rating)) {
                        $equipments->where('rating', $request->rating);
                    }
                    if ($request->has('start_date') && $request->has('end_date')) {
                        if (!empty($request->start_date) && !empty($request->start_date)) {
                            $equipments->whereDate('allocation_start_date', '>=', "{$request->start_date}");
                            $equipments->whereDate('allocation_end_date', '<=', "{$request->end_date}");
                        }

                    }
                    $equipment_data = $equipments->orderBy("id", "desc")->get();
                    if ($equipment_data) {
                        foreach ($equipment_data as $keyTwo => $valueTwo) {
                            $valueTwo->entity_type = "Equipment";
                            array_push($final_data, $valueTwo);
                        }
                    }

                    // Servicemans
                    $servicemans = Servicemans::with([]);
                    if ($request->has('name') && !empty($request->name)) {
                        $servicemans->where('name', 'LIKE', "%{$request->name}%");
                    }
                    if ($request->has('location') && !empty($request->location)) {
                        $servicemans->where('city', $request->location);
                    }
                    if ($request->has('rating') && !empty($request->rating)) {
                        $servicemans->where('rating', $request->rating);
                    }
                    if ($request->has('start_date') && $request->has('end_date')) {
                        if (!empty($request->start_date) && !empty($request->start_date)) {
                            $servicemans->whereDate('allocation_start_date', '>=', "{$request->start_date}");
                            $servicemans->whereDate('allocation_end_date', '<=', "{$request->end_date}");
                        }
                    }
                    $sm_data = $servicemans->orderBy("id", "desc")->get();
                    if ($sm_data) {
                        foreach ($sm_data as $keyThree => $valueThree) {
                            $valueThree->entity_type = "Serviceman";
                            array_push($final_data, $valueThree);
                        }
                    }
                    $page_url_endpoint .= "&name=" . $request->name;
                    $page_url_endpoint .= "&location=" . $request->location;
                    $page_url_endpoint .= "&rating=" . $request->rating;
                    $page_url_endpoint .= "&start_date=" . $request->start_date . "&end_date=" . $request->end_date;
                    $collection = new Collection($final_data);
                    $data = new LengthAwarePaginator($collection->forPage($request->page ? $request->page : 1, 5), $collection->count(), 5, $request->page ? $request->page : 1);
                    break;
            }
        }

        if ($data) {
            $encoded_data = json_decode(json_encode($data), true);
            $encoded_racetracks = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_racetracks);
        } else {
            $code = config('message.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_racetracks);
        }

        return response()->json($response, $code);
    }

    /**
     * Racetrack Details on Landing Page
     * @return {object} racetrack details
     */
    public function racetrack_details(Request $request, $racetrack_id)
    {
        if (isset($racetrack_id) && !empty($racetrack_id)) {
            $details = Racetracks::with(
                [
                    'owner', 'type', 'racetrack_equipments', 'racetrack_equipments.equipments',
                    'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans',
                    'racetrack_servicemans.servicemans',
                ]
            )->find($racetrack_id);
            if ($details) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC_DET'), $details);
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR_DET'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Featured Racetracks on Landing Page
     * @return [array] racetracks array
     */
    public function featured_racetracks(Request $request)
    {
        $racetracks = Racetracks::with(
            [
                'type', 'owner', 'racetrack_equipments', 'racetrack_equipments.equipments',
                'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans',
            ]
        )->where('is_featured', 1)->get();

        if ($racetracks) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $racetracks);
        } else {
            $code = config('message.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $racetracks);
        }
        return response()->json($response, $code);
    }

    /**
     * Contact Us on Details Page
     * @return [array]
     */
    public function contact_us(Request $request)
    {
        $request_data = $request->all();
        $entity_id = $request_data['entity_id'];
        $entity_type = $request_data['entity_type'];
        if (isset($entity_id) && !empty($entity_id)) {
            if (isset($entity_type) && !empty($entity_type)) {
                switch ($entity_type) {
                    case 'Racetrack':
                        $data = Racetracks::with(['type', 'owner', 'racetrack_equipments', 'racetrack_equipments.equipments', 'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans'])->find($entity_id);
                        break;
                    case 'Equipment':
                        $data = Equipments::with(['category', 'type'])->find($entity_id);
                        break;
                    case 'Serviceman':
                        $data = Servicemans::with([])->find($entity_id);
                        break;
                }
                if (Contacts::create($request_data)) {
                    $mail_data = [
                        'name' => $data->name,
                        'address' => $data->address,
                        'email' => $data->owner_email,
                        'contact' => $data->owner_contact,
                        'city' => $data->city,
                    ];

                    Mail::send('mail', $mail_data, function ($message) use ($request_data, $entity_type) {
                        $message->to($request_data['email']);
                        $message->subject($entity_type . " Details");
                        $message->from('noreply@racetrack.com');
                    });
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.ERR'), []);
                }
                return response()->json($response, $code);
            }
        }
    }

}
