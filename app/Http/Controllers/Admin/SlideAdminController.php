<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class SlideAdminController extends Controller
{
    public function index()
    {
        $slides = Slide::orderBy('position')->orderByDesc('id')->get();
        return view('admin.slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.slides.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $path = MediaStorage::store($request->file('image'), 'slides');

        Slide::create([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'link' => $validated['link'] ?? null,
            'position' => $validated['position'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'image' => $path,
        ]);

        return redirect()->route('admin.slides.index')->with('success', 'Slide created.');
    }

    public function edit(Slide $slide)
    {
        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, Slide $slide)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = [
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'link' => $validated['link'] ?? null,
            'position' => $validated['position'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $path = MediaStorage::store($request->file('image'), 'slides');
            $data['image'] = $path;
        }

        $slide->update($data);

        return redirect()->route('admin.slides.index')->with('success', 'Slide updated.');
    }

    public function destroy(Slide $slide)
    {
        $slide->delete();
        return redirect()->route('admin.slides.index')->with('success', 'Slide deleted.');
    }
}
