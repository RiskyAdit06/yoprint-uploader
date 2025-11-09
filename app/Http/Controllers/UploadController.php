<?php

namespace App\Http\Controllers;

use App\Http\Resources\UploadResource;
use App\Jobs\ProcessCsvUpload;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Display upload form and list of uploads
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $uploads = Upload::orderBy('created_at', 'desc')->paginate(20);
        return view('uploads.index', compact('uploads'));
    }

    /**
     * Handle file upload
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:51200', // 50MB max
        ]);

        $file = $request->file('csv_file');
        $originalFilename = $file->getClientOriginalName();
        
        // Check for idempotency - check if same filename was uploaded recently (within last hour)
        $recentUpload = Upload::where('original_filename', $originalFilename)
            ->where('created_at', '>=', now()->subHour())
            ->where('status', '!=', 'failed')
            ->first();

        if ($recentUpload) {
            // If recent upload exists and is not failed, return existing upload
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'File already uploaded recently',
                    'upload' => new UploadResource($recentUpload)
                ], 200);
            }
            
            return redirect()->route('uploads.index')
                ->with('info', 'File already uploaded recently. Processing existing upload.');
        }
        
        // Generate unique filename to prevent overwrites
        $filename = time() . '_' . Str::random(10) . '_' . $originalFilename;
        
        // Store file
        $file->storeAs('uploads', $filename);

        // Create upload record
        $upload = Upload::create([
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'status' => 'pending',
        ]);

        // Dispatch job to process CSV
        ProcessCsvUpload::dispatch($upload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'File uploaded successfully',
                'upload' => new UploadResource($upload)
            ], 201);
        }

        return redirect()->route('uploads.index')
            ->with('success', 'File uploaded successfully. Processing in background.');
    }

    /**
     * Get list of uploads (API)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $uploads = Upload::orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return UploadResource::collection($uploads);
    }

    /**
     * Get single upload status (API)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $upload = Upload::findOrFail($id);
        return new UploadResource($upload);
    }
}