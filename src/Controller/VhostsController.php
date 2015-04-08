<?php
namespace App\Controller;

use App\Error\Exception\RestException;
use App\Form\SiteFileForm;
use App\Lib\CakeboxExecute;
use App\Lib\CakeboxUtility;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Network\Exception\NotFoundException;

class VhostsController extends AppController
{

    /**
     * BeforeFilter
     *
     * @param \Cake\Event\Event $event Event instance.
     * @return void
     * @throws Cake\Network\Exception\NotFoundException
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Security->config('unlockedActions', ['ajaxDelete']);

        if ($this->request->action == 'ajaxDelete') {
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
        'sitefiles' => $this->Info->getRichVhosts(),
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
     * Delete an Nginx virtual host configuration file using ajax
     * @return void
     * @throws App\Error\Exception\RestException
     */
    public function ajaxDelete()
    {
        if (!$this->request->is('post')) {
            throw new NotFoundException();
        }

        if (empty($this->request->data['id'])) {
            throw new RestException('Parameter id is required', null, 401);
        }
        $id = $this->request->data['id'];

        $execute = new CakeboxExecute();
        if ($execute->removeVhost($id) == false) {
            throw new RestException("Error deleting website $id. See cakebox.log for details.", null, 401);
        }

        $this->set([
        'message' => "Website $id deleted successfully.",
        '_serialize' => ['message']
        ]);
    }
}
