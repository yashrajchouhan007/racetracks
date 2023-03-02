<?php
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use \Carbon\Carbon;

/**
 * Change date format
 *
 * @param [string] date
 * @param [string] date_format
 * @return [string] formatted date
 */
function changeDateFormat($date, $date_format)
{
    return Carbon::createFromFormat('Y-m-d', $date)->format($date_format);
}

/**
 * Multiple file/image upload
 *
 * @param [array] image_array
 * @param [string] nm(name of entity)
 * @return [array] data
 */
function upload_multiple_images($image_array, $nm, $type)
{
    $data = [];
    foreach ($image_array as $key => $file) {
        $name = time() . "$key." . $file->extension();
        $path = public_path() . "/images/$type/$nm";
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        $file->move($path, $name);
        $data[$key] = $name;
    }
    return $data;
}

/**
 * Modify pagination urls
 *
 * @param [array] data
 * @param [string] url
 * @return [array] data
 */
function get_modified_pagination_urls($data, $url)
{
    $data['first_page_url'] = !empty($data['first_page_url']) ? $data['first_page_url'] . $url : "";
    $data['last_page_url'] = !empty($data['last_page_url']) ? $data['last_page_url'] . $url : "";
    $data['next_page_url'] = !empty($data['next_page_url']) ? $data['next_page_url'] . $url : "";
    $data['prev_page_url'] = !empty($data['prev_page_url']) ? $data['prev_page_url'] . $url : "";
    $data['data'] = array_values($data['data']); 
    return $data;
}

/**
 * Make Response/Prepare response 
 * @param [array] status
 * @param [string] code
 * @param [string] message
 * @param [array] data
 * @return [array] response
 */
function make_response($status, $code, $message, $data)
{
    return [
        'status' => $status,
        'code' => $code,
        'message' => $message,
        'data' => $data
    ];
}

/**
 * Set global limit 
 * @return [integer] limit
 */
function set_limit()
{
    return config('globals.GLB_LIMIT');
}

/**
 * Validate request 
 * @param [array] request data
 * @param [array] rules
 * @return [object] validator response
 */
function validate_request($request_data, $rules)
{
    return Validator::make($request_data, $rules);
}
