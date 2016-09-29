<?php

namespace Netpromotion\Profiler\Demo\Nette\Presenter;

use Nette\Application\UI\Presenter;

class HomepagePresenter extends Presenter
{
    /**
     * @inheritdoc
     */
    public function startup()
    {
        doSomething();
        return parent::startup();
    }
}
