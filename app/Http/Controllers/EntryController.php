<?php

namespace App\Http\Controllers;

use App\Events\DocumentDeletedEvent;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Http\Resources\EntryResource;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class EntryController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Entry");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Entry::class, Str::snake("Entry"));
    }
    public function index(Request $request)
    {
        return EntryResource::collection(Entry::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreEntryRequest $request)
    {
        $entry = Entry::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $entry->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new EntryResource($entry);
    }
    public function show(Request $request, Entry $entry)
    {
        return new EntryResource($entry);
    }
    public function update(UpdateEntryRequest $request, Entry $entry)
    {
        $entry->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $entry->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new EntryResource($entry);
    }
    public function destroy(Request $request, Entry $entry)
    {

        $entry->dispose();
        // $entry->save();
        return new EntryResource($entry);
    }
}
