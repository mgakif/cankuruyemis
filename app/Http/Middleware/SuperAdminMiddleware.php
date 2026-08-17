<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    private function showUnauthorized()
    {
        return response(<<<'HTML'
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - İşine Bak!</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
                    color: white;
                }
                .message {
                    text-align: center;
                    padding: 2rem;
                    border-radius: 1rem;
                    background: rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(10px);
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    font-size: 3.5rem;
                    margin: 0;
                    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
                }
                .emoji {
                    font-size: 5rem;
                    margin: 1rem 0;
                    animation: sunglasses 2s infinite;
                    display: block;
                }
                @keyframes sunglasses {
                    0%, 100% { transform: rotate(0deg); }
                    50% { transform: rotate(5deg); }
                }
            </style>
        </head>
        <body>
            <div class="message">
                <span class="emoji">😎</span>
                <h1>İŞİNE BAK KARDEŞİM!</h1>
            </div>
        </body>
        </html>
        HTML, 401);
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Sadece /dbadmin route'una izin ver
        if ($request->path() !== 'dbadmin') {
            return response()->view('errors.404');
        }

        // Laravel auth kontrolü
        if (Auth::check() && Auth::user()->email === 'mgakif@gmail.com') {
            return $next($request);
        } else {
            return $this->showUnauthorized();
        }

    }
}
