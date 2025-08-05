<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $attachment=Attachment::with('meeting')->get();
       return response()->json($attachment,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request)
    {
       $attachment = Attachment::create($request->validated());
       return response()->json($attachment,201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $attachment=Attachment::with('meeting')->get()->find($id);
        return response()->json($attachment,201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttachmentRequest $request, string $id)
    {
         $attachment=Attachment::find($id);
         $attachment->update($request->validated());
         return response()->json($attachment,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $attachment=Attachment::find($id);
         $attachment->delete();
         return response()->json(['message'=>'Attachment deleted successfully'],200);
    }
}
