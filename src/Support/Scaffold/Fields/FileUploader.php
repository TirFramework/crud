<?php

namespace Tir\Crud\Support\Scaffold\Fields;


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

    public function maxCount(int $value): static
    {
        $this->maxCount = $value;
        return $this;
    }
}
