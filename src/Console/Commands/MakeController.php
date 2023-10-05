<?php

namespace MVC\Framework\Console\Commands;

use AdvancedPrint\AdvancedPrint as AP;
use MVC\Framework\Console\Commands\ConsoleCommand;

class MakeController extends ConsoleCommand
{   
    /**
     * Command usage
     *
     * @var string
     */
    protected $command = "mvc make:controller <name> [arguments]";

    /**
     * Command description
     *
     * @var string
     */
    protected $description  = "Створює новий класс контроллера.";

    /**
     * Command arguments description if there are any
     *
     * @var array
     */
    protected $arguments = array(
        '--resource'        =>  'Створити контролер <name> з методами [U_Yellow]resource*', 
        '--api'             =>  'Створити контролер <name> з методами [U_Yellow]api*',
        '--model=<Model>'   =>  'Створить контролер <name> разом з класом моделі [Yellow]<Model>'
    );

    /**
     * Command usage example(s)
     *
     * @var array
     */
    protected $examples = array(
        'mvc make:controller PostController --api'          
            =>  'Створить контролер PostController з методами [U_Yellow]api*',
        'mvc make:controller PostController --model=Post'   
            =>  'Створить контролер PostController разом з класом моделі Post',
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
    
        $controller_name = $this->getName($argv);
        
        $namespace = $this->resolveNamespace($controller_name, 'App\Http\Controllers');
        
        $file_path = $this->resolveFilePath($controller_name, 'app\Http\Controllers');        
        
        $controller_name = basename($controller_name);
        
        $methods = [
            'index' => [
                'comment' => 'Display a listing of the resource.'
            ],
            'show' => [
                'comment' =>  'Display the specified resource.',
                'params'  =>  ['int|$id']
            ],
            'create'  => [
                'comment' => 'Show the form for creating a new resource.',
            ], 
            'store' =>  [
                'comment' =>  'Show the form for creating a new resource.',
                // 'params'  =>  ['Request|$request'],
            ], 
            'edit'  =>  [
                'comment' =>  'Show the form for editing the specified resource.',
                'params'  =>  ['int|$id']
            ],
            'update'  => [
                'comment' => 'Update the specified resource in storage.',
                // 'params'  => ['Request|$request', 'int|$id'],
                'params'  => ['int|$id'],
            ],   
            'destroy' =>  [
                'comment' => 'Remove the specified resource from storage.',
                'params'  =>  ['int|$id']
            ]
        ];

        $type = '';
        $model = '';
        foreach($argv as $argument) {    
            if (in_array($argument, ['--resource', '--api'])) {
                $type = $argument;
                
                $index =  array_search($argument, $argv);
                unset($argv[$index]);
            
            }      
      
            if (str_starts_with($argument, '--model')) {
                $chunks = explode('=', $argument, 2);
                if (isset($chunks[1])) {
                    $model = $chunks[1];                    
                } else {
                    echo "--model=<Model>\n";        
                    die();
                }
            }
        }  
    
        // TODO Chech if argument --route=<route> exists
        // if exists and type either --api -r|--resource
        // make routes based on methods
        // if type is not passed (i.g. bare controller),
        // than omit argument [--route]

        // get     posts           PostsController@index      -r | --api
        // get     posts/{id}      PostsController@show       -r | --api
        // get     posts/{id}/edit PostsController@edit       -r 
        // get     posts/new       PostsController@create     -r
        // post    posts           PostsController@store      -r | --api
        // patch   posts/1         PostsController@update     -r | --api
        // delete  posts/1         PostsController@destroy    -r | --api

        $content  = "<?php\n";
        $content .= "\n";
        $content .= "namespace $namespace;\n";  
        $content .= "\n";
        $content .= "use MVC\Framework\Base\Controller;\n";
        $content .= "\n";
        $content .= "class $controller_name extends Controller\n";
        $content .= "{\n";
        $content .= "\n";
          
        if ($type == '--api' || $type == '--resource') {
            if ($type == "--api") {        
                unset($methods['create']);
                unset($methods['edit']);    
            } 
            
            foreach($methods as $method => $info) {    
                if ($info['comment']) {
                    $content .= "    /**\n";      
                    $content .= "     * {$info['comment']}\n";
                    $params = [];
                    if (isset($info['params'])) {
                        $content .= "     *\n";
                        foreach($info['params'] as $param) {
                            $parts = explode('|', $param);
                            $content .= "     * @param\t$parts[0]\t\t{$parts[1]}\n";
                            $params[] = $parts[1];
                        }
                    } else {
                        $content .= "     *\n";
                    }
                    $content .= "     * @return\tHttp\n";
                    $content .= "     */\n";      
                }    
                $content .= "    public function $method(" . join(', ', $params) .")\n";
                $content .= "    {\n";
                $content .= "    }\n";
                $content .= "   \n";
            }
        }
      
        $content .= "}\n";
          
        if ($this->createFile($controller_name, $file_path, $content)) {
            echo "\n";                        
            AP::printLn("Class [B_Green]{$controller_name}[Reset] has been successfully created.");            
            if ($model !== '') {
              (new MakeModel())->handler([$model]);
              echo "\n";    
            }  
        }
    } 
}
