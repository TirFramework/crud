<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Tir\Crud\Support\Enums\FilterType;

class Price extends BaseField
{
    protected string $type = 'Price';
    protected string $currency = '$';
    protected  FilterType | string $filterType = FilterType::Slider;

    public function currency($currency): Price
    {

        $this->currency = $currency;
        return $this;
    }
}
