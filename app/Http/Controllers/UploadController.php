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
        )->post('http://127.0.0.1:9000/predict/');
        //fastapi-detect.onrender.com/predict/
        if ($response->successful()) {
            $result = $response->json();

            $isFake = isset($result['prediction']) && strtolower($result['prediction']) === 'fake' ? 1 : 0;
            $confidence = $result['confidence_score'] ?? $result['confidence'] ?? null;

            $file->update([
                'is_fake' => $isFake,
                'confidence_score' => $confidence,
                'check_date' => now(),
            ]);

            $predict = $isFake ? 'Fake' : 'Real';

            return response()->json([
                'message' => 'File uploaded and processed successfully',
                'predict' => $predict,
                'confidence' => $confidence,
                'file' => $file
            ], 201);
        }

        // If FastAPI failed
        return response()->json([
            'status' => false,
            'message' => 'File uploaded, but AI analysis failed'
        ], 500);
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
