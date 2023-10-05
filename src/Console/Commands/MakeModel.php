<?php

namespace MVC\Framework\Console\Commands;

use AdvancedPrint\AdvancedPrint as AP;
use MVC\Framework\Console\Commands\ConsoleCommand;

class MakeModel extends ConsoleCommand
{
    /**
     * Command usage
     *
     * @var string
     */
    protected $command = 'make:model <name> [arguments]';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Створити новий клас моделі.';

    /**
     * Command arguments description if there are any
     *
     * @var array
     */
    protected $arguments = array(        
        '--controller=[resource|api]' =>  'Створити контролер <name>Controller з методами [U_Yellow]resource*[Reset] або [U_Yellow]api*[Reset]',
    );

    /**
     * Command usage example(s)
     *
     * @var array
     */
    protected $examples = array(
        'mvc make:model Post'
            =>  'Створить клас моделі Post',
        'mvc make:model Post --controller=api'
            =>  'Створить клас моделі Post разом з контроллером PostController з методами [U_Yellow]api*',
    );

    /**
     * Footnotes description, if there are any
     *
     * @var array
     */
    protected $footnotes = array(
        'resource*'  =>  'Містить методи [B_White]index, show, edit, update, create, store, delete, destroy',
        'api*'       =>  'Містить методи притаманні для обробки api запитів: [B_White]index, show, update, store, destroy' 
    );

    /**
     * Command handler
     *
     * @param array $argv   Command arguments
     * @return void
     */
    public function handler(array $argv)
    {        
        $model_name = $this->getName($argv);
        
        $namespace = $this->resolveNamespace($model_name, 'App\Models');
        
        $file_path = $this->resolveFilePath($model_name, 'app/Models');        
        
        $model_name = basename($model_name);
                  
        $controller_name = '';
        $controller_type = '';
        
        foreach($argv as $argument) {        
            list($arg, $value) = array_pad(explode('=', $argument, 2), 2, '');
            if ($arg == '--controller') {
                $controller_name = $model_name . 'Controller';
                if (in_array($value, ['resource', 'api'])) {
                  $controller_type = '--' . $value;
                }
            }              
        }          

        $content  = "<?php\n";
        $content .= "\n";
        $content .= "namespace $namespace;\n";
        $content .= "\n";
        $content .= "use MVC\Framework\Base\Model;\n";
        $content .= "\n";
        $content .= "class $model_name extends Model\n";
        $content .= "{\n";
        $content .= "\n";
        $content .= "}\n";
        
        if ($this->createFile($model_name, $file_path, $content)) {
            echo "\n";
            AP::printLn("Class [B_Green]{$model_name}[Reset] has been successfully created.");
            if ($controller_name !== '') {            
                (new MakeController())->handler([$controller_name, $controller_type]);
            }
        }          
    }
}
