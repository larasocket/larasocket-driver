<?php


namespace Exzachly\Larasocket\Facades;


use Exzachly\Larasocket\LarasocketManager;
use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static string testing()
 *
 * @see LarasocketManager
 */
class Larasocket extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return 'larasocket.manager';
    }
}
