<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ajo.only' => \App\Http\Middleware\EnsureAjoOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Page expired. Refresh and try again.'], 419);
            }

            return redirect()->route('login')
                ->with('error', 'Page expired. Please sign in again.');
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            $message = match (true) {
                $request->routeIs('teams.store') => 'You can create up to 5 teams a day. Try again tomorrow.',
                $request->routeIs('projects.store') => 'You can create up to 10 projects a day. Try again tomorrow.',
                $request->routeIs('friends.request') => 'Too many friend requests. Try again in an hour.',
                default => 'Too many attempts. Please wait and try again.',
            };

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 429);
            }

            return redirect()->back()->with('error', $message);
        });
            })->create();