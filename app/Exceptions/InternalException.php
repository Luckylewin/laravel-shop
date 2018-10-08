<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InternalException extends Exception
{
    //
    protected $messageForUser;

    public function __construct(string $message, string $messageForUser = '系统内部错误', int $code = 500)
    {
        parent::__construct($message, $code);
        $this->messageForUser = $messageForUser;
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->messageForUser], $this->code);
        }

        return view('pages.error', ['msg' => $this->messageForUser]);
    }
}
