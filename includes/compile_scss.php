<?php
/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

require_once "scss/scss.inc.php";
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
use ScssPhp\ScssPhp\Compiler;

try {

    $compiled = get_stylesheet_directory().'/style.css';
    // Check if style.css exists
    if (file_exists($compiled)) {
        $startcompile = false;
        $lastmodified = filemtime($compiled);
        
        // if the compiled file exists - Get the last modified date
        // Now Loop through the SCSS asset Directory recursively and look if any monified date
        // is greater than that one.
        $di = new RecursiveDirectoryIterator(get_stylesheet_directory().'/assets/scss/');
        foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
            // echo $filename . ' - ' . $file->getSize() . ' bytes <br/>';
            // echo filemtime($filename)."<br/>";
            if (filemtime($filename) > $lastmodified) {
                $startcompile = true;
                break;
            }
        }
    } else {
        $startcompile = false;
    }
    
    if ($startcompile) {

        $compiler = new Compiler();

        $compiler->setImportPaths(get_stylesheet_directory().'/assets/scss/');

        $compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
        $compiler->setSourceMapOptions([
            // relative or full url to the above .map file
            'sourceMapURL' => './style.map',

            // (optional) relative or full url to the .css file
            'sourceMapFilename' => 'style.css',

            // partial path (server root) removed (normalized) to create a relative url
            'sourceMapBasepath' => get_stylesheet_directory(),

            // (optional) prepended to 'source' field entries for relocating source files
            'sourceRoot' => '/',
        ]);

        $compiler->setOutputStyle('compressed');

        $result = $compiler->compileString('@import "style.scss";');
        
        file_put_contents(get_stylesheet_directory().'/style.map', $result->getSourceMap());
        file_put_contents(get_stylesheet_directory().'/style.css', $result->getCss());
    }

} catch (\Exception $e) {
    echo $e;
    syslog(LOG_ERR, 'scssphp: Unable to compile content');
}
