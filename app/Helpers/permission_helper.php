<?php

use App\Models\RolePermissionModel;
use App\Models\PermissionCategoryModel;

if (!function_exists('can_view')) {
    function can_view($permission_category)
    {
        $PermissionCategoryModel = new PermissionCategoryModel();
        $permission_cat = $PermissionCategoryModel->select('id')->where('short_code', $permission_category)->first();

        if (isset($permission_cat)) {
            $rolePermissionModel = new RolePermissionModel();
            $permission = $rolePermissionModel->select('can_view')->where('perm_cat_id', $permission_cat['id'])->where('role_id', session()->get('role_id'))->first();
            if (isset($permission['can_view'])  && $permission['can_view']) {
                return true;
            } else {
                return false;
            }
        }else {
            return false;
        }
    }
}

if (!function_exists('can_add')) {
    function can_add($permission_category)
    {
        // Your global function logic here
        $PermissionCategoryModel = new PermissionCategoryModel();
        $permission_cat = $PermissionCategoryModel->select('id')->where('short_code', $permission_category)->first();

        $rolePermissionModel = new RolePermissionModel();
        $permission = $rolePermissionModel->select('can_add')->where('perm_cat_id', $permission_cat['id'])->where('role_id', session()->get('role_id'))->first();
        if (isset($permission['can_add'])  && $permission['can_add']) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('can_delete')) {
    function can_delete($permission_category)
    {
        // Your global function logic here
        $PermissionCategoryModel = new PermissionCategoryModel();
        $permission_cat = $PermissionCategoryModel->select('id')->where('short_code', $permission_category)->first();

        $rolePermissionModel = new RolePermissionModel();
        $permission = $rolePermissionModel->select('can_delete')->where('perm_cat_id', $permission_cat['id'])->where('role_id', session()->get('role_id'))->first();
        if (isset($permission['can_delete'])  && $permission['can_delete']) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('can_edit')) {
    function can_edit($permission_category)
    {
        // Your global function logic here
        $PermissionCategoryModel = new PermissionCategoryModel();
        $permission_cat = $PermissionCategoryModel->select('id')->where('short_code', $permission_category)->first();

        $rolePermissionModel = new RolePermissionModel();
        $permission = $rolePermissionModel->select('can_edit')->where('perm_cat_id', $permission_cat['id'])->where('role_id', session()->get('role_id'))->first();
        if (isset($permission['can_edit'])  && $permission['can_edit']) {
            return true;
        } else {
            return false;
        }
    }
}
