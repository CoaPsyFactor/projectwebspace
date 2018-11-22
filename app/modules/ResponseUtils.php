<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 24.2.2016.
 * Time: 14.18
 */

namespace App\Modules;


interface ResponseUtils
{
    const
        CODE_CONTINUE           = 100;

    const
        CODE_SUCCESS            = 200,
        CODE_CREATED            = 201,
        CODE_ACCEPTED           = 202;

    const
        CODE_MOVED_PERM         = 301,
        CODE_FOUND              = 302;

    const
        CODE_BAD_REQUEST        = 400,
        CODE_UNAUTHORIZED       = 401,
        CODE_PAYMENT_REQUIRED   = 402,
        CODE_FORBIDDEN          = 403,
        CODE_NOT_FOUND          = 404,
        CODE_METHOD_NOT_ALLOWED = 405,
        CODE_NOT_ACCEPTABLE     = 406,
        CODE_REQUEST_TIMEOUT    = 408,
        CODE_GONE               = 410,
        CODE_URI_TOO_LONG       = 414;

    const
        CODE_INTERNAL_ERROR     = 500,
        CODE_NOT_IMPLEMENTED    = 501,
        CODE_BAD_GATEWAY        = 502,
        CODE_SERVICE_UNAVAIL    = 503,
        CODE_GATEWAY_TIMEOUT    = 504;

    const
        TYPE_JSON               = 'application/json',
        TYPE_HTML               = 'text/html',
        TYPE_CSS                = 'text/css',
        TYPE_JAVASCRIPT         = 'text/javascript',
        TYPE_PLAIN              = 'text/plain';
}