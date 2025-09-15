<?php
// view-counter.php
function getVisitorIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function updateViewCount($page_url) {
    $visitor_ip = getVisitorIP();
    $today = date('Y-m-d');
    
    // File lưu data (thay bằng database nếu có)
    $data_file = 'page_views.json';
    $views_data = [];
    
    if (file_exists($data_file)) {
        $views_data = json_decode(file_get_contents($data_file), true);
    }
    
    // Khởi tạo page nếu chưa có
    if (!isset($views_data[$page_url])) {
        $views_data[$page_url] = [
            'total_views' => 0,
            'unique_ips' => [],
            'daily_views' => []
        ];
    }
    
    // Kiểm tra IP đã xem hôm nay chưa
    $ip_today_key = $visitor_ip . '_' . $today;
    
    if (!in_array($ip_today_key, $views_data[$page_url]['unique_ips'])) {
        // IP mới hoặc ngày mới
        $views_data[$page_url]['total_views']++;
        $views_data[$page_url]['unique_ips'][] = $ip_today_key;
        
        // Lưu theo ngày
        if (!isset($views_data[$page_url]['daily_views'][$today])) {
            $views_data[$page_url]['daily_views'][$today] = 0;
        }
        $views_data[$page_url]['daily_views'][$today]++;
        
        // Dọn dẹp data cũ (giữ 30 ngày gần nhất)
        $views_data[$page_url]['unique_ips'] = array_filter(
            $views_data[$page_url]['unique_ips'],
            function($ip_date) {
                $date_part = explode('_', $ip_date);
                $record_date = end($date_part);
                return (strtotime($record_date) >= strtotime('-30 days'));
            }
        );
        
        // Lưu file
        file_put_contents($data_file, json_encode($views_data, JSON_PRETTY_PRINT));
    }
    
    return $views_data[$page_url]['total_views'];
}

// Sử dụng
$current_page = $_SERVER['REQUEST_URI'];
$view_count = updateViewCount($current_page);
echo $view_count;
?>
