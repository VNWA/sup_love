<?php

return [

    /** Giữ biến môi trường cho tương thích; người chơi chọn phòng qua ?room= trên /game. */
    'default_room_slug' => env('WHEEL_DEFAULT_ROOM_SLUG', 'default'),

    'min_bet' => (int) env('WHEEL_MIN_BET', 1),

    'max_bet' => (int) env('WHEEL_MAX_BET', 99_999),

/**
 * Các ô vòng quay mặc định nằm trong WheelChoiceSeeder (migrate:fresh --seed).
 * id 0 = «Chúc bạn may mắn lần sau» (ô hệ thống, không xóa). Admin: /admin/wheel-choices.
 */
];
