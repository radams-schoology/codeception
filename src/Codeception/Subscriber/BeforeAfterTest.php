<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\TestCase;
use Codeception\Util\Debug;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeAfterTest implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_BEFORE => 'beforeClass',
        Events::SUITE_AFTER  => 'afterClass',
    ];

    protected $hooks = [];
    protected $startedTests = [];
    protected $unsuccessfulTests = [];

    public function beforeClass(SuiteEvent $e)
    {
        foreach ($e->getSuite()->tests() as $test) {
            /** @var $test \PHPUnit_Framework_Test  * */
            $testClass = get_class($test);
            $this->hooks[$testClass] = \PHPUnit_Util_Test::getHookMethods($testClass);
        }
        codecept_debug('beforeClass beforeHooks');
        $this->runHooks('beforeClass');
        codecept_debug('beforeClass afterHooks');
    }


    public function afterClass(SuiteEvent $e)
    {
        $this->runHooks('afterClass');
    }

    protected function runHooks($hookName)
    {
        foreach ($this->hooks as $className => $hook) {
            foreach ($hook[$hookName] as $method) {
                if (is_callable([$className, $method])) {
                    codecept_debug('calling function ' . $method . ' in class ' . $className);
                    call_user_func([$className, $method]);
                }
            }
        }
    }
}
