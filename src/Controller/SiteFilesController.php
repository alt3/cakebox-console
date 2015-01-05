<?php
namespace App\Controller;

use App\Error\Exception\RestException;
use App\Error\Exception\RestValidationException;
use App\Form\SiteFileForm;
use App\Lib\CakeboxUtility;
use App\Lib\CakeboxExecute;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Network\Exception\NotFoundException;

class SiteFilesController extends AppController
{

    /**
     *
     */
     public function beforeFilter(Event $event)
     {
        parent::beforeFilter($event);
        $this->Security->config('unlockedActions', ['ajax_add']);
    }

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

    /**
     * Add nginx file using ajax
     */
    public function ajax_add()
    {
        if (!$this->request->is('post')) {
            throw new NotFoundException();
        }

        $form = new SiteFileForm();
        if (!$form->validate($this->request->data)) {
            throw new RestValidationException($form->errors());
        }

        if (is_dir($this->request->data['webroot'])) {
            throw new RestException('Directory already exists');
        }

        // Shell new Execute object
        $execute = new CakeboxExecute();
        if ($execute->mkVagrantDir($this->request->data['webroot']) == false) {
            throw new RestException('Error creating directory', $execute->errors(), 401);
        }

        // still here, things went well
        $this->set([
           'message' => 'Directory created successfully',
           'url' => $this->request->data['url'],
           'webroot' => $this->request->data['webroot'],
           '_serialize' => ['message', 'url', 'webroot']
        ]);
    }
}
