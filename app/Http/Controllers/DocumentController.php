<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image; // optional, but we'll fallback to GD if not present

class DocumentController extends Controller
{
    public function index()
    {
        $q = request()->query('q');
        $query = Document::query();

        if ($q) {
            $query->where(function($qf) use ($q) {
                $qf->where('title', 'like', "%{$q}%")
                    ->orWhere('filename', 'like', "%{$q}%")
                    ->orWhere('mime_type', 'like', "%{$q}%");
                // notes column may not exist; guard it
                if (Schema::hasColumn('documents', 'notes')) {
                    $qf->orWhere('notes', 'like', "%{$q}%");
                }
                // remarks column may exist; include it in search
                if (Schema::hasColumn('documents', 'remarks')) {
                    $qf->orWhere('remarks', 'like', "%{$q}%");
                }
            });
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        return view('documents.index', compact('documents', 'q'));
    }

    // Separate endpoint for AJAX list (used by live search)
    public function list(Request $request)
    {
        $q = $request->query('q');
        $query = Document::query();

        if ($q) {
            $query->where(function($qf) use ($q) {
                $qf->where('title', 'like', "%{$q}%")
                    ->orWhere('filename', 'like', "%{$q}%")
                    ->orWhere('mime_type', 'like', "%{$q}%");
                if (Schema::hasColumn('documents', 'notes')) {
                    $qf->orWhere('notes', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('documents', 'remarks')) {
                    $qf->orWhere('remarks', 'like', "%{$q}%");
                }
            });
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        return view('documents._list', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'order_no' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240', // 10MB
            'cameraImage' => 'nullable|string',
        ]);
        // If cameraImage is provided as base64 (accept both data URL and raw base64)
        if ($request->filled('cameraImage')) {
            $data = $request->input('cameraImage');

            // If it's a data URL, extract mime and base64
            if (preg_match('/^data:(image\/[^;]+);base64,/', $data, $m)) {
                $mime = $m[1];
                $base64 = substr($data, strpos($data, ',') + 1);
            } else {
                // assume raw base64 image (jpeg)
                $mime = 'image/jpeg';
                $base64 = $data;
            }

            $binary = base64_decode($base64);
            if ($binary === false) {
                logger()->error('Document store: invalid base64 data');
                return back()->withErrors(['cameraImage' => 'Invalid image data (base64)']);
            }

            $ext = explode('/', $mime)[1] ?? 'jpg';
            // build filename from metadata: DocumentType_OrderNo_OrderDate
            $docType = $request->input('document_type');
            $orderNo = $request->input('order_no');
            $orderDate = $request->input('order_date');
            $parts = array_filter([$docType, $orderNo, $orderDate]);
            if (!empty($parts)) {
                $slugParts = array_map(function($p){ return Str::slug($p, '_'); }, $parts);
                $base = implode('_', $slugParts);
            } else {
                $base = 'scan_' . uniqid();
            }
            $filename = 'documents/' . $base . '.' . $ext;
            // ensure unique
            if (Storage::exists($filename)) {
                $filename = 'documents/' . $base . '_' . uniqid() . '.' . $ext;
            }
            Storage::put($filename, $binary);

            $size = strlen($binary);

            $doc = Document::create([
                'title' => $request->input('title') ?? $request->input('document_type') ?? 'Scanned Document',
                'document_type' => $request->input('document_type'),
                'order_no' => $request->input('order_no'),
                'order_date' => $request->input('order_date'),
                'remarks' => $request->input('remarks'),
                'filename' => $filename,
                'mime_type' => $mime,
                'size' => $size,
                'uploaded_by' => $request->user()?->id ?? null,
            ]);

            // generate a small thumbnail for images if possible
            if (strpos($mime, 'image/') === 0) {
                try {
                    // try using Intervention if available
                    if (class_exists('\Intervention\Image\ImageManagerStatic')) {
                        $img = \Intervention\Image\ImageManagerStatic::make($binary)->fit(200, 200)->encode('jpg', 80);
                        $thumbPath = 'documents/thumb_' . uniqid() . '.jpg';
                        Storage::put($thumbPath, (string) $img);
                        $doc->thumbnail = $thumbPath;
                        $doc->save();
                    } else {
                        // fallback: create GD thumbnail
                        $im = imagecreatefromstring($binary);
                        if ($im !== false) {
                            $w = imagesx($im);
                            $h = imagesy($im);
                            $sizeThumb = 200;
                            $thumb = imagecreatetruecolor($sizeThumb, $sizeThumb);
                            imagecopyresampled($thumb, $im, 0,0,0,0, $sizeThumb, $sizeThumb, $w, $h);
                            ob_start();
                            imagejpeg($thumb, null, 80);
                            $thumbData = ob_get_clean();
                            imagedestroy($im);
                            imagedestroy($thumb);
                            $thumbPath = 'documents/thumb_' . uniqid() . '.jpg';
                            Storage::put($thumbPath, $thumbData);
                            $doc->thumbnail = $thumbPath;
                            $doc->save();
                        }
                    }
                } catch (\Throwable $e) {
                    logger()->warning('Thumbnail generation failed: ' . $e->getMessage());
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['status' => 'ok', 'document' => $doc]);
            }

            return redirect()->route('documents.index')->with('status', 'Document saved from camera.');
        }

        // If regular file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // build filename from metadata and original extension
            $ext = $file->getClientOriginalExtension() ?: 'bin';
            $docType = $request->input('document_type');
            $orderNo = $request->input('order_no');
            $orderDate = $request->input('order_date');
            $parts = array_filter([$docType, $orderNo, $orderDate]);
            if (!empty($parts)) {
                $slugParts = array_map(function($p){ return Str::slug($p, '_'); }, $parts);
                $base = implode('_', $slugParts);
            } else {
                $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'upload_' . uniqid();
            }
            $name = $base . '.' . $ext;
            // ensure unique
            $i = 0;
            $candidate = 'documents/' . $name;
            while (Storage::exists($candidate)) {
                $i++;
                $candidate = 'documents/' . $base . '_' . $i . '.' . $ext;
            }
            $path = $file->storeAs('documents', basename($candidate));

            $doc = Document::create([
                'title' => $request->input('title') ?? $request->input('document_type') ?? $file->getClientOriginalName(),
                'document_type' => $request->input('document_type'),
                'order_no' => $request->input('order_no'),
                'order_date' => $request->input('order_date'),
                'remarks' => $request->input('remarks'),
                'filename' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()?->id ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['status' => 'ok', 'document' => $doc]);
            }

            return redirect()->route('documents.index')->with('status', 'File uploaded.');
        }

        return back()->withErrors(['file' => 'No file or camera image provided']);
    }

    public function show(Document $document)
    {
        if (!Storage::exists($document->filename)) {
            abort(404);
        }

        // If ?download=1 is present, send as download
        if (request()->query('download')) {
            return response()->download(Storage::path($document->filename), basename($document->filename));
        }

        return response()->file(Storage::path($document->filename));
    }

    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'order_no' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $document->update($data);

        return redirect()->route('documents.index')->with('status', 'Document updated');
    }

    public function destroy(Document $document)
    {
        // delete file from storage if exists
        if (Storage::exists($document->filename)) {
            Storage::delete($document->filename);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('status', 'Document deleted');
    }
}
