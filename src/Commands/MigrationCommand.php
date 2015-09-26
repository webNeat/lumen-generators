<?php namespace Wn\Generators\Commands;


class MigrationCommand extends BaseCommand {

	protected $signature = 'wn:migration
        {table : The table name.}
        {--schema= : the schema.}';
        // {action : One of create, add, remove or drop options.}
        // The action is only create for the moment

	protected $description = 'Generates a migration to create a table with schema';

    public function handle()
    {
        $table = $this->argument('table');
        $name = 'Create' . ucwords(camel_case($table));

        $content = $this->getTemplate('migration')
            ->with([
                'table' => $table,
                'name' => $name,
                'schema' => $this->getSchema()
            ])
            ->get();

        $name = snake_case($name);
        $this->save($content, "./database/migrations/{$name}.php");

        $this->info("{$table} migration generated !");
    }

    protected function getSchema()
    {
        $schema = $this->option('schema');
        if(! $schema){
            return "\t\t\t// Schema declaration";
        }

        $items = $this->getArgumentParser('schema')->parse($schema);

        $fields = [];
        foreach ($items as $item) {
            $fields[] = $this->getFieldDeclaration($item);
        }

        return implode(PHP_EOL, $fields);
    }

    protected function getFieldDeclaration($parts)
    {
        $name = $parts[0]['name'];
        $parts[1]['args'] = array_merge(["'{$name}'"], $parts[1]['args']);
        unset($parts[0]);
        $parts = array_map(function($part){
            return '->' . $part['name'] . '(' . implode(', ', $part['args']) . ')';
        }, $parts);
        return "\t\t\t\$table" . implode('', $parts) . ';';
    }
    
}