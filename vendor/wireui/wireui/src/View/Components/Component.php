<?php

namespace WireUi\View\Components;

use Closure;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View;
use Illuminate\View\ComponentAttributeBag;
use WireUi\Support\Html;

abstract class Component extends View\Component
{
    protected const DEFAULT = 'default';

    public function resolveView(): Closure|ViewContract
    {
        $view = $this->render();

        if ($view instanceof ViewContract) {
            return $view;
        }

        $resolver = fn (ViewContract $view) => new Html($view->render());

        return fn (array $data = []) => $resolver($view($data));
    }

    protected function classes(array $classList): string
    {
        $classes = [];

        foreach ($classList as $class => $constraint) {
            if (is_numeric($class)) {
                $classes[] = $constraint;
            } elseif ($constraint) {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Will find the correct modifier, like sizes, xs, sm given as a component attribute
     * This function will return "default" if no matches are found
     * e.g. The sizes modifiers are: $sizes ['xs' => '...', ...]
     *      <x-badge xs ... /> will return "xs"
     *      <x-badge ... /> will return "default"
     */
    private function findModifier(ComponentAttributeBag $attributes, array $modifiers): string
    {
        $keys      = collect($modifiers)->keys()->except(self::DEFAULT)->toArray();
        $modifiers = $attributes->only($keys)->getAttributes();
        $modifier  = collect($modifiers)->filter()->keys()->first();

        // store the modifier to remove from attributes bag
        if ($modifier && !in_array($modifier, $this->smartAttributes)) {
            $this->smartAttributes[] = $modifier;
        }

        return $modifier ?? self::DEFAULT;
    }

    /** Finds the correct modifier css classes on attributes */
    public function modifierClasses(ComponentAttributeBag $attributes, array $modifiers): string
    {
        $modifier = $this->findModifier($attributes, $modifiers);

        return $modifiers[$modifier];
    }
}
