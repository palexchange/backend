<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Account");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Account::class, Str::snake("Account"));
    }
    public function index(Request $request)
    {
        return AccountResource::collection(Account::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreAccountRequest $request)
    {
        $account = Account::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $account->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new AccountResource($account);
    }
    public function show(Request $request, Account $account)
    {
        return new AccountResource($account);
    }
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $account->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new AccountResource($account);
    }
    public function destroy(Request $request, Account $account)
    {
        $account->delete();
        return new AccountResource($account);
    }
}
