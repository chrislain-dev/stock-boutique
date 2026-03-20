<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TODO: Fix Laravel 11 middleware alias syntax
        // $middleware->alias('permission', App\Http\Middleware\CheckPermission::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->report(function (\Throwable $e) {

            // Ne pas spammer pour les erreurs courantes
            if ($e instanceof \Illuminate\Auth\AuthenticationException) return;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) return;
            if ($e instanceof \Illuminate\Validation\ValidationException) return;
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) return;

            // Récupérer infos de la requête
            $request = request();
            $url     = $request?->fullUrl() ?? 'N/A';
            $method  = $request?->method() ?? 'N/A';
            $user    = Auth::check()
                ? Auth::user()->name . ' (#' . Auth::id() . ')'
                : 'Guest';
            $ip      = $request?->ip() ?? 'N/A';

            $code    = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = $e->getMessage() ?: get_class($e);
            $file    = $e->getFile();
            $line    = $e->getLine();

            // Ne notifier que les erreurs 4xx/5xx significatives
            $notify = $code >= 400 && $code !== 404 && $code !== 422 && $code !== 403;
            if ($code === 500 || ($code >= 400 && $notify)) {

                $text = "🚨 *Erreur {$code} — " . config('app.name') . "*\n"
                    . "📍 `{$method} {$url}`\n"
                    . "👤 {$user} | 🌐 {$ip}\n"
                    . "💬 {$message}\n"
                    . "📂 " . basename($file) . ":{$line}\n"
                    . "🕐 " . now()->format('d/m/Y H:i:s');

                \App\Jobs\SendWhatsAppNotification::dispatch($text, $code . $file . $line);
            }
        });
    })->create();
