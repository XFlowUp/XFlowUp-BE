<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GithubWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        dd($request->all());
    }
}
