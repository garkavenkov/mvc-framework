<?php

namespace MVC\Framework\Console\Commands;

use AdvancedPrint\AdvancedPrint as AP;

abstract class ConsoleCommand
{ 
    /**
     * Command usage
     *
     * @var string
     */
    protected $command = "Command";

    /**
     * Command description
     *
     * @var string
     */
    protected $description = "Command description";

    /**
     * Command arguments description if there are any
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * Command usage example(s)
     *
     * @var array
     */
    protected $examples = array();
    
    /**
     * Footnotes description, if there are any
     *
     * @var array
     */
    protected $footnotes = array();


    /**
     * Command handler
     *
     * @param array $argv Command arguments
     * @return void
     */
    abstract public function handler(array $argv);
    
    
    /**
     * Output command help
     *
     * @return void
     */ 
    public function help()
    {        
        AP::printLn("\n[B_White]{$this->description}\n");
        AP::printLn("[Cyan]Використовуйте:");
        AP::printLn("    " . $this->command . "\n");
        
        if (isset($this->arguments) && (count($this->arguments) > 0)) {
            $longest_option = max(array_map('mb_strlen', array_keys($this->arguments)))+4;            
            AP::printLn("[Cyan]Параметри:");
            foreach($this->arguments as $option => $description) {                                
                AP::printf("    [Green]{$option}[Reset]", $longest_option, 0, ' ');
                AP::printLn(" - " . $description);
            }
            echo "\n";
        }
        
        if (isset($this->examples) && (count($this->examples) > 0)) {                
            AP::printLn("[Cyan]Наприклад:");            
            $longest_option = max(array_map('mb_strlen', array_keys($this->examples)));            
            foreach($this->examples as $example => $description) {
                AP::printLN("    [Green]$example");
                AP::printLn("        " . $description ."\n");
            }
            echo "\n";
        }
        if (isset($this->footnotes) && (count($this->footnotes) > 0)) {                
            AP::printLn("[Cyan]Примітка:");            
            $longest_option = max(array_map('mb_strlen', array_keys($this->footnotes)))+4;
            
            foreach($this->footnotes as $footnote => $description) {                
                AP::printLn("    [Yellow]$footnote");
                if (is_array($description)) {                    
                    foreach($description as $line) {                        
                        AP::printLn("        " . $line);                        
                    }
                } else {
                    AP::printLn("        " . $description);
                }
            }
            echo "\n";
        }
    }
    
    /**
     * Parse $argv and determine name parameter
     *
     * @param array $argv       Arguments
     * @param string $message   Error message
     * @return string           Name
     */
    protected function getName(&$argv, string $message = ''): string
    {
        if (count($argv) == 0) {
            echo "\n";
            $message = ($message == '') ? 'Enter name' : $message;
            AP::printLn("[B_Red]Error:[Reset] $message");
            echo "\n";
            die();
        }
        return array_shift($argv);
    }

    /**
     * Resolve class namespace
     *
     * @param string $name      Class name
     * @param string $prefix    Namespace prefix
     * @return string           Resolved class namespace
     */
    protected function resolveNamespace(string $name, string $prefix = ''): string
    {        
        $name = rtrim($name, '/');
        $namespace = explode('/', $name);
        array_pop($namespace);
        $namespace = join('\\', $namespace);
        if ($prefix !== '') {
            $namespace = $prefix . '\\' . $namespace;
        }
        return rtrim($namespace, '\\');
    }

    /**
     * Resolve class path
     *
     * @param string $name          Class name
     * @param string $path_prefix   Path prefix
     * @return string               Resolved file path
     */
    protected function resolveFilePath(string $name, string $path_prefix = ''): string
    {
        $file_path = explode('/', $name);        
        $name =  ucfirst(array_pop($file_path));      
        $file_path = join(DIRECTORY_SEPARATOR, $file_path);                
        if ($path_prefix !== '') {
            $path_prefix = str_replace('\\', '/', $path_prefix);
            $file_path = rtrim($path_prefix, '/') . '/' . $file_path;
        }
        return $file_path;
    }

    /**
     * Create file and fill it with content
     *
     * @param string $name      File name
     * @param string $path      File path
     * @param string $content   File content     
     * @return void
     */
    protected function createFile(string $name, string $path, string $content)
    {   
        $file_path =  ROOT_DIR . '/' . $path;
        
        if (!file_exists($file_path)) {    
            if (!mkdir($file_path, 0777, true)) {
                echo "\n";
                AP::printLn("[B_Red]Error:[Reset]Cannot create directory " . $file_path. " Check your rights on this folder.");
                echo "\n";
                die();
            }
        }

        $file_name = $file_path . DIRECTORY_SEPARATOR . $name . '.php';
        // echo "File name in createFile: $file_name\n";die();
        if (file_exists($file_name)) {    
            echo "\n";
            AP::printLn("[B_Red]Error:[Reset] Class with name '[B_Yellow]{$name}[Reset]' is already exists.");
            echo "\n";
            die();    
        }

        $fh = fopen($file_name, 'w+');
        if (!$fh) {
            echo "\n";
            AP::printLn("[B_Red]Error:[Reset]Cannot create class. Check rights on catalog.");
            echo "\n";
            die();
        }

        if (fwrite($fh, $content) === FALSE) {
            echo "Не могу произвести запись в файл ($file_name)\n";
            fclose($fh);
            return false;
        }

        fclose($fh);  
        return true;
    }
}