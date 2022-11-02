<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartyRequest;
use App\Http\Requests\UpdatePartyRequest;
use App\Http\Resources\PartyResource;
use App\Models\Account;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class PartyController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Party");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Party::class, Str::snake("Party"));
    }
    public function index(Request $request)
    {
        return PartyResource::collection(Party::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StorePartyRequest $request)
    {
        $validation = $request->validated();
        if (!isset($validation['account_id'])) {
            $account =  Account::create(['name' => $request->validated()['name'], 'type_id' => 1]);
            $validation += ['account_id' => $account->id];
        }
        $party = Party::create($validation);
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $party->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }

        return new PartyResource($party);
    }
    public function show(Request $request, Party $party)
    {
        return new PartyResource($party);
    }
    public function update(UpdatePartyRequest $request, Party $party)
    {
        $party->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $party->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new PartyResource($party);
    }
    public function destroy(Request $request, Party $party)
    {
        $party->delete();
        return new PartyResource($party);
    }
}
