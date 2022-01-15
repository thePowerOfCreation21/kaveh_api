<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (isset($_SERVER['HTTP_ORIGIN']))
        {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            /*
            switch($_SERVER['HTTP_ORIGIN'])
            {
                //Handle an IP address and Port
                case 'http://1.2.3.4:4200':
                    header('Access-Control-Allow-Origin: http://1.2.3.4:4200');
                    break;
                //Handle an Website Domain (using https)
                case 'https://www.someSite.com':
                    header('Access-Control-Allow-Origin: https://www.someSite.com');
                    break;
                //Handle an Website Domain (using http)
                case 'http://www.someSite.com':
                    header('Access-Control-Allow-Origin: http://www.someSite.com');
                    break;
                //Catch if someone's site is actually the reject being cheeky
                case 'https://not.you':
                    header('Access-Control-Allow-Origin: https://nice.try');
                    break;
                //Handle a rejection passing something that is not the request origin.
                default:
                    header('Access-Control-Allow-Origin: https://not.you');
                    break;
            }
            */
        }
        else
        {
            header('Access-Control-Allow-Origin: http://localhost:8081');
        }
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token,Authorization');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json; charset=utf-8');
        header("Cache-Control: public,max-age=3600");
        //if its an options request you don't need to proceed past CORS request.
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            die();
        }

        return $next($request);
    }
}
