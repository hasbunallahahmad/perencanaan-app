<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Default Sanctum user route - NO SANITIZATION (authentication route)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

// Public API routes (tidak memerlukan authentication) - NO SANITIZATION for system routes
Route::prefix('v1')->group(function () {
  // System health check - no user input
  Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
  });
});

// Protected API routes (memerlukan authentication) - NO SANITIZATION for profile
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
  // User profile - no user input processing
  Route::get('/profile', function (Request $request) {
    return response()->json($request->user());
  });
});

// ===========================================
// API ROUTES THAT NEED SANITIZATION
// ===========================================
// Only add sanitization to routes that process user input for business logic
Route::middleware(['sanitize', 'throttle:60,1'])->prefix('v1')->group(function () {

  // Public API routes with user input that need sanitization
  Route::post('/contact', function (Request $request) {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email',
      'message' => 'required|string|max:5000',
    ]);

    // Process contact form via API
    return response()->json([
      'message' => 'Contact form submitted successfully',
      'id' => uniqid()
    ]);
  });

  Route::post('/feedback', function (Request $request) {
    $validated = $request->validate([
      'rating' => 'required|integer|min:1|max:5',
      'comment' => 'nullable|string|max:1000',
      'category' => 'required|string|in:bug,feature,general',
    ]);

    // Process feedback
    return response()->json([
      'message' => 'Feedback submitted successfully',
      'id' => uniqid()
    ]);
  });
});

// Protected API routes with sanitization (authenticated users submitting data)
Route::middleware(['auth:sanctum', 'sanitize', 'throttle:30,1'])->prefix('v1')->group(function () {

  // User profile update - needs sanitization for user input
  Route::put('/profile', function (Request $request) {
    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'bio' => 'sometimes|nullable|string|max:500',
      'website' => 'sometimes|nullable|url',
    ]);

    // Update user profile
    $user = $request->user();
    $user->update($validated);

    return response()->json([
      'message' => 'Profile updated successfully',
      'user' => $user->fresh()
    ]);
  });

  // User posts/content creation - needs sanitization
  Route::post('/posts', function (Request $request) {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'content' => 'required|string|max:10000',
      'tags' => 'sometimes|array|max:10',
      'tags.*' => 'string|max:50',
    ]);

    // Create post logic here
    return response()->json([
      'message' => 'Post created successfully',
      'post' => [
        'id' => uniqid(),
        'title' => $validated['title'],
        'created_at' => now()
      ]
    ]);
  });

  // Comment submission - needs sanitization
  Route::post('/posts/{id}/comments', function (Request $request, $id) {
    $validated = $request->validate([
      'comment' => 'required|string|max:2000',
    ]);

    // Add comment logic here
    return response()->json([
      'message' => 'Comment added successfully',
      'comment' => [
        'id' => uniqid(),
        'content' => $validated['comment'],
        'post_id' => $id,
        'created_at' => now()
      ]
    ]);
  });
});
