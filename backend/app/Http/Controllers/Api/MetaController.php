<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;

class MetaController extends Controller
{
    public function users()
    {
        return User::query()->select('id','name','email')->orderBy('name')->get();
    }

    public function statuses()
    {
        return Status::query()->select('id','name')->orderBy('id')->get();
    }
}


