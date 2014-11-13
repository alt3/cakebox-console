<?php
namespace App\Shell;
use Cake\Core\App;
use Cake\Console\Shell;
# use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Core\Configure;

/**
 * SiteShell class is used to generate, enable and load website configuration files.
 *
 * Not using File class right now due to error "Call to undefined method App\Shell\SiteShell::clearStatCache()
 * in /cakebox/commands/vendor/cakephp/cakephp/src/Filesystem/File.php on line 403.
 *
 */
class SiteShell extends Shell {

    /*
     * SiteShell uses these tasks
     */
    public $tasks = [
        'Symlink',
        'Exec',
        'Template'
    ];

    /*
     * var @array containing webserver specific settings
     */
    public $webservers = [
        'nginx' => [
            'sites_available' => '/etc/nginx/sites-available',
            'sites_enabled' => '/etc/nginx/sites-enabled'
            ]
        ];

    /**
     * Define `cakebox site` subcommands and their options `create` and `listall`.
     *
     * @param void
     * @return void
     */
    public function getOptionParser() {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('create', [
            'parser' => [
                'description' => [
                    __("Generates, enables and loads an Nginx site configuration file.")
                ],
                'arguments' => [
                    'url' => ['help' => __('Fully qualified domain name used to expose the site.'), 'required' => true],
                    'webroot' => ['help' => __('Full path to the directory serving the web pages.'), 'required' => true]
                ],
                'options' => [
                    'force' => ['short' => 'f', 'help' => __('Overwrite existing configuration file.'), 'boolean' => true]
                ]
            ]]);
        $parser->addSubcommand('listall', [
            'parser' => [
                'description' => [
                    __("Lists all available nginx site configuration files.")
                ]
            ]]);

        return $parser;
    }


    public function main() {

    }

    /**
     * create() generates, enables and loads a site configuration file.
     *
     * @param string $url containing fqdn used to expose the site
     * @param string $webroot containing full path to site's webroot directory
     * @return boolean false on success, true when errors are encountered
     */
     public function create($url, $webroot){
        $this->out("Creating site configuration file:");
        $file = $this->webservers['nginx']['sites_available'] . "/" . $url;

        # Generate site configuration file
        if (file_exists($file)){
            if ($this->params['force'] == false){
                $this->out("* Skipping: $file already exists. Use --force to overwrite.");
                return;
            }
            $this->out("* Overwriting existing file");
        }

        # Set viewVars for the template
        $this->Template->set ([
           'url' => $url,
           'webroot' => $webroot
        ]);

        # Write generated template to file
        $contents = $this->Template->generate('config', 'vhost_nginx');
        $this->createFile($file, $contents);

        # Enable site by creating symlink in sites-enabled
        $this->out("Enabling site");
        $symlink = $this->webservers['nginx']['sites_enabled'] . "/" . $url;
        $this->Symlink->create($file, $symlink);

         # Reload webserver to effectuate changes
         $this->out("Reloading webserver");
         $this->Exec->run("service nginx reload");
     }

    /**
     * listall
     */
    public function listall() {
        $this->out('Enabled nginx sites are highlighted:');
        $dir = new Folder($this->webservers['nginx']['sites_available']);
        $files = $dir->find('.*', 'sort');
        foreach ($files as $file) {
            if ($this->Symlink->exists($this->webservers['nginx']['sites_enabled'] . "/$file")){
                $this->out("  <info>$file</info>");
            }else{
                $this->out("  $file");
            }
        }
    }

}
