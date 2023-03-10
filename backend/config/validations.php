<?php

return [

    // Admin/Mster
    'add_category' => ['name' => 'required|string', 'desc' => 'required|string'],
    'update_category' => ['id' => 'required|integer', 'name' => 'required|string', 'desc' => 'required|string'],
    'add_subcategory' => ['name' => 'required|string', 'desc' => 'required|string', 'category_id' => 'required|integer'],
    'update_subcategory' => [
        'id' => 'required|integer', 'name' => 'required|string', 'desc' => 'required|string', 'category_id' => 'required|integer',
    ],
    'add_type' => ['name' => 'required|string', 'desc' => 'required|string'],
    'update_type' => ['id' => 'required|integer', 'name' => 'required|string', 'desc' => 'required|string'],
    'add_rt' => [
        'name' => 'required|string|unique:racetracks',
        'desc' => 'required|string',
        'distance' => 'required|string',
        'type_id' => 'required|array',
        'address' => 'required|string',
        'city' => 'required|string',
        'video_link' => 'string',
        'allocation_start_date' => 'required|string',
        'allocation_end_date' => 'required|string',
        'allocation_time' => 'required|string',
        'owner_id' => 'required|integer',
        'owner_email' => 'required|email|string',
        'owner_contact' => 'required|string',
        'responsible_person' => 'required|string',
        'rating' => 'required|integer',
        'shared' => 'required|integer',
        'is_featured' => 'required|integer',
        'images' => 'array',
        'equipments' => 'array',
        'servicemans' => 'array',
    ],
    'update_rt' => [
        'id' => 'required|integer',
        'name' => 'required|string',
        'desc' => 'required|string',
        'distance' => 'required|string',
        'type_id' => 'required|array',
        'address' => 'required|string',
        'city' => 'required|string',
        'video_link' => 'string',
        'allocation_start_date' => 'required|string',
        'allocation_end_date' => 'required|string',
        'allocation_time' => 'required|string',
        'owner_id' => 'required|integer',
        'owner_email' => 'required|email|string',
        'owner_contact' => 'required|string',
        'responsible_person' => 'required|string',
        'rating' => 'required|integer',
        'shared' => 'required|integer',
        'is_featured' => 'required|integer',
        'new_images' => 'array',
        'delete_images' => 'array',
        'new_equipments' => 'array',
        'new_servicemans' => 'array',
    ],
    'delete_rt' => ['id' => 'required|integer'],
    'change_rt_av' => [
        'id' => 'required|integer',
        'allocation_start_date' => 'required|string',
        'allocation_end_date' => 'required|string',
        'allocation_time' => 'required|string',
    ],
    'change_status' => [
        'id' => 'required|integer',
        'type' => 'required|string',
        'status' => 'required|string',
    ],
    'add_equipment' => [
        'name' => 'required|string',
        'desc' => 'required|string',
        'category_id' => 'required|integer',
        'subcategory_id' => 'required|integer',
        'type_id' => 'required|integer',
        'special_instructions' => 'required|string',
        'allocation_start_date' => 'required|string',
        'allocation_end_date' => 'required|string',
        'allocation_time' => 'required|string',
        'contact_no' => 'required|string',
        'email_id' => 'required|string',
        'rating' => 'required|string',
        'images' => 'required|array',
        'video_link' => 'string',
    ],
    'update_equipment' => [
        'id' => 'required|integer',
        'name' => 'required|string',
        'desc' => 'required|string',
        'category_id' => 'required|integer',
        'subcategory_id' => 'required|integer',
        'type_id' => 'required|integer',
        'special_instructions' => 'required|string',
        'allocation_start_date' => 'required|string',
        'allocation_end_date' => 'required|string',
        'allocation_time' => 'required|string',
        'contact_no' => 'required|string',
        'email_id' => 'required|string',
        'rating' => 'required|string',
        'video_link' => 'string',
        'new_images' => 'array',
        'delete_images' => 'array',
    ],
    'add_sm' => [
        'name' => 'required|string',
        'contact_no' => 'required|string',
        'email_id' => 'required|string|email',
        'address' => 'required|string',
        'specializations' => 'required|string',
        'available_hours' => 'required|integer',
        'rating' => 'required|integer',
    ],
    'update_sm' => [
        'id' => 'required|integer',
        'name' => 'required|string',
        'contact_no' => 'required|string',
        'email_id' => 'required|string|email',
        'address' => 'required|string',
        'specializations' => 'required|array',
        'available_hours' => 'required|integer',
        'rating' => 'required|integer',
    ],
    'add_user' => [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email_id' => 'required|string|email|unique:users',
        'user_role' => 'required|string',
        'password' => 'required|string|confirmed',
        'password_confirmation' => 'required',
    ],
    'update_user' => [
        'id' => 'required|integer',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
    ],
    'delete_user' => [
        'id' => 'required|integer',
    ],
    'details' => [
        'id' => 'required|integer',
        'type' => 'required|string',
    ],

    // Racetrack Owner
    'get_owner_racetracks' => [
        'owner_id' => 'required|integer',
    ],

    // Serviceman
    'change_sm_status' => [
        'id' => 'required|integer',
        'status' => 'required|string',
    ],
    'sm_details' => [
        'id' => 'required|integer',
    ],
    'add_sm_to_rt' => [
        'racetrack_id' => 'required|integer',
        'serviceman_id' => 'required|integer',
    ],

    // Equipment
    'change_equip_status' => [
        'id' => 'required|integer',
        'status' => 'required|string',
    ],
    'equip_details' => [
        'id' => 'required|integer',
    ],
    'add_equip_to_rt' => [
        'racetrack_id' => 'required|integer',
        'equipment_id' => 'required|integer',
    ],
];
