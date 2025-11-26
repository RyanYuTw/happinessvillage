<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 圖片管理控制器
 */
class PhotoController extends Controller
{
    /**
     * 顯示圖片列表
     */
    public function index(Request $request)
    {
        $query = Photo::orderBy('created_at', 'desc');
        
        // 如果有搜尋參數
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // 搜尋檔名
                $q->where('filename', 'like', "%{$search}%");
                
                // 搜尋標籤（檢查 JSON 陣列中的任何元素是否包含搜尋字串）
                // 由於 SQLite 的 JSON 支援有限，我們需要使用不同的方法
                $q->orWhere('tags', 'like', "%{$search}%");
            });
        }
        
        $photos = $query->get()->map(function($photo) {
            return [
                'id' => $photo->id,
                'filename' => $photo->filename,
                'url' => Storage::url($photo->path),
                'path' => $photo->path,
                'width' => $photo->width,
                'height' => $photo->height,
                'size' => $photo->size,
                'mime_type' => $photo->mime_type,
                'tags' => $photo->tags,
                'created_at' => $photo->created_at,
                'updated_at' => $photo->updated_at,
            ];
        });
        
        // 如果是 API 請求（通過 Accept header 判斷）
        if ($request->wantsJson() || $request->is('api/*') || $request->has('search')) {
            return response()->json(['data' => $photos]);
        }
        
        // 否則返回 Inertia 頁面
        return Inertia::render('Admin/Photos', ['photos' => $photos]);
    }

    /**
     * 更新圖片資訊
     */
    public function update(Request $request, $id)
    {
        $photo = Photo::findOrFail($id);
        
        $request->validate([
            'filename' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
        ]);
        
        if ($request->has('filename')) {
            $photo->filename = $request->filename;
        }
        
        if ($request->has('tags')) {
            $photo->tags = $request->tags;
        }
        
        $photo->updated_at = time();
        $photo->save();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * 刪除圖片
     */
    public function destroy($id)
    {
        $photo = Photo::findOrFail($id);
        
        // 刪除檔案
        Storage::disk('public')->delete($photo->path);
        
        // 刪除記錄
        $photo->delete();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * 上傳圖片
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'filename' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
        ]);

        $file = $request->file('image');
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);
        
        // 處理標籤
        $tags = null;
        if ($request->has('tags')) {
            $tags = json_decode($request->tags, true);
        }
        
        // 檢查是否已存在
        $photo = Photo::where('hash', $hash)->first();
        
        if ($photo) {
            // 更新現有記錄
            if ($request->has('filename')) {
                $photo->filename = $request->filename;
            }
            if ($tags) {
                $photo->tags = $tags;
            }
            $photo->updated_at = time();
            $photo->save();
        } else {
            // 儲存檔案
            $filename = $hash . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photos', $filename, 'public');
            
            // 獲取圖片尺寸
            list($width, $height) = getimagesize($file->getRealPath());
            
            // 建立新記錄
            $photo = Photo::create([
                'hash' => $hash,
                'path' => $path,
                'filename' => $request->has('filename') ? $request->filename : $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
                'tags' => $tags,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }

        return response()->json([
            'url' => Storage::url($photo->path),
            'id' => $photo->id,
        ]);
    }
}
