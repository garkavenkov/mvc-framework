<?php

namespace MVC\Framework\Console\Commands;

use AdvancedPrint\AdvancedPrint as AP;
use MVC\Framework\Console\Commands\ConsoleCommand;

class MakeCommand extends ConsoleCommand
{  
    /**
     * Command usage
     *
     * @var string
     */
    protected $command = "mvc make:command <CommandName> [arguments]";

    /**
     * Command description
     *
     * @var string
     */
    protected $description  = "Створює шаблон нової команди користувача. Для користування командою, її треба [U_Yellow]зареєструвати у файлі*";

    /**
     * Command arguments description if there are any
     *
     * @var array
     */
    protected $arguments = array(
        // 'argument'        =>  'argument description', 
    );

    /**
     * Command usage example(s)
     *
     * @var array
     */
    protected $examples = array(
        'mvc make:command TestCommand'
            =>  'Створить макет команді TestCommand',        
    );

    /**
     * Footnotes description, if there are any
     *
     * @var array
     */
    protected $footnotes = array(
        'зареєструвати у файлі*'  =>  array (            
            'Додати запис у файл app/config/commands.php у вигляді',
            "[D_White]//command => class",
            "'test'    => TestCommand::class"
        )            
    );

    /**
     * Command handler
     *
     * @param array $argv   Command arguments
     * @return void
     */
    public function handler($argv)
    {
        $command_name = $this->getName($argv);
        
        $namespace = $this->resolveNamespace($command_name, 'App\Console');
        
        $file_path = $this->resolveFilePath($command_name, 'app\Console');        
        
        $command_name = basename($command_name);
        
        $content  = "<?php\n";
        $content .= "\n";
        $content .= "namespace $namespace;\n";  
        $content .= "\n";
        $content .= "use AdvancedPrint\AdvancedPrint as AP;\n";
        $content .= "use MVC\Framework\Console\Commands\ConsoleCommand;\n";
        $content .= "\n";
        $content .= "class $command_name extends ConsoleCommand\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Command usage\n";
        $content .= "     *\n";
        $content .= "     * @var string\n";
        $content .= "     */\n";
        $content .= "    protected \$command = \"mvc <command> [arguments]\";\n";
        $content .= "\n";
        $content .= "    /**\n";
        $content .= "     * Command description\n";
        $content .= "     *\n";
        $content .= "     * @var string\n";
        $content .= "     */\n";
        $content .= "    protected \$description  = \"Command description.\";\n";
        $content .= "\n";
        $content .= "    /**\n";
        $content .= "     * Command arguments description if there are any\n";
        $content .= "     *\n";
        $content .= "     * @var array\n";
        $content .= "     */\n";
        $content .= "    protected \$arguments  = array(\n";
        $content .= "        //argument => description\n";
        $content .= "    );\n";
        $content .= "\n";
        $content .= "    /**\n";
        $content .= "     * Command usage example(s)\n";
        $content .= "     *\n";
        $content .= "     * @var array\n";
        $content .= "     */\n";
        $content .= "    protected \$examples  = array(\n";
        $content .= "        //mvc <command> => command result\n";
        $content .= "    );\n";
        $content .= "\n";
        $content .= "    /**\n";
        $content .= "     * Footnotes description, if there are any\n";
        $content .= "     *\n";
        $content .= "     * @var array\n";
        $content .= "     */\n";
        $content .= "    protected \$footnotes  = array(\n";
        $content .= "        //footnote* => footnote description\n";
        $content .= "    );\n";
        $content .= "\n";            
        $content .= "    /**\n";
        $content .= "     * Command handler\n";
        $content .= "     *\n";
        $content .= "     * @param array \$argv   Command arguments\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function handler(array \$argv)\n";
        $content .= "    {\n";
        $content .= "        echo \"\\n\";\n";
        $content .= "        AP::printLn(\"[Green]Finish your command\");\n";
        $content .= "        echo \"\\n\";\n";
        $content .= "    }\n";    
        $content .= "\n";
        $content .= "}\n";
          
        if ($this->createFile($command_name, $file_path, $content)) {
            echo "\n";                        
            AP::printLn("Class [B_Green]{$command_name}[Reset] has been successfully created.");                        
            echo "\n";                
        }
    }
}