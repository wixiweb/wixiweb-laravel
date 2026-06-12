<?php

namespace Wixiweb\WixiwebLaravel\Logging;

interface ContextFilterInterface
{
    /**
     * Reçoit le contexte (potentiellement imbriqué) et retourne sa version filtrée.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function filter(array $context): array;
}
