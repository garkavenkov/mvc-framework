<?php

namespace   MVC\Framework\Console\Commands;

use AdvancedPrint\AdvancedPrint as AP;
use MVC\Framework\Console\Commands\ConsoleCommand;

class Server extends ConsoleCommand
{
    /**
     * Command usage
     *
     * @var string
     */
    protected $command = "mvc server";

    /**
     * Command description
     *
     * @var string
     */
    protected $description = "Запуск вбудованого веб-сервера.";
    
    /**
     * Command arguments description if there are any
     *
     * @var array
     */
    protected $examples = array(
        'mvc server --host=<IP address> --port=3000' 
            =>  'Запустить вбудований сервер за [U_Yellow]IP адресою компьютера*[Reset] використовуючи [U_Yellow]порт*[Reset] 3000. ',
        'mvc server --env=development'  
            =>  'Запустить вбудований сервер з налаштування середовища із файла .env.development',
    );

    /**
     * Command usage example(s)
     *
     * @var array
     */
    protected $arguments = array(
        '--host'    =>  'Host address for built-in web server (default: [Yellow]127.0.0.1[Reset])',
        '--port'    =>  'Port number for built-in web server (default: [Yellow]8080[Reset])',
        '--www'     =>  'Specify document root for built-in web server (default: [Yellow]public[Reset])',
        '--env'     =>  'Start built-in web server with environment setting'
    );    

    /**
     * Footnotes description, if there are any
     *
     * @var array
     */
    protected $footnotes = array(
        'IP адресою компьютера*'    
            =>  array(
                    "Для доступу до вбудованого сервере через LAN, необхідно використовувати", 
                    "IP адресу компьютера, на якому запускається сервер"
                ),
        'порт*'
            =>  'Номер порта повинен бути вільним і починатися з 1024'
    );

    /**
     * Command handler
     *
     * @param array $argv   Command arguments
     * @return void
     */
    public function handler(array $argv)
    {
        $host = '127.0.0.1';
        $port = 8080;
        $www  = 'public';
        $env  = '';
        
        foreach($argv as $argument) {
            list($arg, $value) = array_pad(explode('=', $argument, 2), 2, '');          
            switch ($arg) {        
                case '--host':
                    $host = $value;
                    break;
                case '--port':          
                    $port = $value;
                    break;        
                case '--www':          
                    $www = $value;
                    break;
                case '--env':          
                    $env = $value;
                    break;
                default:          
                    break;
            }    
        } 
         
        while(true) 
        {
            $connection = @fsockopen($host, $port);    
            if (is_resource($connection)) {      
                echo "Port: $port is already used. Will try next port\n";
                $port++;
                fclose($connection);
            } else {            
                break;
            }    
        }
      
        AP::printLn("[B_White]Starting local server...");
        if ($env !== '') {    
            putenv(sprintf('ENV=%s', $env));
            AP::printLn("[White]Using [B_Red]$env [White]environment.");
        }
      
        exec("php -S $host:$port -t $www");
    }
}
