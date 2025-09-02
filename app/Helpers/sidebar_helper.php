<?php

use App\Models\SidebarModel;

if (!function_exists('get_admin_sidebar_data')) {
    function get_admin_sidebar_data()
    {
        // Create an instance of the SidebarModel
        $sidebarModel = new SidebarModel();
        
        // Fetch all sidebar items ordered by parent_id and id
        $sidebarItems = $sidebarModel->where('for_account_type', 'Admin')
                                     ->orderBy('row_order', 'ASC')
                                     ->findAll();
                                     
        return $sidebarItems;
    }
    
}
if (!function_exists('get_seller_sidebar_data')) {
    function get_seller_sidebar_data()
    {
        // Create an instance of the SidebarModel
        $sidebarModel = new SidebarModel();
        
        // Fetch all sidebar items ordered by parent_id and id
        $sidebarItems = $sidebarModel->where('for_account_type', 'Seller')
                                     ->orderBy('row_order', 'ASC')
                                     ->findAll();
                                     
        return $sidebarItems;
    }
    
}
