<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Illuminate\Support\Arr;

class FileUploader extends BaseField
{

    protected string $type = 'FileUploader';
    protected string $postUrl;
    protected int $maxCount = 1;

    public function uploadUrl($url): static
    {
        $this->postUrl = $url;
        return $this;
    }

    protected function setValue($model): void
    {
//        if(isset($model)){
//            $this->value = [
//                 'file'=>Arr::get($model, $this->name),
//                'baseUrl' =>'https://monarch-crm.s3.eu-central-1.amazonaws.com'
//               ];
//        }

        if(isset($model)){
            $this->value = Arr::get($model, $this->name);
        }
    }

    public function maxCount(int $value): static
    {
        $this->maxCount = $value;
        return $this;
    }
}
