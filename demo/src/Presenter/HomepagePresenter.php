<?php

namespace Netpromotion\Profiler\Demo\Presenter;

use Netpromotion\Profiler\Profiler;
use Nette\Application\UI\Presenter;

class HomepagePresenter extends Presenter
{
    public function startup()
    {
        Profiler::start("HomepagePresenter::startup() begin");
        parent::startup();
        Profiler::finish("HomepagePresenter::startup() end");
    }
}
