<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Toggle between light and dark theme.
     */
    public function toggle(Request $request)
    {
        $user = $request->user();
        
        // Toggle the theme preference
        $currentTheme = $user->theme ?? 'light';
        $newTheme = $currentTheme === 'light' ? 'dark' : 'light';
        
        // Save the new theme preference
        $user->theme = $newTheme;
        $user->save();
        
        return response()->json(['theme' => $newTheme]);
    }
}
