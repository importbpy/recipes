<?php

declare(strict_types = 1);

namespace App\Model\Twig;

use Parsedown;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TwigExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('markup', [$this, 'markupToHtml'])
        ];
    }

    public function markupToHtml(string $markup): string {
        $parsedown = new Parsedown();
        return $parsedown->text($markup);
    }

}