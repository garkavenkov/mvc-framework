<?php

namespace MVC\Framework\Console;

use MVC\Framework\Console\Commands\Server;
use MVC\Framework\Console\Commands\MakeModel;
use MVC\Framework\Console\Commands\RouteList;
use MVC\Framework\Console\Commands\MakeCommand;
use MVC\Framework\Console\Commands\MakeController;
use AdvancedPrint\AdvancedPrint as AP;

class Runner
{
    /**
     * Available commands
     *
     * @var array
     */
    private $available_commands = [        
        'server'            =>  Server::class,
        'make:controller'   =>  MakeController::class,
        'make:model'        =>  MakeModel::class,
        'make:command'      =>  MakeCommand::class,
        'route:list'        =>  RouteList::class,
    ];    

    /**
     * Constructor
     */
    public function __construct()
    {
        $file_name = CONFIG_DIR . '/commands.php';
        if (file_exists($file_name)) {
            $config_commands = include $file_name;
            if ($config_commands) {
                $this->available_commands = array_merge($this->available_commands, $config_commands);
            }
        }
    }

    /**
     * Output available commands list
     *
     * @return void
     */
    private function availableCommandsList()
    {
        AP::printLn("\n[B_White]Доступні команди:");
        $commands = [];
        foreach($this->available_commands as $command => $class) {            
            $properties = (new \ReflectionClass($class))->getDefaultProperties();
            $description = array_key_exists('description', $properties) ? $properties['description'] : '';
            $commands[$command] = $description;            
        }
        $longest_command = max(array_map('strlen', array_keys($commands)))+5;
        AP::printf("    [Green]list", $longest_command, 0, ' ');
        AP::printLn(" - " . "Виводить поточну інформацію");
        AP::printf("    [Green]help", $longest_command, 0, ' ');
        AP::printLn(" - " . "Виводить допомогу по використанню команд");
        foreach($commands as $command => $description) {                            
            AP::printf("    [Green]{$command}", $longest_command, 0, ' ');
            AP::printLn(" - " . $description);            
        }
        echo "\n";
    }

    /**
     * Call command help function
     *
     * @param array $argv   Arguments
     * @return void
     */
    private function displayCommandHelp(array $argv)
    {
        if (!empty($argv)) {
            // Command help
            $command =  array_shift($argv);
            if (array_key_exists($command, $this->available_commands)) {
                call_user_func(array(new $this->available_commands[$command], 'help'));
            } else {
                AP::printLn("\n[B_Red]Помилка: [Reset]Команда '$command' відсутня\n");
            }
        } else {
            AP::printLn("\n[B_White]Командна утіліта для фреймфворка MVC\n");
            AP::printLn("[Cyan]Використовуйте:\n");
            AP::printLn("    [B_White]mvc [Yellow]command [Reset]<name> [arguments]\n");
            AP::printLn('    де: [Yellow]command [Reset]  - им`я команди');
            // AP::printLn('        [Yellow]command');
            AP::printLn('        [Cyan]name [Reset]     - в залежності від команди може бути відсутне, або містити назву класу');
            AP::printLn('        [Green]arguments [Reset]- опціональні параметри команди');
            echo "\n";
            AP::printLn("[Cyan]Наприклад:\n");
            AP::printLn("    mvc list [Reset]        - перелік доступних команд");
            AP::printLn("    mvc help [Reset]        - відображення поточної інформації");
            AP::printLn("    mvc help [Yellow]command [Reset]- відображення домопоги по команді [Yellow]command");
            echo "\n";
        }
    }

    /**
     * Command runner
     *
     * @param array $argv   Arguments
     * @return void
     */
    public function run(array $argv)
    {        
        if (count($argv) == 1) {  
            AP::printLn("\n[B_White]Відсутня команда:");
            AP::printLn("\n[Cyan]Використовуйте:");
            // echo  "Usage:\n";
            echo  "  command <name> [arguments]\n";

            AP::printLn("\n[Cyan]Для отримання переліку доступних команд використовуйте команду: [B_Green]list");
            echo  "\n";
            exit(0);
        } else {
            array_shift($argv);
        }
        
        $command = array_shift($argv);
        
        if (array_key_exists($command, $this->available_commands)) {            
            call_user_func_array(array(new $this->available_commands[$command], 'handler'), [$argv]);
        } elseif ($command == 'list') {    
            $this->availableCommandsList();
        } elseif ($command == 'help') {    
            $this->displayCommandHelp($argv);
        } else {
            exit("Command $command does not exists.\n");
        }
    }
}