<?php

namespace App\Http\Middleware;

use App\Models\DomainTheme;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class DomainThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $headerDomainId = $request->header('X-Domain-Id');

        if ($headerDomainId && is_numeric($headerDomainId)) {
            $mapping = DomainTheme::where('id', (int) $headerDomainId)->where('active', true)->first();
        } else {
            $domain = $request->getHttpHost();
            $mapping = DomainTheme::where('domain', $domain)->where('active', true)->first();
        }

        $themeSlug = $mapping ? $mapping->theme_slug : 'default';

        config(['app.active_theme' => $themeSlug]);

        if ($mapping) {
            config(['app.domain_id' => $mapping->id]);

            $siteName = Setting::where('key', 'site_name')->where('domain_id', $mapping->id)->first()?->value;
            $siteDesc = Setting::where('key', 'site_description')->where('domain_id', $mapping->id)->first()?->value;
            $siteTopic = Setting::where('key', 'site_topic')->where('domain_id', $mapping->id)->first()?->value;

            if ($siteName) {
                config(['app.name' => $siteName]);
            }
            if ($siteDesc) {
                config(['app.description' => $siteDesc]);
            }
            if ($siteTopic) {
                config(['app.site_topic' => $siteTopic]);
            }
        } else {
            config(['app.domain_id' => 0]);

            $path = trim($request->path(), '/');
            if (!str_starts_with($path, 'admin') && !$request->expectsJson()) {
                return redirect('/admin/login');
            }
        }

        return $next($request);
    }
}
