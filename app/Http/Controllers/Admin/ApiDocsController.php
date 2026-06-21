<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $token = $user->tokens()->where('name', 'api-token')->first();
        $newToken = null;

        if (!$token) {
            $newToken = $user->createToken('api-token')->plainTextToken;
        }

        return admin_view('api-docs', compact('newToken'));
    }

    public function regenerateToken()
    {
        $user = auth()->user();
        $user->tokens()->where('name', 'api-token')->delete();
        $plainText = $user->createToken('api-token')->plainTextToken;

        return back()->with('newToken', $plainText);
    }
}
