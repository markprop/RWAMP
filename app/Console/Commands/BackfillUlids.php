<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillUlids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ulid:backfill {--model=* : Limit to specific model classes (e.g. App\\Models\\User)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing ULIDs for models that use the HasUlid trait';

    /**
     * Known models that participate in routed URLs.
     *
     * @var array<int, class-string>
     */
    protected array $knownModels = [
        \App\Models\User::class,
        \App\Models\CryptoPayment::class,
        \App\Models\WithdrawRequest::class,
        \App\Models\ResellerApplication::class,
        \App\Models\Transaction::class,
        // Optional / future models can be added here (Page, Post, Doc, Project, News, etc.)
    ];

    public function handle(): int
    {
        $models = $this->option('model');

        if (! empty($models)) {
            $targets = array_intersect($this->knownModels, $models);
        } else {
            $targets = $this->knownModels;
        }

        if (empty($targets)) {
            $this->warn('No matching models to backfill.');
            return self::SUCCESS;
        }

        foreach ($targets as $class) {
            if (! class_exists($class)) {
                $this->warn("Model {$class} does not exist, skipping.");
                continue;
            }

            $this->backfillForModel($class);
        }

        $this->info('ULID backfill complete.');

        return self::SUCCESS;
    }

    /**
     * Backfill ULIDs for a single model class.
     *
     * @param  class-string  $class
     */
    protected function backfillForModel(string $class): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $class();

        if (! $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'ulid')) {
            $this->warn("Table {$model->getTable()} has no ulid column, skipping {$class}.");
            return;
        }

        $this->info("Backfilling ULIDs for {$class} ({$model->getTable()})...");

        $total = $class::whereNull('ulid')->count();

        if ($total === 0) {
            $this->line('  All records already have ULIDs.');
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $class::whereNull('ulid')
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($bar) {
                foreach ($chunk as $record) {
                    $record->ulid = (string) Str::ulid();
                    $record->saveQuietly();
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
    }
}


