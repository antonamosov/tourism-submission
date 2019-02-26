<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\ClientStoreRequest;
use App\Services\ClientService;

class ApiClientController extends Controller
{
    public function store(ClientStoreRequest $request)
    {
        app()->make(ClientService::class)->create($request->all());

        return response()->json([
            'message' => 'Clients have been successfully added.',
        ]);
    }
}
