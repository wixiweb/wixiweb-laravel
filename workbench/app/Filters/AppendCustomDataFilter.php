<?php

namespace App\Filters;

use Wixiweb\WixiwebLaravel\Logging\ContextFilterInterface;

class AppendCustomDataFilter implements ContextFilterInterface
{
    public function filter(array $context): array
    {
        $context['CUSTOM'] = 'injected';

        return $context;
    }
}
