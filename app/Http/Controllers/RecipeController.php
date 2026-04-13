<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with(['user', 'category'])->published();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $recipes = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('recipes.index', compact('recipes', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('recipes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'required|string',
            'category_id'            => 'nullable|exists:categories,id',
            'prep_time'              => 'required|integer|min:1',
            'cook_time'              => 'required|integer|min:0',
            'servings'               => 'required|integer|min:1',
            'difficulty'             => 'required|in:easy,medium,hard',
            'instructions'           => 'required|string',
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ingredients'            => 'required|array|min:1',
            'ingredients.*.name'     => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:50',
            'ingredients.*.unit'     => 'nullable|string|max:50',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('recipes', 'public');
        }

        $recipe = Auth::user()->recipes()->create([
            ...$validated,
            'image' => $imagePath,
        ]);

        foreach ($request->ingredients as $ingredient) {
            $recipe->ingredients()->create($ingredient);
        }

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe created successfully!');
    }

    public function show(Recipe $recipe)
    {
        $recipe->load(['user', 'category', 'ingredients']);
        $isFavorited = Auth::check() && Auth::user()->hasFavorited($recipe);
        return view('recipes.show', compact('recipe', 'isFavorited'));
    }

    public function edit(Recipe $recipe)
    {
        $this->authorize('update', $recipe);
        $categories = Category::all();
        $recipe->load('ingredients');
        return view('recipes.edit', compact('recipe', 'categories'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'required|string',
            'category_id'            => 'nullable|exists:categories,id',
            'prep_time'              => 'required|integer|min:1',
            'cook_time'              => 'required|integer|min:0',
            'servings'               => 'required|integer|min:1',
            'difficulty'             => 'required|in:easy,medium,hard',
            'instructions'           => 'required|string',
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ingredients'            => 'required|array|min:1',
            'ingredients.*.name'     => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:50',
            'ingredients.*.unit'     => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('image')) {
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        }

        $recipe->update($validated);

        $recipe->ingredients()->delete();
        foreach ($request->ingredients as $ingredient) {
            $recipe->ingredients()->create($ingredient);
        }

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully!');
    }

    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        if ($recipe->image) {
            Storage::disk('public')->delete($recipe->image);
        }

        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully!');
    }

    public function myRecipes()
    {
        $recipes = Auth::user()->recipes()->with('category')->latest()->paginate(12);
        return view('recipes.my-recipes', compact('recipes'));
    }
}