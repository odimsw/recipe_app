<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Recipe $recipe)
    {
        $user = Auth::user();

        if ($user->hasFavorited($recipe)) {
            $user->favorites()->detach($recipe->id);
            $message = 'Removed from favorites.';
        } else {
            $user->favorites()->attach($recipe->id);
            $message = 'Added to favorites!';
        }

        return back()->with('success', $message);
    }

    public function index()
    {
        $recipes = Auth::user()->favorites()->with(['user', 'category'])->latest()->paginate(12);
        return view('recipes.favorites', compact('recipes'));
    }
}