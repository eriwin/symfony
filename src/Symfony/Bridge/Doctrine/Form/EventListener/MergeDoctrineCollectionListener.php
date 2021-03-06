<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\Form\EventListener;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Merge changes from the request to a Doctrine\Common\Collections\Collection instance.
 *
 * This works with ORM, MongoDB and CouchDB instances of the collection interface.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @see Collection
 */
class MergeDoctrineCollectionListener implements EventSubscriberInterface
{
    // Keeps BC. To be removed in 4.0
    private $bc = true;

    public static function getSubscribedEvents()
    {
        // Higher priority than core MergeCollectionListener so that this one
        // is called before
        return array(
            FormEvents::SUBMIT => array(
                // BC
                array('onBind', 10),
                array('onSubmit', 5),
            ),
        );
    }

    public function onSubmit(FormEvent $event)
    {
        // If onBind() is overridden then logic has been executed
        if ($this->bc) {
            @trigger_error('The onBind() method is deprecated since version 3.1 and will be removed in 4.0. Use the onSubmit() method instead.', E_USER_DEPRECATED);

            return;
        }

        $collection = $event->getForm()->getData();
        $data = $event->getData();

        // If all items were removed, call clear which has a higher
        // performance on persistent collections
        if ($collection instanceof Collection && count($data) === 0) {
            $collection->clear();
        }
    }

    /**
     * Alias of {@link onSubmit()}.
     *
     * @deprecated since version 3.1, to be removed in 4.0.
     *             Use {@link onSubmit()} instead.
     */
    public function onBind()
    {
        if (__CLASS__ === get_class($this)) {
            $this->bc = false;
        }
    }
}
