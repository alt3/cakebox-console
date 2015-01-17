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
        $this->Security->config('unlockedActions', ['ajax_add', 'ajax_delete']);

        if ($this->request->action == 'ajax_delete') {
            $this->eventManager()->detach($this->Csrf);
        }
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

		if (!$this->request->data['force']) {
			if (file_exists('/etc/nginx/sites-available/' . $this->request->data['url'])) {
				throw new RestException('Website already exists. Use force to overwrite.');
			}
		}

        $execute = new CakeboxExecute();
        if ($execute->addSite($this->request->data['url'], $this->request->data['webroot'], true) == false) {
            throw new RestException('Error creating website. See cakebox.log for details.', null , 401);
        }

        $this->set([
           'message' => 'Website created successfully',
           'url' => $this->request->data['url'],
           'webroot' => $this->request->data['webroot'],
           '_serialize' => ['message', 'url', 'webroot']
        ]);
    }

    /**
     * Add nginx file using ajax
     */
    public function ajax_delete()
    {
        if (!$this->request->is('post')) {
            throw new NotFoundException();
        }

        if (empty($this->request->data['id'])) {
            throw new RestException('Parameter id is required', null, 401);
        }
        $id = $this->request->data['id'];

        $execute = new CakeboxExecute();
        if ($execute->removeSite($id) == false) {
            throw new RestException("Error deleting website $id. See cakebox.log for details.", null, 401);
        }

        $this->set([
            'message' => "Website $id deleted successfully.",
            '_serialize' => ['message']
        ]);
    }
}
