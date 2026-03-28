<?php

return [

    'cost_per_spin' => (int) env('WHEEL_COST_PER_SPIN', 1),

    /**
     * Giải mặc định khi chạy WheelPrizeSeeder (bảng wheel_prizes).
     * Vòng quay đọc giải từ database; admin sửa tại /admin/wheel-prizes.
     */
    'prizes' => [
        [
            'label' => 'Giải đặc biệt — Tour du lịch 2 người',
            'label_ngan' => 'Tour 2N',
            'color' => '#c2185b',
            'weight' => 1,
        ],
        [
            'label' => 'Giải nhất — Voucher 500.000đ',
            'label_ngan' => 'Voucher 500k',
            'color' => '#e91e63',
            'weight' => 2,
        ],
        [
            'label' => 'Giải nhì — Voucher 200.000đ',
            'label_ngan' => 'Voucher 200k',
            'color' => '#ec407a',
            'weight' => 4,
        ],
        [
            'label' => 'Giải ba — Voucher 100.000đ',
            'label_ngan' => 'Voucher 100k',
            'color' => '#f06292',
            'weight' => 6,
        ],
        [
            'label' => 'Giải khuyến khích — 50 điểm tài khoản',
            'label_ngan' => '50 điểm',
            'color' => '#ad1457',
            'weight' => 10,
        ],
        [
            'label' => 'Quà tặng — Ly giữ nhiệt CLB',
            'label_ngan' => 'Ly CLB',
            'color' => '#880e4f',
            'weight' => 8,
        ],
        [
            'label' => '10 điểm tích lũy',
            'label_ngan' => '10 điểm',
            'color' => '#f48fb1',
            'weight' => 14,
        ],
        [
            'label' => 'Chúc may mắn lần sau',
            'label_ngan' => 'May mắn',
            'color' => '#fce4ec',
            'weight' => 20,
        ],
    ],
];
