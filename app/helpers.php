<?php

if (!function_exists('theme_view')) {
    function theme_view(string $view, array $data = [], array $mergeData = [])
    {
        $theme = config('app.active_theme', 'default');
        return view("theme::{$theme}.{$view}", $data, $mergeData);
    }
}

if (!function_exists('active_theme')) {
    function active_theme(): string
    {
        return config('app.active_theme', 'default');
    }
}

if (!function_exists('admin_view')) {
    function admin_view(string $view, array $data = [], array $mergeData = [])
    {
        return view("theme::default.admin.{$view}", $data, $mergeData);
    }
}

if (!function_exists('tenant_id')) {
    function tenant_id(): ?int
    {
        return config('app.domain_id');
    }
}

if (!function_exists('webp_url')) {
    function webp_url(?string $url): ?string
    {
        if (!$url) return null;
        $webp = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $url);
        if ($webp !== $url && file_exists(public_path(parse_url($webp, PHP_URL_PATH)))) {
            return $webp;
        }
        return $url;
    }
}
