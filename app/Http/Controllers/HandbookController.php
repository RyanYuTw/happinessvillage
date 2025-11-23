<?php

namespace App\Http\Controllers;

use App\Models\Handbook;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 學習手冊控制器
 */
class HandbookController extends Controller
{
    /**
     * 顯示學習手冊列表
     */
    public function index(Request $request): Response
    {
        $query = Handbook::query();

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('lesson')) {
            $query->where('lesson', 'like', '%' . $request->lesson . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $handbooks = $query->orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Admin/Handbooks', [
            'handbooks' => $handbooks,
            'filters' => $request->only(['year', 'grade', 'semester', 'lesson', 'status'])
        ]);
    }

    /**
     * 顯示新增手冊頁面
     */
    public function create(): Response
    {
        return Inertia::render('Admin/HandbookCreate');
    }

    /**
     * 儲存新手冊
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'grade' => 'required|integer|between:1,6',
            'semester' => 'required|in:上,下',
            'lesson' => 'required|string|max:32',
            'content' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $now = time();
        $validated['created_at'] = $now;
        $validated['updated_at'] = $now;
        $validated['published_at'] = $validated['status'] == 1 ? $now : 0;

        Handbook::create($validated);

        return redirect()->route('admin.handbooks');
    }

    /**
     * 顯示編輯手冊頁面
     */
    public function edit($id): Response
    {
        $handbook = Handbook::findOrFail($id);
        return Inertia::render('Admin/HandbookEdit', [
            'handbook' => $handbook->toArray()
        ]);
    }

    /**
     * 更新手冊
     */
    public function update(Request $request, $id)
    {
        $handbook = Handbook::findOrFail($id);
        
        $validated = $request->validate([
            'year' => 'required|integer',
            'grade' => 'required|integer|between:1,6',
            'semester' => 'required|in:上,下',
            'lesson' => 'required|string|max:32',
            'content' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $validated['updated_at'] = time();
        if ($validated['status'] == 1 && $handbook->status == 0) {
            $validated['published_at'] = time();
        }

        $handbook->update($validated);

        return redirect()->route('admin.handbooks');
    }

    /**
     * 顯示手冊詳情
     */
    public function show($id): Response
    {
        $handbook = Handbook::findOrFail($id);
        return Inertia::render('Admin/HandbookShow', [
            'handbook' => $handbook->toArray()
        ]);
    }

    /**
     * 刪除手冊
     */
    public function destroy($id)
    {
        $handbook = Handbook::findOrFail($id);
        $handbook->delete();

        return redirect()->route('admin.handbooks');
    }
}
