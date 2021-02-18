<?php

namespace Tir\Crud\Support\Scaffold\Inputs;

use Tir\Setting\Entities\Setting;

class Select extends BaseInput
{
    use GetSelfTrait;
    protected static string $type = 'blank';

}