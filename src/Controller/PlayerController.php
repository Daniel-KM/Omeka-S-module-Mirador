<?php declare(strict_types=1);

namespace Mirador\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class PlayerController extends AbstractActionController
{
    /**
     * Forward to the 'play' action
     *
     * @see self::playAction()
     */
    public function indexAction()
    {
        $params = $this->params()->fromRoute();
        $params['action'] = 'play';
        return $this->forward()->dispatch(__CLASS__, $params);
    }

    public function playAction()
    {
        // The exception is thrown automatically.
        $id = $this->params('id');
        $resource = $this->api()->read('resources', $id)->getContent();
        $view = new ViewModel([
            'resource' => $resource,
        ]);
        return $view
            ->setTerminal(true);
    }
}
