<?php

namespace App\Http\Controllers;

// Modules
use App\Models\Equipments;
use App\Models\Racetracks;
use App\Models\Racetrack_equipments;

// Models
use App\Models\Racetrack_servicemans;
use App\Models\Servicemans;
use App\Models\Users;
use Illuminate\Http\Request;

class RacetrackOwnerController extends Controller
{

    /**
     * Get owner's all racetracks list
     * @param [integer] id
     */
    public function racetracks(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.get_owner_racetracks'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $page_url_endpoint = "&owner_id=" . $request->owner_id;
            $racetrack = Racetracks::with(
                [
                    'type', 'owner', 'racetrack_equipments', 'racetrack_equipments.equipments',
                    'racetrack_equipments.equipments.category', 'racetrack_equipments.equipments.type', 'racetrack_servicemans',
                ]
            )->where('owner_id', $request->owner_id);
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
                $encoded_data = json_decode(json_encode($final_data), true);
                $encoded_racetracks = get_modified_pagination_urls($encoded_data, $page_url_endpoint);
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC_LST'), $encoded_racetracks);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR_LST'), $encoded_racetracks);
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Add master racetrack with equipments and servicemans
     * @param [string] namespace
     * @param [string] desc
     * @param [string] distance
     * @param [integer] type_id
     * @param [string] address
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
     * @param [array] equipments
     * @param [array] servicemans
     */
    public function add_racetrack(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_rt'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $racetrack_data = [
                'name' => $request->name, 'desc' => $request->desc, 'type_id' => $request->type_id, 'distance' => $request->distance,
                'address' => $request->address, 'video_link' => $request->video_link, 'allocation_start_date' => $request->allocation_start_date,
                'allocation_end_date' => $request->allocation_end_date, 'allocation_time' => $request->allocation_time, 'owner_id' => $request->owner_id,
                'owner_email' => $request->owner_email, 'owner_contact' => $request->owner_contact, 'responsible_person' => $request->responsible_person,
                'rating' => $request->rating, 'shared' => $request->shared,
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
                $response = make_response(true, $code, config('globals.SUCC'), []);
            } else {
                $code = config('message.ERR_CD');
                $response = make_response(false, $code, config('globals.ERR'), []);
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
     * @param [array] new_images
     * @param [array] delete_images
     * @param [array] new_equipments
     * @param [array] new_servicemans
     */
    public function update_racetrack(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_type'));
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
                $racetrack->name = $request->name;
                $racetrack->desc = $request->desc;
                $racetrack->distance = $request->distance;
                $racetrack->type_id = $request->type_id;
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
                $equipment->video_link = $request->video_link;

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

}
