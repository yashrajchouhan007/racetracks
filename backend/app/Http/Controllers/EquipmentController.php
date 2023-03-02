<?php

namespace App\Http\Controllers;

// Modules
use App\Models\Equipments;
use App\Models\Racetracks;
use App\Models\Racetrack_equipments;

// Models
use Illuminate\Http\Request;

class EquipmentController extends Controller
{

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

        if ($request->has('category_id')) {
            $equipment->where("category_id", $request->category_id);
            $page_url_endpoint .= "&category_id=" . $request->category_id;
        }

        if ($request->has('search')) {
            $equipment->where('name', 'LIKE', "%{$request->search}%");
            $page_url_endpoint .= "&search=" . $request->search;
        }

        $final_data = $equipment->paginate(set_limit());
        $encoded_data = json_decode(json_encode($final_data), true);
        $encoded_equipments = get_modified_pagination_urls($encoded_data, $page_url_endpoint);

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
     * Change equippment status
     * @param [integer] id
     * @param [string] status
     */
    public function change_eq_status(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.change_equip_status'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $equipment = Equipments::find($request->id);
            if (isset($equipment) && !empty($equipment)) {
                $equipment->status = $request->status;
                $done = $equipment->save();
                if ($done) {
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
     * Get equipment details
     * @param [integer] id
     */
    public function eq_details(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.equip_details'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $equipment = Equipments::with(['category', 'type'])->find($request->id);
            if (!isset($equipment) || empty($equipment)) {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            } else {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC_DET'), $equipment);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add New Equipment to Racetrack
     * @param [integer] ractrack_id
     * @param [integer] equipment_id
     */
    public function add_equipment_to_racetrack(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_equip_to_rt'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $racetrack = Racetracks::find($request->racetrack_id);
            if (isset($racetrack) && !empty($racetrack)) {
                $equipment = Equipments::find($request->equipment_id);
                if (!isset($equipment) || empty($equipment)) {
                    $code = config('globals.ERR_CD');
                    $response = make_response(false, $code, config('globals.INVD') . " (equipment_id)", []);
                } else {
                    $existing_re = Racetrack_equipments::where(['equipments_id' => $request->equipment_id, 'racetracks_id' => $request->racetrack_id])->get()->toArray();
                    if (isset($existing_re) && !empty($existing_re)) {
                        $code = config('globals.ERR_CD');
                        $response = make_response(false, $code, "This equipment already added to this racetrack", []);
                    } else {
                        $re_array = ['racetracks_id' => $request->racetrack_id, 'equipments_id' => $request->equipment_id];
                        $racetrack_eq_added = Racetrack_equipments::create($re_array);
                        if ($racetrack_eq_added) {
                            $code = config('globals.SUCC_CD');
                            $response = make_response(true, $code, config('globals.SUCC'), []);
                        } else {
                            $code = config('globals.ERR_CD');
                            $response = make_response(false, $code, config('globals.ERR'), []);
                        }
                    }
                }
            } else {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD') . " (racetrack_id)", []);
            }
        }
        return response()->json($response, $code);
    }
}
