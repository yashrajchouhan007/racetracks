<?php

namespace App\Http\Controllers;

// Modules
use App\Models\Racetracks;
use App\Models\Racetrack_equipments;

// Models
use App\Models\Racetrack_servicemans;
use App\Models\Servicemans;
use App\Models\Specialization;
use Illuminate\Http\Request;

class ServicemanController extends Controller
{

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
           // $full_request['specializations'] = json_encode($full_request['specializations'], false);
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
     * Change serviceman status
     * @param [integer] id
     * @param [string] status
     */
    public function change_sm_status(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.update_sm'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $sm = Servicemans::find($request->id);
            if (isset($sm) && !empty($sm)) {
                $sm->status = $request->status;
                $done = $sm->save();
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
     * Get serviceman details
     * @param [integer] id
     */
    public function sm_details(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.sm_details'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $sm = Servicemans::find($request->id);
            if (!isset($sm) || empty($sm)) {
                $code = config('globals.ERR_CD');
                $response = make_response(false, $code, config('globals.INVD'), []);
            } else {
                $code = config('globals.SUCC_CD');
                $response = make_response(true, $code, config('globals.SUCC_DET'), $sm);
            }
        }
        return response()->json($response, $code);
    }

    /**
     * Add New Serviceman to Racetrack
     * @param [integer] racetrack_id
     * @param [integer] serviceman_id
     */
    public function add_sm_to_racetrack(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.add_sm_to_rt'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            $racetrack = Racetracks::find($request->racetrack_id);
            if (isset($racetrack) && !empty($racetrack)) {
                $sm = Servicemans::find($request->serviceman_id);
                if (!isset($sm) || empty($sm)) {
                    $code = config('globals.ERR_CD');
                    $response = make_response(false, $code, config('globals.INVD') . " (serviceman_id)", []);
                } else {
                    $existing_rs = Racetrack_servicemans::where(['servicemans_id' => $request->serviceman_id, 'racetracks_id' => $request->racetrack_id])->get()->toArray();
                    if (isset($existing_rs) && !empty($existing_rs)) {
                        $code = config('globals.ERR_CD');
                        $response = make_response(false, $code, "This serviceman already added to this racetrack", []);
                    } else {
                        $rs_array = ['racetracks_id' => $request->racetrack_id, 'servicemans_id' => $request->serviceman_id];
                        $racetrack_sm_added = Racetrack_servicemans::create($rs_array);
                        if ($racetrack_sm_added) {
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

     /**
     * Delete Serviceman
     * @param [integer] id
     */
    public function delete_serviceman(Request $request)
    {
        $validator = validate_request($request->all(), config('validations.delete_user'));
        if ($validator->fails()) {
            $code = config('globals.ERR_VD_CD');
            $response = make_response(false, $code, config('globals.VLD_ERR'), $validator->errors());
        } else {
            if (Servicemans::find($request->id)) {
                $sm_deleted = Servicemans::destroy($request->id);
                if ($sm_deleted) {
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

}
