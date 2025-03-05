<?php

    namespace app\Http\Controllers\Api;

    use Spatie\RouteAttributes\Attributes\Get;

    class TestController
    {
        #[Get("test")]
        public function index()
        {
            return response()->json([
                'name' => 'Dmm',
                'email' => ''
            ]);
        }
    }
