<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Profile;
use App\Traits\HandlesImageUpload;

class ProfileController extends Controller
{
    use HandlesImageUpload;
    public function update(Request $request)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'full_name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

// Update username
    $user->update([
        'full_name' => $request->full_name,
    ]);

// Update profile picture if available
        if ($request->hasFile('profile_image')) {
            $newImagePath = $this->uploadImage(
                $request->file('profile_image'),
                'profile_images',
                $user->profile->profile_image
            );
            $user->profile->update([
                'profile_image' => $newImagePath,
            ]);
        }

// Update phone number
    $user->profile->update([
        'phone' => $request->phone,
    ]);

    return response()->json([
        'message' => 'Data updated successfully',
        'user' => $user->load('profile'),
    ]);
}
}
