<?php

namespace Wixiweb\WixiwebLaravel\Logging;

use Illuminate\Support\Arr;

/**
 * Masque les champs sensibles du contexte en utilisant la dot-notation :
 * chaque entrée de $hiddenFields est un chemin exact (ex: "HTTP.POST.password"),
 * et seule la valeur à cette position précise est masquée si elle est truthy.
 */
readonly class RedactSensitiveFields implements ContextFilterInterface
{
    /**
     * @param  array<int, string>  $hiddenFields  Chemins dot-notation à masquer.
     */
    public function __construct(private array $hiddenFields)
    {
    }

    public function filter(array $context): array
    {
        foreach ($this->hiddenFields as $path) {
            if (Arr::get($context, $path)) {
                Arr::set($context, $path, '***');
            }
        }

        return $context;
    }
}
