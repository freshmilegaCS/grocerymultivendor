<?php

if (!function_exists('is_rtl')) {
    /**
     * Check if current language is RTL
     */
    function is_rtl(): bool
    {
        return (bool) session()->get('is_rtl');
    }
}

if (!function_exists('text_align')) {
    /**
     * Get text alignment class based on language direction
     */
    function text_align(): string
    {
        return is_rtl() ? 'text-right' : 'text-left';
    }
}

if (!function_exists('flex_direction')) {
    /**
     * Get flex direction class based on language direction
     */
    function flex_direction(): string
    {
        return is_rtl() ? 'flex-row-reverse' : 'flex-row';
    }
}

if (!function_exists('float_direction')) {
    /**
     * Get float direction class based on language direction
     */
    function float_direction(): string
    {
        return is_rtl() ? 'float-right' : 'float-left';
    }
}

if (!function_exists('margin_auto')) {
    /**
     * Get margin auto class based on language direction
     */
    function margin_auto(): string
    {
        return is_rtl() ? 'mr-0 ml-auto' : 'ml-0 mr-auto';
    }
}

if (!function_exists('dir_attribute')) {
    /**
     * Get dir attribute value
     */
    function dir_attribute(): string
    {
        return is_rtl() ? 'rtl' : 'ltr';
    }
}

if (!function_exists('space_reverse')) {
    /**
     * Get space reverse class for RTL
     */
    function space_reverse(): string
    {
        return is_rtl() ? 'space-x-reverse' : '';
    }
}