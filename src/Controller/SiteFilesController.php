<?php
namespace App\Controller;

use App\Lib\CakeboxUtility;
use Cake\Network\Exception\NotFoundException;

class SiteFilesController extends AppController
{

    /**
     * Index
     *
     * @return void
     */
    public function index()
    {
        $this->set('data', [
            'directories' => [
                'sites-available' => '/etc/nginx/sites-available',
                'sites-enabled' => '/etc/nginx/sites-enabled'
            ],
            'sitefiles' => $this->cbi->getRichNginxFiles(),
        ]);
    }

    /**
     * Serve nginx site configuration file as html
     *
     * @param string $filename Name of the nginx site file (without path).
     * @return void
     */
    public function file($filename)
    {
        $content = CakeboxUtility::getFileContent("/etc/nginx/sites-available/$filename");
        if (!$content) {
            throw new NotFoundException();
        }
        $this->set([
            'content' => $content,
            '_serialize' => ['content']
        ]);
    }
}
