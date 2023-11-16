<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ArrayExtension extends AbstractExtension
{
    public function getFilters() : array
    {
        return [
            new TwigFilter('values', 'array_values'),
        ];
    }
}