<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $except, $pagination;
    public $bulk_store_mode;
    public $user;
    public function __construct(Request $request)
    {
        $this->user = auth()->user();
        $this->middleware('auth:api', ['except' => $this->except]);
        $this->pagination = (request('itemsPerPage') ?? request('per_page')) ?? 500;
        $this->bulk_store_mode = $request->bulk ? 'insert' : 'create';
    }
}
