<?php
namespace DMA\Friends\Commands;

use Log;
use APP;
use Swagger;
use ReflectionClass;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use DMA\Friends\Facades\FriendsAPI;

/**
 * Generate API documentation
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class GenerateAPIDocs extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:generate-api-docs';


    /**
     * @return void
     */
    public function fire()
    {

        $output = dirname(__DIR__) . '/docs/api-docs/specs/swagger.json';
        
        $paths = $this->getScanFolders();
        $exclude = array_map(function($p){
           return  $p . DIRECTORY_SEPARATOR . 'vendor';
        }, $paths);
        
        $this->prepareSwagger();
        $swagger = Swagger\scan($paths, ['exclude'=> $exclude ]);

        $methods = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch'];
        $counter = 0;
        // Output report
        foreach ($swagger->paths as $path) {
            foreach ($path as $method => $operation) {
                if ($operation !== null && in_array($method, $methods)) {
                    error_log(str_pad($method, 7, ' ', STR_PAD_LEFT) . ' ' . $path->path);
                    $counter++;
                }
            }
        }
        error_log('----------------------'.  str_repeat('-', strlen($counter)));
        error_log($counter.' operations documented');
        error_log('----------------------'.  str_repeat('-', strlen($counter)));
        
     
        $swagger->saveAs($output);
        error_log('Written to '.realpath($output));
    }
        

    protected function prepareSwagger(){
        $errorTypes = [
                E_ERROR => 'ERROR',
                E_WARNING => 'WARNING',
                E_PARSE => 'PARSE',
                E_NOTICE => 'NOTICE',
                E_CORE_ERROR => 'CORE_ERROR',
                E_CORE_WARNING => 'CORE_WARNING',
                E_COMPILE_ERROR => 'COMPILE_ERROR',
                E_COMPILE_WARNING => 'COMPILE_WARNING',
                E_USER_ERROR => 'ERROR',
                E_USER_WARNING => 'WARNING',
                E_USER_NOTICE => 'NOTICE',
                E_STRICT => 'STRICT',
                E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
                E_DEPRECATED => 'DEPRECATED',
                E_USER_DEPRECATED => 'DEPRECATED'
        ];
        
        set_error_handler(function ($errno, $errstr, $file, $line) use ($errorTypes) {
            if (!(error_reporting() & $errno)) {
                return; // This error code is not included in error_reporting
            }
            $type = @$errorTypes[$errno] ?: 'ERROR';
            error_log('[' . $type . '] '.$errstr .' in '.$file.' on line '.$line);
            if ($type === 'ERROR') {
                exit($errno);
            }
        });
        
        set_exception_handler(function ($exception) {
            error_log('[EXCEPTION] '.$exception->getMessage() .' in '.$exception->getFile().' on line '.$exception->getLine());
            exit($exception->getCode() ?: 1);
        });
        
        Swagger\Logger::getInstance()->log = function ($entry, $type) {
            $type = $type === E_USER_NOTICE ? 'INFO' : 'WARN';
            if ($entry instanceof Exception) {
                $entry = $entry->getMessage();
            }
            error_log('[' . $type . '] ' . $entry . PHP_EOL);
        };
    }
    
    
    /**
     * Get list of plugin folders Swagger needs to scan
     */
    
    public function getScanFolders()
    {
        $pluginsClassNames = [];
        // Get plugins folders that register endpoints in Friends API
        foreach(FriendsAPI::getResources() as $url => $class){
            try{
                $bits = explode('\\', $class);
                $pluginClassname = implode('\\', array_merge(array_slice($bits, 0, 2), ['Plugin']));
                $pluginsClassNames[] = $pluginClassname;
        
            }catch(Exception $e){
                Log::error("API : Resource endpoint fail to register due to '" . $e->getMessage() . "'");
            }
        }
        
        $scanFolders = [];
        // Get folder paths
        foreach(array_unique($pluginsClassNames) as $plugin){
            $reflector = new ReflectionClass($plugin);
            $fn = $reflector->getFileName();
            $scanFolders[] = dirname($fn);
        }
        
        return $scanFolders;
    }
    
    
}