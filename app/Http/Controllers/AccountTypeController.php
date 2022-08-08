<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreAccountTypeRequest;
use App\Http\Requests\UpdateAccountTypeRequest;
use App\Http\Resources\AccountTypeResource;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class AccountTypeController extends Controller
{

    public static function routeName(){
        return Str::snake("AccountType");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(AccountType::class,Str::snake("AccountType"));
    }
    public function index(Request $request)
    {
        return AccountTypeResource::collection(AccountType::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreAccountTypeRequest $request)
    {
        $accountType = AccountType::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $accountType->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new AccountTypeResource($accountType);
    }
    public function show(Request $request,AccountType $accountType)
    {
        return new AccountTypeResource($accountType);
    }
    public function update(UpdateAccountTypeRequest $request, AccountType $accountType)
    {
        $accountType->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $accountType->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new AccountTypeResource($accountType);
    }
    public function destroy(Request $request,AccountType $accountType)
    {
        $accountType->delete();
        return new AccountTypeResource($accountType);
    }
}
