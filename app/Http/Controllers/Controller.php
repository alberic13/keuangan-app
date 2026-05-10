<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function ensureAnyRole(array $roles): void
    {
        abort_unless(auth()->user()?->hasAnyRole($roles), 403, 'User tidak berwenang.');
    }

    protected function redirectBackWithMessage(Request $request, string $message): RedirectResponse
    {
        return redirect()->to($request->headers->get('referer') ?: '/')->with('status', $message);
    }
}
