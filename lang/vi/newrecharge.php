<?php

return [
    'history' => [
        'status' => [
            0 => 'Đang đợi xử lý',
            1 => 'Đang đợi thanh toán',
            2 => 'Thanh toán thất bại',
            3 => 'Thanh toán xong',
            4 => 'Thanh toán xong(còn thừa tiền)',
            5 => 'Thanh toán chưa đủ'
        ],
        'game_status' => [
            0 => 'Đang đợi xử lý',
            1 => 'Chuyển thành công',
            2 => 'Đang thử lại'
        ],
        'empty' => 'Chưa thanh toán lần nào'
    ],
    'not-enough-money' => 'Giá trị thanh toán chưa đủ, cần thanh toán thêm! (số tiền vừa thanh toán đã trả về ví xu web)',
    'shop-error' => 'Tải danh sách vật phẩm để mua thất bại, mời thử lại',
    'recharge-error' => 'Thanh toán thất bại, mời thử lại',
    'shop-empty' => 'Chưa có vật phẩm game nào để mua',
    'callback-error' => 'Xử lý thanh toán gặp lỗi, mời liên hệ hỗ trợ viên. (callback-error)',
    'query-error' => 'Truy vấn giao dịch gặp lỗi, mời thử lại trước khi liên hệ hỗ trợ viên',
    'pending' => 'Giao dịch cần thêm chút thời gian để hoàn thành..đợi xíu..',
    'success' => 'Thanh toán thành công.',
    'callback-in-progress' => 'Đang xử lý rồi, hãy đợi chút..',
];