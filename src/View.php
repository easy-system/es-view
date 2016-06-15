<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View;

use Es\Events\EventsInterface;
use Es\Mvc\ViewInterface;
use Es\Mvc\ViewModelInterface;
use Es\Services\ServicesTrait;

/**
 * The system view.
 */
class View implements ViewInterface
{
    use ServicesTrait;

    /**
     * The "root" view model.
     *
     * @var \Es\Mvc\ViewModelInterface
     */
    protected $layout;

    /**
     * The events.
     *
     * @var \Es\Events\EventsInterface
     */
    protected $events;

    /**
     * Sets the layout.
     *
     * @param \Es\Mvc\ViewModelInterface $layout The layout
     */
    public function setLayout(ViewModelInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Gets the layout.
     *
     * @return \Es\Mvc\ViewModelInterface The layout
     */
    public function getLayout()
    {
        if (! $this->layout) {
            $layout = new ViewModel();
            $layout->setTemplate('layout/layout');
            $this->layout = $layout;
        }

        return $this->layout;
    }

    /**
     * Sets the events.
     *
     * @param \Es\Events\EventsInterface $events The events
     */
    public function setEvents(EventsInterface $events)
    {
        $this->events = $events;
    }

    /**
     * Gets the events.
     *
     * @return \Es\Events\EventsInterface The events
     */
    public function getEvents()
    {
        if (! $this->events) {
            $services = $this->getServices();
            $events   = $services->get('Events');
            $this->setEvents($events);
        }

        return $this->events;
    }

    /**
     * Renders the view model.
     *
     * @param \Es\Mvc\ViewModelInterface $model The view model
     *
     * @return mixed The result of rendering
     */
    public function render(ViewModelInterface $model)
    {
        foreach ($model as $child) {
            $groupId = $child->getGroupId();
            if (empty($groupId)) {
                continue;
            }
            $result    = $this->render($child);
            $oldResult = $model->getVariable($groupId, '');
            $model->setVariable($groupId, $oldResult . $result);
        }

        $events = $this->getEvents();
        $event  = new ViewEvent($model);
        $events->trigger($event);

        return $event->getResult();
    }
}
