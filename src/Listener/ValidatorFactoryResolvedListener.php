<?php

declare(strict_types=1);

namespace Verdient\Dora\Listener;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Verdient\Dora\Annotation\Validator;
use Verdient\Dora\Validation\Validator as ValidationValidator;

/**
 * @Listener
 * @author Verdient。
 */
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class
        ];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function process(object $event)
    {
        $validatorFactory = $event->validatorFactory;
        $validatorFactory->resolver(function (...$args) {
            return new ValidationValidator(...$args);
        });
        foreach (AnnotationCollector::getClassesByAnnotation(Validator::class) as $class => $annotation) {
            $class::register($validatorFactory);
        }
    }
}
