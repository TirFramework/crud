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
     * @param $field
     * @param $item
     * @param $message
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
     * @return View
     */
    public function render()
    {
        return view('core::scaffold.components.field');
    }
}