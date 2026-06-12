<?php

namespace Wixiweb\WixiwebLaravel\Logging;

use Illuminate\Container\Container;
use Illuminate\Contracts\Log\ContextLogProcessor;
use Illuminate\Log\Context\Repository as ContextRepository;
use Monolog\LogRecord;
use Wixiweb\WixiwebLaravel\Wixiweb;

/**
 * Remplace le ContextLogProcessor par défaut de Laravel : injecte le contexte global
 * dans le "extra" du LogRecord, mais en appliquant au préalable le pipeline de filtres
 * (redaction dot-notation + filtres custom). Le contexte explicite du log ($record->context)
 * est également filtré.
 */
class ContextFilterProcessor implements ContextLogProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $filters = Wixiweb::buildFilterPipeline();

        $applyFilters = fn (array $data): array => array_reduce(
            $filters,
            fn (array $ctx, ContextFilterInterface $filter): array => $filter->filter($ctx),
            $data,
        );

        $app = Container::getInstance();
        $contextData = $app->bound(ContextRepository::class)
            ? $app->get(ContextRepository::class)->all()
            : [];

        return $record->with(
            extra: [...$record->extra, ...$applyFilters($contextData)],
            context: $applyFilters($record->context),
        );
    }
}
