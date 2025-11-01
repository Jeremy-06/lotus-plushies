<?php

/**
 * UIHelper - Reusable UI components to eliminate code duplication
 */
class UIHelper {
    
    /**
     * Status badge configuration
     */
    private static $statusConfig = [
        'pending' => [
            'class' => 'warning',
            'icon' => 'fa-clock',
            'bg' => '#fff3cd',
            'text' => '#856404',
            'label' => 'Pending'
        ],
        'processing' => [
            'class' => 'info',
            'icon' => 'fa-spinner',
            'bg' => '#d1ecf1',
            'text' => '#0c5460',
            'label' => 'Processing'
        ],
        'shipped' => [
            'class' => 'primary',
            'icon' => 'fa-shipping-fast',
            'bg' => '#cfe2ff',
            'text' => '#084298',
            'label' => 'Shipped'
        ],
        'delivered' => [
            'class' => 'success',
            'icon' => 'fa-check',
            'bg' => '#d1e7dd',
            'text' => '#0f5132',
            'label' => 'Delivered'
        ],
        'completed' => [
            'class' => 'dark',
            'icon' => 'fa-check-circle',
            'bg' => '#d6d8db',
            'text' => '#1a1e21',
            'label' => 'Completed'
        ],
        'cancelled' => [
            'class' => 'danger',
            'icon' => 'fa-times-circle',
            'bg' => '#f8d7da',
            'text' => '#842029',
            'label' => 'Cancelled'
        ]
    ];
    
    /**
     * Render order status badge
     * 
     * @param string $status Order status
     * @param string $size Size: 'sm', 'md', 'lg'
     * @return string HTML badge
     */
    public static function renderOrderStatusBadge($status, $size = 'md') {
        $config = self::$statusConfig[$status] ?? [
            'class' => 'secondary',
            'icon' => 'fa-question',
            'bg' => '#e2e3e5',
            'text' => '#41464b',
            'label' => ucfirst($status)
        ];
        
        $fontSize = $size === 'sm' ? '0.75rem' : ($size === 'lg' ? '1rem' : '0.9rem');
        $padding = $size === 'sm' ? '6px 12px' : ($size === 'lg' ? '12px 24px' : '8px 16px');
        
        return sprintf(
            '<span class="badge" style="background-color: %s; color: %s; border-radius: 20px; font-size: %s; padding: %s;">
                <i class="fas %s me-1"></i>%s
            </span>',
            htmlspecialchars($config['bg']),
            htmlspecialchars($config['text']),
            $fontSize,
            $padding,
            htmlspecialchars($config['icon']),
            htmlspecialchars($config['label'])
        );
    }
    
    /**
     * Render simple Bootstrap badge for table status
     * 
     * @param string $status Status value
     * @return string HTML badge
     */
    public static function renderSimpleStatusBadge($status) {
        $statusClass = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'completed' => 'dark',
            'cancelled' => 'danger'
        ];
        
        $class = $statusClass[$status] ?? 'secondary';
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            htmlspecialchars($class),
            htmlspecialchars(ucfirst($status))
        );
    }
    
    /**
     * Get status configuration
     * 
     * @param string $status Status value
     * @return array Configuration array
     */
    public static function getStatusConfig($status) {
        return self::$statusConfig[$status] ?? [
            'class' => 'secondary',
            'icon' => 'fa-question',
            'bg' => '#e2e3e5',
            'text' => '#41464b',
            'label' => ucfirst($status)
        ];
    }
    
    /**
     * Calculate status counts from orders array
     * 
     * @param array $orders Array of orders
     * @param bool $groupShipped Whether to group processing/delivered as shipped
     * @return array Status counts
     */
    public static function calculateStatusCounts($orders, $groupShipped = false) {
        $statusCounts = [
            'pending' => 0,
            'processing' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        foreach ($orders as $order) {
            $status = $order['order_status'];
            
            // Group for customer view
            if ($groupShipped && ($status == 'processing' || $status == 'delivered')) {
                $status = 'shipped';
            }
            
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        
        return $statusCounts;
    }
    
    /**
     * Format currency
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency symbol
     * @return string Formatted amount
     */
    public static function formatCurrency($amount, $currency = '₱') {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Format date
     * 
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    public static function formatDate($date, $format = 'M d, Y') {
        return date($format, strtotime($date));
    }
    
    /**
     * Render empty state message
     * 
     * @param string $title Title
     * @param string $message Message
     * @param string $icon FontAwesome icon class
     * @param string $actionUrl Optional action button URL
     * @param string $actionText Optional action button text
     * @return string HTML
     */
    public static function renderEmptyState($title, $message, $icon = 'fa-inbox', $actionUrl = null, $actionText = null) {
        $html = '<div class="card shadow-sm text-center" style="border: none; border-radius: 20px; padding: 3rem;">
            <div class="card-body">
                <div class="mb-4">
                    <i class="fas ' . htmlspecialchars($icon) . '" style="font-size: 5rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                </div>
                <h4 class="mb-3" style="color: var(--purple-dark);">' . htmlspecialchars($title) . '</h4>
                <p class="text-muted mb-4">' . htmlspecialchars($message) . '</p>';
        
        if ($actionUrl && $actionText) {
            $html .= '<a href="' . htmlspecialchars($actionUrl) . '" class="btn btn-lg" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border: none; border-radius: 25px; padding: 12px 40px;">
                ' . htmlspecialchars($actionText) . '
            </a>';
        }
        
        $html .= '</div></div>';
        
        return $html;
    }
}
