<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{

    public static function routeName()
    {
        return Str::snake("File");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(File::class, Str::snake("File"));
    }
    public function index(Request $request)
    {
        return FileResource::collection(File::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreFileRequest $request)
    {
        // $file = File::create($request->validated());
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        $mimetype = $file->getClientOriginalExtension();
        $path = $file->store(
            'files',
            'public'
        );
        $arr = [
            'attachable_type' => $request->attachable_type,
            'attachable_id' => $request->attachable_id,
            // 'user_id' => $request->user_id,
            'path' => $path,
            'name' => $name,
            'mimetype' => $mimetype,

        ];
        $file = File::create($arr);
        return new FileResource($file);
    }
    public function show(Request $request, File $file)
    {
        return new FileResource($file);
    }
    public function update(UpdateFileRequest $request, File $file)
    {
        $file->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $file->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new FileResource($file);
    }
    public function destroy(Request $request, File $file)
    {
        $file->delete();
        return new FileResource($file);
    }
}
