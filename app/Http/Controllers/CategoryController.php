<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\CategoryLog;
use App\Models\User;
use App\Notifications\ApprovalSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $categories = Category::withCount('assets')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('slug', 'like', "%{$search}%")
                             ->orWhere('prefix', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('categories.partials.table', compact('categories'));
        }

        return view('categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Category::create($validated);
        Log::info("User ID {Auth::id()} created new category: {$validated['name']}");

        return redirect()->route('categories.index')
            ->with('success', "Kategori \"{$validated['name']}\" berhasil ditambahkan!");
    }

    public function edit(int $id): View
    {
        $category = Category::withCount('assets')->findOrFail($id);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $category = Category::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            $oldSlug = $category->slug;
            $category->update($validated);

            // Update slug di tabel assets jika berubah
            if ($oldSlug !== $validated['slug']) {
                \App\Models\Asset::where('category', $oldSlug)
                    ->update(['category' => $validated['slug']]);
            }

            CategoryLog::create([
                'category_id' => $category->id,
                'user_id' => $user->id,
                'action' => 'update',
                'old_data' => $category->getOriginal(),
                'new_data' => $category->toArray(),
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            Log::info("Super Admin (ID: {$user->id}) updated category: {$category->name}");
            return redirect()->route('categories.index')
                ->with('success', "Kategori \"{$validated['name']}\" berhasil diperbarui!");
        }

        // Admin: Cek tiket pending duplikat
        $existingPending = CategoryLog::where('category_id', $category->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('categories.index')
                ->with('error', 'Sudah ada tiket pending untuk kategori ini. Tunggu tiket sebelumnya diproses.');
        }

        // Admin: pending approval
        $log = CategoryLog::create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'action' => 'update',
            'old_data' => $category->toArray(),
            'new_data' => $validated,
            'status' => 'pending',
        ]);

        User::where('role', 'super_admin')->each(function (User $sa) use ($category, $user, $log) {
            $sa->notify(new ApprovalSubmittedNotification(
                entityType: 'kategori',
                action: 'update',
                entityName: $category->name,
                requesterName: $user->name,
                logId: $log->id,
                logType: 'category',
            ));
        });

        return redirect()->route('categories.index')
            ->with('success', 'Permintaan update kategori telah dikirim dan menunggu persetujuan Super Admin.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::withCount('assets')->findOrFail($id);

        if ($category->assets_count > 0) {
            return redirect()->route('categories.index')
                ->with('error', "Tidak bisa menghapus kategori \"{$category->name}\" karena masih digunakan oleh {$category->assets_count} aset.");
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            CategoryLog::create([
                'category_id' => $category->id,
                'user_id' => $user->id,
                'action' => 'delete',
                'old_data' => $category->toArray(),
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $category->delete();
            Log::info("Super Admin (ID: {$user->id}) deleted category: {$category->name}");

            return redirect()->route('categories.index')
                ->with('success', "Kategori \"{$category->name}\" berhasil dihapus.");
        }

        // Admin: Cek tiket pending duplikat
        $existingPending = CategoryLog::where('category_id', $category->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('categories.index')
                ->with('error', 'Sudah ada tiket pending untuk kategori ini. Tunggu tiket sebelumnya diproses.');
        }

        // Admin: pending approval
        $log = CategoryLog::create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'action' => 'delete',
            'old_data' => $category->toArray(),
            'status' => 'pending',
        ]);

        User::where('role', 'super_admin')->each(function (User $sa) use ($category, $user, $log) {
            $sa->notify(new ApprovalSubmittedNotification(
                entityType: 'kategori',
                action: 'delete',
                entityName: $category->name,
                requesterName: $user->name,
                logId: $log->id,
                logType: 'category',
            ));
        });

        return redirect()->route('categories.index')
            ->with('success', 'Permintaan hapus kategori dikirim ke Super Admin.');
    }

}
