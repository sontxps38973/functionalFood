<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BannedWord;

class BannedWordController extends Controller
{
    // Lấy danh sách từ cấm
    public function index()
    {
        return response()->json(['data' => BannedWord::all()]);
    }

    // Thêm từ cấm
    public function store(Request $request)
    {
        $data = $request->validate([
            'word' => 'required|string|unique:banned_words,word',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $bannedWord = BannedWord::create($data);
        return response()->json(['message' => 'Created', 'data' => $bannedWord], 201);
    }

    // Sửa từ cấm
    public function update(Request $request, $id)
    {
        $bannedWord = BannedWord::findOrFail($id);
        $data = $request->validate([
            'word' => 'sometimes|string|unique:banned_words,word,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $bannedWord->update($data);
        return response()->json(['message' => 'Updated', 'data' => $bannedWord]);
    }

    // Xóa từ cấm
    public function destroy($id)
    {
        $bannedWord = BannedWord::findOrFail($id);
        $bannedWord->delete();
        return response()->json(['message' => 'Deleted']);
    }
} 