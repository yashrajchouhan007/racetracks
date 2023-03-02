<?php
namespace App\Http\Controllers;

use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
    public function signup(Request $request)
    {
        $validation_rules = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email_id' => 'required|string|email|unique:users',
        'user_role'=>'required|string',
        'password' => 'required|string|confirmed'];

        $validator = Validator::make($request->all(),$validation_rules);  
        if ($validator->fails()){
            $code = 422; 
        	$response = [
        		'status'=> false,
        		'code'=>$code,
                'message' => 'You have some of the validation errors. Please Check', 
                'data'=>$validator->errors()
        	]; 
            return response()->json($response, $code);
        }

        $user = new Users([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email_id' => $request->email_id,
            'user_role'=>$request->user_role,
            'password' => bcrypt($request->password)
        ]);
        if ($user->save()){
        	$code = 200; 
        	$response = [
        		'status'=> true,
        		'code'=>$code,
                'message' => 'New racetrack user created', 
                'data'=>[]
        	]; 

        	return response()->json($response, $code);
        }
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email_id
     * @param  [string] password
     * @return [json] auth data object
     */
    public function login(Request $request)
    {
        $validation_rules = [
            'email_id' => 'required|string|email',
            'password' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            $code = 422;
            $response = [
                'status' => false,
                'code' => $code,
                'message' => 'You have some of the validation errors. Please Check',
                'data' => $validator->errors(),
            ];
            return response()->json($response, $code);
        }
        if (!Auth::attempt($request->all())) {
            $code = 401;
            $response = [
                'status' => false,
                'code' => $code,
                'message' => 'Unauthorized. Invalid Username or Password',
            ];
            return response()->json($response, $code);
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        if ($tokenResult) {
            $code = 200;
            $response = [
                'status' => true,
                'code' => $code,
                'message' => 'User login successful',
                'data' => [
                    'access_token' => $tokenResult->accessToken,
                    'token_type' => 'Bearer',
                    'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                    'userdata' => $user,
                ],
            ];
            return response()->json($response, $code);
        }
    }
}
