<?php


namespace Exzachly\LaravelWebsockets\Console;


use App;
use Aws\ApiGateway\ApiGatewayClient;
use Aws\Sdk;
use Illuminate\Console\Command;
use function resolve;

class InitApiGateway extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-gateway:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Communicates with AWS using your AWS .env access token and key to create the resources you need to get started using AWS API Gateway driver.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /**
         * @var Sdk $aws
         */
        $aws = resolve('aws');

        $gateway = $aws->createApiGatewayV2();

        $routes = $gateway->getApis([
//            'ResourceId' => '23',
//            'ApiId' => 'neget3j605',
        ]);

        dd($routes);
    }
}
