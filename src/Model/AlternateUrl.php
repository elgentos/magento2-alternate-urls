<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Model;

final class AlternateUrl
{
    private string $hreflang;

    private string $url;

    public function __construct(
        string $hreflang,
        string $url
    ) {
        $this->hreflang = $hreflang;
        $this->url      = $url;
    }

    public function getHreflang(): string
    {
        return $this->hreflang;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
