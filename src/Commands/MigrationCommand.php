<?php namespace Wn\Generators\Commands;


class MigrationCommand extends BaseCommand {

	protected $signature = 'wn:migration
        {table : The table name.}
        {--schema= : the schema.}
        {--add= : specifies additional columns like timestamps, softDeletes, rememberToken and nullableTimestamps.}
        {--keys= : foreign keys.}
        {--file= : name of the migration file (to use only for testing purpose).}
        {--parsed : tells the command that arguments have been already parsed. To use when calling the command from an other command and passing the parsed arguments and options}
        {--force= : override the existing files}
    ';
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
                'schema' => $this->getSchema(),
                'additionals' => $this->getAdditionals(),
                'constraints' => $this->getConstraints()
            ])
            ->get();

        $file = $this->option('file');
        if(! $file){
            $file = date('Y_m_d_His_') . snake_case($name) . '_table';
        }

        $this->save($content, "./database/migrations/{$file}.php", "{$table} migration");
    }

    protected function getSchema()
    {
        $schema = $this->option('schema');
        if(! $schema){
            return $this->spaces(12) . "// Schema declaration";
        }

        $items = $schema;
        if( ! $this->option('parsed')){
            $items = $this->getArgumentParser('schema')->parse($schema);
        }

        $fields = [];
        foreach ($items as $item) {
            $fields[] = $this->getFieldDeclaration($item);
        }

        return implode(PHP_EOL, $fields);
    }

    protected function getAdditionals()
    {
        $additionals = $this->option('add');
        if (empty($additionals)) {
            return '';
        }

        $additionals = explode(',', $additionals);
        $lines = [];
        foreach ($additionals as $add) {
            $add = trim($add);
            $lines[] = $this->spaces(12) . "\$table->{$add}();";
        }

        if(!empty($lines))
            return implode(PHP_EOL, $lines);
        else
            return "";
    }

    protected function getFieldDeclaration($parts)
    {
        $name = $parts[0]['name'];
        $parts[1]['args'] = array_merge(["'{$name}'"], $parts[1]['args']);
        unset($parts[0]);
        $parts = array_map(function($part){
            return '->' . $part['name'] . '(' . implode(', ', $part['args']) . ')';
        }, $parts);
        return $this->spaces(12) . "\$table" . implode('', $parts) . ';';
    }

    protected function getConstraints()
    {
        $keys = $this->option('keys');
        if(! $keys){
            return $this->spaces(12) . "// Constraints declaration";
        }

        $items = $keys;
        if(! $this->option('parsed')){
            $items = $this->getArgumentParser('foreign-keys')->parse($keys);
        }

        $constraints = [];
        foreach ($items as $item) {
            $constraints[] = $this->getConstraintDeclaration($item);
        }

        return implode(PHP_EOL, $constraints);
    }

    protected function getConstraintDeclaration($key)
    {
        if(! $key['column']){
            $key['column'] = 'id';
        }
        if(! $key['table']){
            $key['table'] = str_plural(substr($key['name'], 0, strlen($key['name']) - 3));
        }

        // Indent the constraint properly
        $constraint = $this->spaces(12);

        $constraint .= $this->getTemplate('migration/foreign-key')
            ->with([
                'name' => $key['name'],
                'table' => $key['table'],
                'column' => $key['column']
            ])
            ->get();

        if($key['on_delete']){
            $constraint .= $this->getTemplate('migration/on-constraint')
                ->with([
                    'event' => 'Delete',
                    'action' => $key['on_delete']
                ])
                ->get();
        }

        if($key['on_update']){
            $constraint .= $this->getTemplate('migration/on-constraint')
                ->with([
                    'event' => 'Update',
                    'action' => $key['on_update']
                ])
                ->get();
        }

        return $constraint . ';';
    }

}
