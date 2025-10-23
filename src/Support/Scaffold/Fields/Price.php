<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Tir\Crud\Support\Enums\FilterType;

class Price extends BaseField
{

    protected string $type = 'Price';
    protected  FilterType | string $filterType = FilterType::Slider;
}
