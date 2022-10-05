<?php

namespace App\Http\Controllers;

use App\Models\UserAccount;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserAccountResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserAccountRequest;
use App\Http\Requests\UpdateUserAccountRequest;

use Illuminate\Support\Facades\Validator;

class UserAccountController extends Controller
{

    public static function routeName()
    {
        return Str::snake("UserAccount");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(UserAccount::class, Str::snake("UserAccount"));
    }
    public function index(Request $request)
    {
        return UserAccountResource::collection(UserAccount::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreUserAccountRequest $request)
    {
        $userAccount = UserAccount::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $userAccount->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new UserAccountResource($userAccount);
    }
    public function show(Request $request, UserAccount $userAccount)
    {
        return new UserAccountResource($userAccount);
    }
    public function update(UpdateUserAccountRequest $request, UserAccount $userAccount)
    {
        $userAccount->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $userAccount->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new UserAccountResource($userAccount);
    }
    public function destroy(Request $request, UserAccount $userAccount)
    {
        $userAccount->delete();
        return new UserAccountResource($userAccount);
    }
}
