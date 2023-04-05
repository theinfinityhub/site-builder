<?php

namespace Kavi\SiteEditor\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SiteEditor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SiteEditorController extends Controller
{
    public function editor($business, Request $request)
    {
        return view('editor::editor', compact('business'));
    }

    public function upload(Request $request, $business)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpg,jpeg,png,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $fileName = time() . rand() . '.' . $request->file->extension();
        $request->file->storeAs("site-editor/" . $business, $fileName, 'public');

        return $fileName;
    }

    public function scan(Request $request, $business)
    {
        $mediaPath = $request->input('mediaPath', public_path('storage/site-editor/' . $business));

        $response = $this->scanDirectory($mediaPath);

        return response()->json([
            'name'  => '',
            'type'  => 'folder',
            'path'  => '',
            'items' => $response,
        ]);
    }

    private function scanDirectory($dir)
    {
        $files = [];

        $directories = File::directories($dir);
        $filesInDir = File::files($dir);

        foreach ($directories as $directory) {
            $files[] = [
                'name'  => basename($directory),
                'type'  => 'folder',
                'path'  => str_replace(public_path(), '', $directory),
                'items' => $this->scanDirectory($directory),
            ];
        }

        foreach ($filesInDir as $file) {
            $files[] = [
                'name' => $file->getFilename(),
                'type' => 'file',
                'path' => str_replace(public_path(), '', $file->getPathname()),
                'size' => $file->getSize(),
            ];
        }

        return $files;
    }

    public function save(Request $request, $business)
    {
        $html = $this->sanitizeFileName($request->input('html'));
        
        $user = auth()->user()->business()->first();
        
        $user->site_editor()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'user_id' => auth()->id(),
                'content' => $html
            ]
        );

        return "File saved <a href='/$business' target='_blank'>$business</a> ;)";
    }

    private function sanitizeFileName($file)
    {
        //sanitize, remove double dot .. and remove get parameters if any
        $file = preg_replace('@\?.*$@', '', preg_replace('@\.{2,}@', '', preg_replace('@[^\/\\a-zA-Z0-9{}\-\._]@', '', $file)));
        return $file;
    }
}
