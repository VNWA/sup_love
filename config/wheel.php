<?php

return [

    /** Giữ biến môi trường cho tương thích; người chơi chọn phòng qua ?room= trên /game. */
    'default_room_slug' => env('WHEEL_DEFAULT_ROOM_SLUG', 'default'),

    'min_bet' => (int) env('WHEEL_MIN_BET', 1),

    'max_bet' => (int) env('WHEEL_MAX_BET', 99_999),

    /**
     * Tên ô mặc định khi chạy WheelChoiceSeeder (migrate:fresh).
     * Admin quản lý đầy đủ tại /admin/wheel-choices.
     */
    'default_choice_names' => [
        'Hôn nhân',
        'Tình yêu',
        'Gia đình',
        'Sự nghiệp',
        'Sức khỏe',
        'Bạn bè',
        'Du lịch',
        'Tài chính',
    ],
];
