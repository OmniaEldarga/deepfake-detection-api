<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    use HandlesImageUpload;
public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,mkv|max:102400'
        ]);

        $user = Auth::user();
        $uploadedFile = $request->file('file');

        // Selected the file type
        $fileType = in_array($uploadedFile->extension(), ['mp4', 'mov', 'mkv']) ? 'video' : 'image';
        // Store file
        $filePath = $this->uploadFile($uploadedFile, 'uploads');
        $fileUrl = Storage::url($filePath);
        // Save data to database
        $file = File::create([
            'user_id' => auth()->id(),
            'file_path' => $fileUrl,
            'file_type' => $fileType,
        ]);

        // File analysis with artificial intelligence
        $fullPath = storage_path("app/public/" . $filePath);
        $response = Http::attach(
        'file', file_get_contents($fullPath), $uploadedFile->getClientOriginalName()
        )->post('http://127.0.0.1:8000/predict');

    if ($response->successful()) {
        $result = $response->json();
        $file->update([
            'is_fake' => $result['is_fake'] ?? null,
            'confidence_score' => $result['confidence_score'] ?? null,
            'check_date' => now(),
        ]);
    }    $fullPath = storage_path("app/public/" . $filePath);

    $response = Http::attach(
        'file', file_get_contents($fullPath), $uploadedFile->getClientOriginalName()
    )->post('http://127.0.0.1:8000/predict');

    if ($response->successful()) {
        $result = $response->json();

        $file->update([
            'is_fake' => $result['is_fake'] ?? null,
            'confidence_score' => $result['confidence_score'] ?? null,
            'check_date' => now(),
        ]);
    }
        return response()->json([
            'message' => 'File uploaded and processed successfully',
            'file' => $file
        ], 201);
    }
// Function to display user's history files
public function history(Request $request)
    {
        $user = Auth::user();

        $files = File::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'status' => true,
            'files' => $files
        ]);
    }
// Function to delete a specific file (soft delete)
public function destroy(File $file)
    {
        $user = Auth::user();

        if ($file->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete the file from storage
        if ($file->file_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $file->file_path));
        }
        // Soft delete record
        $file->delete();
        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully'
        ]);
    }
}
