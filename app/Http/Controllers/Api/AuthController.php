<?php

    namespace app\Http\Controllers\Api;

    use Spatie\RouteAttributes\Attributes\Get;
    use Spatie\RouteAttributes\Attributes\Post;
    use Spatie\RouteAttributes\Attributes\Prefix;

    #[Prefix('auth')]
    readonly class AuthController
    {
        #[Get('info')]
        public function getUserInfo()
        {
            return response()->json([
                'name' => 'Dmm',
                'email' => ''
            ]);
        }

        #[Post('login')]
        public function login()
        {
            return response()->json([
                'message' => 'Login success'
            ]);
        }
    }
