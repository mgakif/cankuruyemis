<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Desteklenen diller
     */
    protected $languages = ['tr', 'en'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // URL'den dil parametresi kontrolü
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, $this->languages)) {
                Session::put('locale', $lang);
                App::setLocale($lang);

                return redirect()->back();
            }
        }

        // Session'da kayıtlı dil varsa onu kullan
        if (Session::has('locale') && in_array(Session::get('locale'), $this->languages)) {
            App::setLocale(Session::get('locale'));
        } else {
            // Tarayıcı dilini kontrol et
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (in_array($browserLang, $this->languages)) {
                App::setLocale($browserLang);
                Session::put('locale', $browserLang);
            } else {
                // Varsayılan dil: Türkçe
                App::setLocale('tr');
                Session::put('locale', 'tr');
            }
        }

        return $next($request);
    }
}
