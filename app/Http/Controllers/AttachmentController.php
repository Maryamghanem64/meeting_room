<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $attachments = Attachment::with('meeting')->get();
            return response()->json($attachments, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('filePath')) {
                $file = $request->file('filePath');
                $path = $file->store('attachments', 'public');
                $data['filePath'] = $path;
                $data['fileType'] = $file->getClientOriginalExtension();
            }
            $attachment = Attachment::create($data);
            return response()->json($attachment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attachment = Attachment::with('meeting')->findOrFail($id);
            return response()->json($attachment, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttachmentRequest $request, string $id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $data = $request->validated();
            if ($request->hasFile('filePath')) {
                // Delete old file
                if ($attachment->filePath && Storage::disk('public')->exists($attachment->filePath)) {
                    Storage::disk('public')->delete($attachment->filePath);
                }
                $file = $request->file('filePath');
                $path = $file->store('attachments', 'public');
                $data['filePath'] = $path;
                $data['fileType'] = $file->getClientOriginalExtension();
            }
            $attachment->update($data);
            return response()->json($attachment, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            if ($attachment->filePath && Storage::disk('public')->exists($attachment->filePath)) {
                Storage::disk('public')->delete($attachment->filePath);
            }
            $attachment->delete();
            return response()->json(['message' => 'Attachment deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
