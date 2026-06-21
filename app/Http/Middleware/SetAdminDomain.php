<?php

namespace App\Http\Middleware;

use App\Models\DomainTheme;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SetAdminDomain
{
    public function handle(Request $request, Closure $next)
    {
        $domainId = session('admin_domain_id');

        if ($domainId) {
            $domain = DomainTheme::find($domainId);
            if ($domain && $domain->active) {
                config(['app.domain_id' => $domain->id]);
                config(['app.active_theme' => $domain->theme_slug]);

                $siteName = Setting::where('key', 'site_name')->where('domain_id', $domain->id)->first()?->value;
                $siteDesc = Setting::where('key', 'site_description')->where('domain_id', $domain->id)->first()?->value;
                $siteTopic = Setting::where('key', 'site_topic')->where('domain_id', $domain->id)->first()?->value;

                if ($siteName) {
                    config(['app.name' => $siteName]);
                }
                if ($siteDesc) {
                    config(['app.description' => $siteDesc]);
                }
                if ($siteTopic) {
                    config(['app.site_topic' => $siteTopic]);
                }
            }
        }

        View::composer('theme::default.admin.layouts.app', function ($view) {
            $view->with('adminDomains', DomainTheme::where('active', true)->orderBy('domain')->get());
            $view->with('currentDomainId', session('admin_domain_id', config('app.domain_id')));
        });

        return $next($request);
    }
}
