<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{

    public static function routeName()
    {
        return Str::snake("User");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(User::class, Str::snake("User"));
    }
    public function index(Request $request)
    {
        return UserResource::collection(User::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $user->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new UserResource($user);
    }
    public function show(Request $request, User $user)
    {
        return new UserResource($user);
    }
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $user->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new UserResource($user);
    }
    public function destroy(Request $request, User $user)
    {
        $user->delete();
        return new UserResource($user);
    }
}
