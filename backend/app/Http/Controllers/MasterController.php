<?php
namespace App\Http\Controllers;

// Modules
use App\Models\Categories;
use App\Models\Cities;
use App\Models\Equipments;

// Models
use App\Models\Racetracks;
use App\Models\Racetrack_equipments;
use App\Models\Racetrack_servicemans;
use App\Models\Servicemans;
use App\Models\Subcategories;
use App\Models\Types;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MasterController extends Controller
{
    /**
     * Master categories
     * @return [array] master categories
     */
    public function categories(Request $request)
    {
        $categories = Categories::orderBy('id', 'desc');
        $page_url_endpoint = "";
        if ($request->has('status')) {
            $categories->where("status", $request->status);
            $page_url_endpoint .= "&status=" . $request->status;
        }
        if ($request->has('pagination') && $request->pagination == 1) {
            $page_url_endpoint .= "&pagination=1";
            $final_categories = $categories->paginate(set_limit());
            $encoded_data = json_decode(json_encode($final_categories), true);
            $encoded_categories = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
        } else {
            $encoded_categories = $categories->get();
        }

        if ($categories) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_categories);
        } else {
            $code = config('message.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_categories);
        }
        return response()->json($response, $code);
    }

    /**
     * Master subcategories
     * @return [array] master subcategories
     */
    public function subcategories(Request $request)
    {
        $subcategories = Subcategories::with('categories')->orderBy('id', 'desc');
        $page_url_endpoint = "";
        if ($request->has('status')) {
            $subcategories->where("status", $request->status);
            $page_url_endpoint .= "&status=" . $request->status;
        }

        if ($request->has('pagination') && $request->pagination == 1) {
            $page_url_endpoint .= "&pagination=1";
            $final_subcategories = $subcategories->paginate(set_limit());
            $encoded_data = json_decode(json_encode($final_subcategories), true);
            $encoded_subcategories = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
        } else {
            $encoded_subcategories = $subcategories->get();
        }

        if ($subcategories) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_subcategories);
        } else {
            $code = config('message.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_subcategories);
        }
        return response()->json($response, $code);
    }

    /**
     * Masters types
     * @return [array] master types
     */
    public function types(Request $request)
    {
        $types = Types::orderBy('id', 'desc');
        $page_url_endpoint = "";
        if ($request->has('status')) {
            $types->where("status", $request->status);
            $page_url_endpoint .= "&status=" . $request->status;
        }
        if ($request->has('pagination') && $request->pagination == 1) {
            $page_url_endpoint .= "&pagination=1";
            $final_types = $types->paginate(set_limit());
            $encoded_data = json_decode(json_encode($final_types), true);
            $encoded_types = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
        } else {
            $encoded_types = $types->get();
        }

        if ($types) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_types);
        } else {
            $code = config('message.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_types);
        }
        return response()->json($response, $code);
    }

    /**
     * Master racetracks with Search and Filter for Type and Category
     * @return [array] master racetracks array
     */
    public function racetracks(Request $request)
    {
        $page_url_endpoint = "";
        $racetrack = Racetracks::with(
            [
                'type', 'owner', 'racetrack_equipments', 'racetrack_equipments.equipments',
                'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans', 'racetrack_servicemans.servicemans',
            ]
        );
        if ($request->has('type_id')) {
            $racetrack->where('type_id', $request->type_id);
            $page_url_endpoint .= "&type_id=" . $request->type_id;
        }

        if ($request->has('category_id')) {
            $racetrack->whereHas(
                'racetrack_equipments',
                function ($racetrack_equipments) use ($request) {
                    $racetrack_equipments->whereHas(
                        'equipments',
                        function ($equipments) use ($request) {
                            if ($request->has('category_id')) {
                                $equipments->where('category_id', $request->category_id);
                            }
                        }
                    );
                }
            );
            $page_url_endpoint .= "&category_id=" . $request->category_id;
        }

        // Search
        if ($request->has('search')) {
            $racetrack->where('name', 'LIKE', "%{$request->search}%");
            $racetrack->orWhere('desc', 'LIKE', "%{$request->search}");
            $racetrack->orWhere('address', 'LIKE', "%{$request->search}");
            $page_url_endpoint .= "&search=" . $request->search;
        }

        if ($racetrack) {
            $final_data = $racetrack->orderBy("id", "desc")
                ->paginate(set_limit());
            $typesArray = array();        
            foreach ($final_data as $row) {
                    $rset = Types::find(json_decode($row->type_id,true));
                    foreach ($rset as $set)
                    {
                        array_push($typesArray,array('id'=>$set->id,'name'=>$set->name));
                    }
                    $row->type_id = json_encode($typesArray);
                    $typesArray = array();
                }  
            $encoded_data = json_decode(json_encode($final_data), true);
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
     * Masters equipments
     * @return [array] master equipments array
     */
    public function equipments(Request $request)
    {
        $equipment = Equipments::with(['category', 'type'])->orderBy("id", "desc");
        $page_url_endpoint = "";
        if ($request->has('type_id')) {
            $equipment->where("type_id", $request->type_id);
            $page_url_endpoint .= "&type_id=" . $request->type_id;
        }

        if ($request->has('pagination') && $request->pagination == 1) {
            $page_url_endpoint .= "&pagination=1";
            $final_data = $equipment->paginate(set_limit());
            $encoded_data = json_decode(json_encode($final_data), true);
            $encoded_equipments = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
        } else {
            $encoded_equipments = $equipment->get();
        }

        if ($equipment) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_equipments);
        } else {
            $code = config('message.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_equipments);
        }
        return response()->json($response, $code);
    }

    /**
     * Masters servicemans
     * @return [array] master servicemans array
     */
    public function servicemans(Request $request)
    {
        $smans = Servicemans::orderBy('id', 'desc');
        if ($request->has('pagination') && $request->pagination == 1) {
            $smans = $smans->paginate(10);
        } else {
            $smans = $smans->get();
        }

        if ($smans) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $smans);
        } else {
            $code = config('globals.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $smans);
        }
        return response()->json($response, $code);
    }

    /**
     * Add master category
     * @param [string] name
     * @param [string] desc
     */
    public function add_category(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_category'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $category = Categories::create($request->all());
            if ($category->save()) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update master category
     * @param [integer] id
     * @param [string] name
     * @param [string] desc
     */
    public function update_category(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_category'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $category = Categories::find($request->id);
            if ($category) {
                $category->name = $request->name;
                $category->desc = $request->desc;
                if ($category->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add master subcategory
     * @param [string] name
     * @param [string] desc
     * @param [integer] category_id
     */
    public function add_subcategory(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_subcategory'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $request_inputs = $request->all();
            $request_inputs['categories_id'] = $request_inputs['category_id'];
            unset($request_inputs['category_id']);
            $subcategory = Subcategories::create($request_inputs);
            if ($subcategory->save()) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update master subcategory
     * @param [integer] id
     * @param [string] name
     * @param [string] desc
     * @param [integer] category_id
     */
    public function update_subcategory(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_subcategory'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $subcategory = Subcategories::find($request->id);
            if ($subcategory) {
                $subcategory->name = $request->name;
                $subcategory->desc = $request->desc;
                if ($subcategory->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add master types
     * @param [string] name
     * @param [string] desc
     */
    public function add_type(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_type'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $type = Types::create($request->all());
            if ($type->save()) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update master type
     * @param [integer] id
     * @param [string] name
     * @param [string] desc
     */
    public function update_type(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_type'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $type = Types::find($request->id);
            if ($type) {
                $type->name = $request->name;
                $type->desc = $request->desc;
                if ($type->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add master racetrack with equipments and servicemans
     * @param [string] name
     * @param [string] desc
     * @param [string] distance
     * @param [integer] type_id
     * @param [string] address
     * @param [string] city
     * @param [array] images
     * @param [string] video_link
     * @param [string] allocation_start_date
     * @param [string] allocation_end_date
     * @param [string] allocation_time
     * @param [integer] owner_id
     * @param [string] owner_email
     * @param [string] owner_contact
     * @param [string] responsible_person
     * @param [integer] rating
     * @param [integer] shared
     * @param [integer] is_featured
     * @param [array] equipments
     * @param [array] servicemans
     */
    public function add_racetrack(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_rt'));
        if ($validator->fails()) {
            //$validator->errors()
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            if ($request->has('type_id'))
            {
                $type_id_json = json_encode($request->type_id);
            }
            else
            {
              $type_id_json = '';  
            }
            $racetrack_data = [
                'name' => $request->name, 'desc' => $request->desc, 'type_id' => $type_id_json, 'distance' => $request->distance,
                'address' => $request->address, 'video_link' => $request->video_link, 'allocation_start_date' => $request->allocation_start_date,
                'allocation_end_date' => $request->allocation_end_date, 'allocation_time' => $request->allocation_time, 'owner_id' => $request->owner_id,
                'owner_email' => $request->owner_email, 'owner_contact' => $request->owner_contact, 'responsible_person' => $request->responsible_person,
                'rating' => $request->rating, 'shared' => $request->shared, 'is_featured' => $request->is_featured, 'city' => $request->city,
            ];

            // For Multiple Images
            if ($request->has('images')) {
                $image_uid = md5(uniqid(rand(), true));
                $image_array = $request->images;
                $data = upload_multiple_images($image_array, $image_uid, "Racetracks");
                $racetrack_data['image_uid'] = $image_uid;
                $racetrack_data['images'] = json_encode($data);
            }
            $rt = Racetracks::create($racetrack_data);
            if ($rt->save()) {
                if ($request->has('equipments')) {
                    foreach ($request->equipments as $equip) {
                        $racetrack_equips = ['racetracks_id' => $rt->id, 'equipments_id' => $equip];
                        $rt_equip = (Racetrack_equipments::create($racetrack_equips))->save();
                    }
                }
                if ($request->has('servicemans')) {
                    foreach ($request->servicemans as $smans) {
                        $racetrack_smans = ['racetracks_id' => $rt->id, 'servicemans_id' => $smans];
                        $rt_smans = (Racetrack_servicemans::create($racetrack_smans))->save();
                    }
                }
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), $racetrack_data);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), $racetrack_data);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update master racetrack
     * @param [integer] id
     * @param [string] name
     * @param [string] desc
     * @param [string] distance
     * @param [integer] type_id
     * @param [string] address
     * @param [string] video_link
     * @param [string] allocation_start_date
     * @param [string] allocation_end_date
     * @param [string] allocation_time
     * @param [integer] owner_id
     * @param [string] owner_email
     * @param [string] owner_contact
     * @param [string] responsible_person
     * @param [integer] rating
     * @param [integer] shared
     * @param [integer] is_featured
     * @param [array] new_images
     * @param [array] delete_images
     * @param [array] new_equipments
     * @param [array] new_servicemans
     */
    public function update_racetrack(Request $request)
    {
        // echo '<pre>';
        // print_r($request->all());
        // exit;
        $validator = validate_request($request->all(), config('validations.update_rt'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $racetrack = Racetracks::find($request->id);
            if (!isset($racetrack) || empty($racetrack)) {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            } else {
                $racetrack_images = json_decode($racetrack->images);
               // $racetrack_typeid = json_decode($racetrack->type_id);
                $racetrack->name = $request->name;
                $racetrack->desc = $request->desc;
                $racetrack->distance = $request->distance;
                //$racetrack->type_id = $request->type_id;
                $racetrack->address = $request->address;
                $racetrack->video_link = $request->video_link;
                $racetrack->allocation_start_date = $request->allocation_start_date;
                $racetrack->allocation_end_date = $request->allocation_end_date;
                $racetrack->allocation_time = $request->allocation_time;
                $racetrack->owner_id = $request->owner_id;
                $racetrack->owner_email = $request->owner_email;
                $racetrack->owner_contact = $request->owner_contact;
                $racetrack->responsible_person = $request->responsible_person;
                $racetrack->rating = $request->rating;
                $racetrack->shared = $request->shared;
                $racetrack->is_featured = $request->is_featured;
                $racetrack->city = $request->city;

                if ($request->has('delete_images')) {
                    foreach ($request->delete_images as $delete_image) {
                        $elem_key = array_search(json_decode($delete_image), $racetrack_images);
                        unset($racetrack_images[$elem_key]);
                    }
                }
                if ($request->has('new_images')) {
                     
                    $image_array = $request->new_images;
                    $data = upload_multiple_images($image_array, $racetrack->image_uid, "Racetracks");
                    $racetrack_images = array_merge($data, $racetrack_images);
                }
                $racetrack->images = json_encode($racetrack_images);

                if ($request->has('type_id')){
                   // print_r($request->type_id); exit;
                    $data = $request->type_id;
                    $racetrack_typeid = array_unique($data);
                }
                $racetrack->type_id = json_encode($racetrack_typeid);

                if ($request->has('new_equipments')) {
                    $rt_equip = Racetrack_equipments::where("racetracks_id", $request->id)
                        ->delete();
                    if ($rt_equip) {
                        foreach ($request->new_equipments as $equip) {
                            Racetrack_equipments::create(['racetracks_id' => $request->id, 'equipments_id' => $equip])->save();
                        }
                    }
                }
                if ($request->has('new_servicemans')) {
                    $rt_smans = Racetrack_servicemans::where("racetracks_id", $request->id)
                        ->delete();
                    if ($rt_smans) {
                        foreach ($request->new_servicemans as $smans) {
                            Racetrack_servicemans::create(['racetracks_id' => $request->id, 'servicemans_id' => $smans])->save();
                        }
                    }
                }

                if ($racetrack->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Delete racetrack and its associated data
     * @param [integer] id
     */
    public function delete_racetrack(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.delete_rt'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $rt_id = $request->id;
            $racetrack = Racetracks::find($rt_id);
            if ($racetrack) {
                $image_uid = $racetrack->image_uid;
                $rt_deleted = Racetracks::destroy($rt_id);
                if ($rt_deleted) {
                    File::deleteDirectory(public_path() . "/images/Racetracks/$image_uid");
                    Racetrack_equipments::where("racetracks_id", $rt_id)->delete();
                    Racetrack_servicemans::where("racetracks_id", $rt_id)->delete();
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.DLT'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update racetrack availability
     * @param [integer] id
     * @param [string] date_availability_start
     * @param [string] date_availability_end
     * @param [string] time_availability
     */
    public function change_availability(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.change_rt_av'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $rt = Racetracks::find($request->id);
            if (!isset($rt) || empty($rt)) {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            } else {
                $rt->allocation_start_date = $request->allocation_start_date;
                $rt->allocation_end_date = $request->allocation_end_date;
                $rt->allocation_time = $request->allocation_time;
                if ($rt->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.ERR'), []);
                }
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Change entity status
     * @param [integer] id
     * @param [string] status
     * @param [string] type
     */
    public function change_status(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.change_status'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $exis_err = false;
            $type = $request->type;
            if ($type == "Racetrack") {
                $racetrack = Racetracks::find($request->id);
                if (isset($racetrack) && !empty($racetrack)) {
                    $racetrack->status = $request->status;
                    $done = $racetrack->save();
                } else {
                    $exis_err = true;
                }
            } elseif ($type == "Equipment") {
                $equipment = Equipments::find($request->id);
                if (isset($equipment) && !empty($equipment)) {
                    $equipment->status = $request->status;
                    $done = $equipment->save();
                } else {
                    $exis_err = true;
                }
            } elseif ($type == "Serviceman") {
                $serviceman = Servicemans::find($request->id);
                if (isset($serviceman) && !empty($serviceman)) {
                    $serviceman->status = $request->status;
                    $done = $serviceman->save();
                } else {
                    $exis_err = true;
                }
            } elseif ($type == "Category") {
                $category = Categories::find($request->id);
                if (isset($category) && !empty($category)) {
                    $category->status = $request->status;
                    $done = $category->save();
                } else {
                    $exis_err = true;
                }
            } elseif ($type == "Type") {
                $typ = Types::find($request->id);
                if (isset($typ) && !empty($typ)) {
                    $typ->status = $request->status;
                    $done = $typ->save();
                } else {
                    $exis_err = true;
                }
            } elseif ($type == "Subcategory") {
                $sc = Subcategories::find($request->id);
                if (isset($sc) && !empty($sc)) {
                    $sc->status = $request->status;
                    $done = $sc->save();
                } else {
                    $exis_err = true;
                }
            } elseif ($type == "User") {
                $user = Users::find($request->id);
                if (isset($user) && !empty($user)) {
                    $user->status = $request->status;
                    $done = $user->save();
                } else {
                    $exis_err = true;
                }
            }

            if ($exis_err) {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            } else {
                if ($done) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add New Equipment
     * @param [string] name
     * @param [string] desc
     * @param [integer] category_id
     * @param [integer] subcategory_id
     * @param [integer] type_id
     * @param [string] special_instructions
     * @param [string] allocation_start_date
     * @param [string] allocation_end_date
     * @param [string] allocation_time
     * @param [string] contact_no
     * @param [string] email_id
     * @param [string] rating
     * @param [array] images
     * @param [string] video_link
     */

    public function add_equipment(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_equipment'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $request_data = $request->all();
            if ($request->has('video_link')) {
                if ($request->video_link == "N/A" || $request->video_link == "n/a") {
                    unset($request->video_link);
                }
            }
            if ($request->has('images')) {
                $image_uid = md5(uniqid(rand(), true));
                $image_array = $request->images;
                $data = upload_multiple_images($image_array, $image_uid, "Equipments");
                $request_data['image_uid'] = $image_uid;
                $request_data['images'] = json_encode($data, false);
            }
            $equipments = Equipments::create($request_data);
            if ($equipments->save()) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update Equipment
     * @param [integer] id
     * @param [string] name
     * @param [string] desc
     * @param [integer] category_id
     * @param [integer] subcategory_id
     * @param [integer] type_id
     * @param [string] special_instructions
     * @param [string] allocation_start_date
     * @param [string] allocation_end_date
     * @param [string] allocation_time
     * @param [string] contact_no
     * @param [string] email_id
     * @param [string] rating
     * @param [string] video_link
     */
    public function update_equipment(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_equipment'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $equipment = Equipments::find($request->id);
            if ($equipment) {
                $equipment_images = json_decode($equipment->images);
                $equipment->name = $request->name;
                $equipment->desc = $request->desc;
                $equipment->category_id = $request->category_id;
                $equipment->subcategory_id = $request->subcategory_id;
                $equipment->type_id = $request->type_id;
                $equipment->special_instructions = $request->special_instructions;
                $equipment->allocation_start_date = $request->allocation_start_date;
                $equipment->allocation_end_date = $request->allocation_end_date;
                $equipment->allocation_time = $request->allocation_time;
                $equipment->contact_no = $request->contact_no;
                $equipment->email_id = $request->email_id;
                $equipment->rating = $request->rating;

                if ($request->has('video_link')) {
                    if ($request->video_link !== "N/A" || $request->video_link !== "n/a") {
                        $equipment->video_link = $request->video_link;
                    }
                }

                if ($request->has('delete_images')) {
                    foreach ($request->delete_images as $delete_image) {
                        if (!empty($equipment_images)) {
                            $elem_key = array_search(json_decode($delete_image), $equipment_images);
                            unset($equipment_images[$elem_key]);
                        }
                    }
                }
                if ($request->has('new_images')) {
                    $image_array = $request->new_images;
                    $data = upload_multiple_images($image_array, $equipment->image_uid, "Equipments");
                    $equipment_images = array_merge($data, $equipment_images);
                }
                $equipment->images = json_encode($equipment_images);
                if ($equipment->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add New Serviceman
     * @param [string] name
     * @param [string] contact_no
     * @param [string] email_id
     * @param [string] address
     * @param [array] specializations
     * @param [integer] available_hours
     * @param [integer] rating
     */
    public function add_serviceman(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_sm'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $full_request = $request->all();
            $full_request['specializations'] = json_encode($full_request['specializations'], false);
            $smans = Servicemans::create($full_request);
            if ($smans->save()) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update Serviceman
     * @param [integer] id
     * @param [string] name
     * @param [string] contact_no
     * @param [string] email_id
     * @param [string] address
     * @param [array] specializations
     * @param [integer] available_hours
     * @param [integer] rating
     */
    public function update_serviceman(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_sm'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $smans = Servicemans::find($request->id);
            if (isset($smans) && !empty($smans)) {
                $smans->name = $request->name;
                $smans->contact_no = $request->contact_no;
                $smans->email_id = $request->email_id;
                $smans->address = $request->address;
                $smans->specializations = json_encode($request->specializations, false);
                $smans->available_hours = $request->available_hours;
                $smans->rating = $request->rating;
                if ($smans->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('globals.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Create user
     *
     * @param  [string] first_name
     * @param  [string] last_name
     * @param  [string] email_id
     * @param  [integer] user_role
     * @param [string] password
     * @param [string] password_confirmation
     */
    public function add_user(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_user'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $user = Users::create(
                [
                    'first_name' => $request->first_name, 'last_name' => $request->last_name, 'email_id' => $request->email_id,
                    'user_role' => $request->user_role, 'password' => bcrypt($request->password),
                ]
            );

            if ($user->save()) {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Update user
     *
     * @param  [integer] id
     * @param  [string] first_name
     * @param  [string] last_name
     */
    public function update_user(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_user'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $user = Users::find($request->id);
            if ($user) {
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                if ($user->save()) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.UPD_SUCC'), []);
                } else {
                    $code = config('globals.ERR_CD');
                    $response = make_response(false, $code, config('globals.UPD_ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Masters users
     * @return [array] master users array
     */
    public function users(Request $request)
    {
        $users = Users::orderBy('id', 'desc');
        $page_url_endpoint = "";
        if ($request->has('status')) {
            $users->where("status", $request->status);
            $page_url_endpoint .= "&status=" . $request->status;
        }

        if ($request->has('pagination') && $request->pagination == 1) {
            $page_url_endpoint .= "&pagination=1";
            $final_users = $users->paginate(set_limit());
            $encoded_data = json_decode(json_encode($final_users), true);
            $encoded_users = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
        } else {
            $encoded_users = $users->get();
        }

        if ($users) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_users);
        } else {
            $code = config('globals.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_users);
        }
        return response()->json($response, $code);
    }

    /**
     * Delete user
     * @param [integer] id
     */
    public function delete_user(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.delete_user'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            if (Users::find($request->id)) {
                $user_deleted = Users::destroy($request->id);
                if ($user_deleted) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.DLT'), []);
                } else {
                    $code = config('globals.ERR_CD');
                    $response = make_response(true, $code, config('globals.ERR_DLT'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Delete Type
     * @param [integer] id
     */
    public function delete_type(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.delete_user'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            if (Types::find($request->id)) {
                $type_deleted = Types::destroy($request->id);
                if ($type_deleted) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.DLT'), []);
                } else {
                    $code = config('globals.ERR_CD');
                    $response = make_response(true, $code, config('globals.ERR_DLT'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Delete Equipment
     * @param [integer] id
     */
    public function delete_equipment(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.delete_user'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $eq_id = $request->id;
            $equipment = Equipments::find($eq_id);
            if ($equipment) {
                $image_uid = $equipment->image_uid;
                $eq_deleted = Equipments::destroy($eq_id);
                if ($eq_deleted) {
                    File::deleteDirectory(public_path() . "/images/Equipments/$image_uid");
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.DLT'), []);
                } else {
                    $code = config('message.ERR_CD');
                    $response = make_response(false, $code, config('globals.ERR'), []);
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            }
        }
        return response()->json($response, $code);
    }
    /**
     * Get any entity details
     * @param [integer] id
     * @param [string] type
     */
    public function details(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.details'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $exis_err = false;
            $type = $request->type;
            switch ($type) {
                case 'Racetrack':
                    $racetrack = Racetracks::find($request->id);
                    if (!isset($racetrack) || empty($racetrack)) {
                        $exis_err = true;
                    } else {
                        $details = Racetracks::with(
                            [
                                'owner', 'type', 'racetrack_equipments', 'racetrack_equipments.equipments',
                                'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans',
                                'racetrack_servicemans.servicemans',
                            ]
                        )->find($request->id);

                        $type_id = json_decode($details->type_id);
                        $type_list = Types::find($type_id);
                        $type_resp = array();
                        foreach ($type_list as $type) {
                            array_push($type_resp,array('id' =>$type->id,'name'=>$type->name));
                        }
                        $details->type_id = json_encode($type_resp,true);
                    }
                    break;

                case 'Equipment':
                    $equipment = Equipments::find($request->id);
                    if (!isset($equipment) || empty($equipment)) {
                        $exis_err = true;
                    } else {
                        $details = Equipments::with(['category', 'type'])->find($request->id);
                    }
                    break;

                case 'Serviceman':
                    $sm = Servicemans::find($request->id);
                    if (!isset($sm) || empty($sm)) {
                        $exis_err = true;
                    } else {
                        $details = Servicemans::find($request->id);
                    }
                    break;

                case 'User':
                    $user = Users::find($request->id);
                    if (!isset($user) || empty($user)) {
                        $exis_err = true;
                    } else {
                        $details = Users::find($request->id);
                    }
                    break;
            }

            if ($exis_err) {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            } else {
                if ($details) {
                    $code = config('globals.SUCC_CD');
                    $response = make_response(true, $code, config('globals.SUCC_DET'), $details);
                } else {
                    $code = config('globals.ERR_CD');
                    $response = make_response(false, $code, config('globals.ERR_DET'), []);
                }
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add Indian City
     * @param [array] city_data
     */
    public function add_city(Request $request)
    {
        $city_data = $request->city_data;
        foreach ($city_data as $ckey => $cvalue) {
            Cities::create(['city' => $cvalue['name'], 'state' => $cvalue['state']]);
        }
        $code = config('globals.SUCC_CD');
        $response = make_response(true, $code, config('globals.SUCC'), $details);
        return response()->json($response, $code);
    }

    /**
     * Indian Cities List By State
     * @param [string] state_name
     */
    public function cities(Request $request)
    {
        $state_name = $request->state;
        $cities = [];
        if ($cities = Cities::where('state', $state_name)->select(['state', 'city'])->orderBy("city", "asc")->get()) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $cities);
        } else {
            $code = config('globals.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), $cities);
        }
        return response()->json($response, $code);
    }

    /**
     * Indian States
     */
    public function states()
    {
        //$country_name = $request->country;
        if ($states = Cities::groupBy("state")->select("state")->get()) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $states);
        } else {
            $code = config('globals.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), []);
        }
        return response()->json($response, $code);
    }

    /**
     * Indian States
     */
    public function countries()
    {
        if ($country = Cities::groupBy("country")->select("country")->get()) {
            $code = config('globals.SUCC_CD');
            $response = make_response(true, $code, config('globals.SUCC_LST'), $country);
        } else {
            $code = config('globals.ERR_CD');
            $response = make_response(false, $code, config('globals.ERR_LST'), []);
        }
        return response()->json($response, $code);
    }
}
