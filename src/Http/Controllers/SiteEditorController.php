<?php

namespace OneClx\SiteBuilder\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use URL;
use App\Models\Business;

class SiteEditorController extends Controller
{
    public function editor($business, Request $request)
    {
        return view('editor::editor', compact('business'));
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpg,jpeg,png,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $fileName = time() . rand() . '.' . $request->file->extension();
        $request->file->storeAs("site-editor/" . auth()->user()->business()->first()->slug, $fileName, 'public');

        return $fileName;
    }

    public function scan(Request $request)
    {
        $mediaPath = $request->input('mediaPath', public_path('storage/site-editor/' . auth()->user()->business()->first()->slug));

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
    
    public function save(Request $request)
    {
        $html = $this->sanitizeFileName($request->input('html'));
       
        if(auth()->user()->roles['0']->name == 'super-admin'){
                $currenturl = url()->previous();
                $business = explode("/", $currenturl); 
                $business_name =  $business[5];
                $user = Business::where("slug","=", $business_name)->first();
                $user->site_editor()->updateOrCreate(
                    ['user_id' => $user->_id],
                    [
                        'user_id' => $user->_id,
                        'content' => $html
                    ]
                );
        }else{
                $user = auth()->user()->business()->first();
                $user->site_editor()->updateOrCreate(
                    ['user_id' => auth()->id()],
                    [
                        'user_id' => auth()->id(),
                        'content' => $html
                    ]
                );
        }
        return "Page was successfully saved! <a href='/{$user->slug}'>{$user->slug}</a> ;)";
    }

    private function sanitizeFileName($file)
    {
        //sanitize, remove double dot .. and remove get parameters if any
        $file = preg_replace('@\?.*$@', '', preg_replace('@\.{2,}@', '', preg_replace('@[^\/\\a-zA-Z0-9{}\-\._]@', '', $file)));
        return $file;
    }

    public function reset(Request $request)
    {
        if(auth()->user()->roles['0']->name == 'super-admin'){
            $currenturl = url()->previous();
            $business = explode("/", $currenturl); 
            $business_name =  $business[5];
            $user = Business::where("slug","=", $business_name)->first();
        }else{
            $user = auth()->user()->business()->first();
        }       
        
        $user->site_editor()->delete();
        return redirect()->back();
    }
}
