<?php

namespace Alzundaz\View\Services;

use Alzundaz\NitroPHP\Services\ConfigHandler;
use Alzundaz\NitroPHP\Services\CacheHandler;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Profiler_Profile;
use Twig_Extension_Profiler;
use \Odan\Twig\TwigAssetsExtension;

class TwigFactory
{
    private $ConfigHandler;

    private $CacheHandler;

    private $appconf;

    public function __construct()
    {
        $this->ConfigHandler = ConfigHandler::getInstance();
        $this->CacheHandler = CacheHandler::getInstance();
        $this->appconf = $this->ConfigHandler->getAppConf();
    }

    public static function getTwigFactory()
    {
        return new self;
    }

    public function getTwig()
    {
        if( !file_exists( WEBROOT_DIR.'/assets' ) ) mkdir(WEBROOT_DIR.'/assets', 0775);

        $loader = new Twig_Loader_Filesystem();

        foreach($this->ConfigHandler->getModule() as $module){
            if($module['enabled']){
                if( file_exists( ROOT_DIR.'/Module/'.$module['name'].'/View/' ) ){
                    $loader->addPath(ROOT_DIR.'/Module/'.$module['name'].'/View', $module['name']);
                }
            }
        }

        $twig = new Twig_Environment($loader, array(
            'cache' => ($this->appconf['cachemode'] ? ROOT_DIR.'/Var/Cache/View' : false),
            'auto_reload' => ($this->appconf['dev'])
        ));

        if($this->appconf['dev']){
            $profile = new Twig_Profiler_Profile();
            $twig->addExtension(new Twig_Extension_Profiler($profile));
            // Profiler::getInstance()->setTwigProfil($profile);
            // $dumper = new Twig_Profiler_Dumper_Html();
        }
        $twig->addExtension( new TwigAssetsExtension( $twig, $this->configureAssets() ) );

        return $twig;
    }

    private function configureAssets():array
    {
        return $options = [
            // Public assets cache directory
            'path' => WEBROOT_DIR.'/assets',
            
            // Public cache directory permissions (octal)
            // You need to prefix mode with a zero (0)
            // Use -1 to disable chmod
            'path_chmod' => 0750,
            
            // The public url base path
            'url_base_path' => 'assets/',
            
            // Internal cache settings
            //
            // The main cache directory
            // Use '' (empty string) to disable the internal cache
            'cache_path' => ROOT_DIR.'/Var/Assets',
            
            // Used as the subdirectory of the cache_path directory, 
            // where cache items will be stored
            'cache_name' => 'assets-cache',
            
            // The lifetime (in seconds) for cache items
            // With a value 0 causing items to be stored indefinitely
            'cache_lifetime' => 0,
            
            // Enable JavaScript and CSS compression
            // 1 = on, 0 = off
            'minify' => 1
        ];
    }
}