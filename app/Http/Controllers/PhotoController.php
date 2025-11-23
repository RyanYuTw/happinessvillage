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
    public function index(): Response
    {
        $photos = Photo::orderBy('created_at', 'desc')->get();
        return Inertia::render('Admin/Photos', ['photos' => $photos]);
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
