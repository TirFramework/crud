<?php

namespace Tir\Crud\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Field extends Component
{
    /**
     * The alert type.
     *
     * @var object
     */
    public $field;

    /**
     * The alert type.
     *
     * @var object
     */
    public $item;

    /**
     * The alert type.
     *
     * @var object
     */
    public $message;


    /**
     * Create the component instance.
     *
     * @param string $type
     * @return void
     */
    public function __construct($field, $item, $message)
    {
        $this->field = $field;

        $this->item = $item;

        $this->message = $message;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render()
    {
        return view('core::scaffold.components.field');
    }
}