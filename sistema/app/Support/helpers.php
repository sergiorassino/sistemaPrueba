<?php

use App\Support\SchoolContext;

if (! function_exists('schoolCtx')) {
    function schoolCtx(): SchoolContext
    {
        return app(SchoolContext::class);
    }
}
