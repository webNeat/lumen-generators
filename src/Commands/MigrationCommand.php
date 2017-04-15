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

    protected $description = 'Generates a migration file.

    <comment>Examples:</comment>
        - wn:migration categories --schema="name:string.255 active:tinyint.1:default.0 slug:string" --add="timestamps,softDeletes"
        - wn:migration galleries --schema="name:string user_id:unsignedInteger" --add="timestamps,softDeletes" --keys="user_id"
    
    <comment>Available fields:</comment>
    string(string $column, int $length = 255) 
    text(string $column) | mediumText longText 
    boolean(string $column) 
    integer(string $column, bool $autoIncrement = false, bool $unsigned = false) | tinyInteger smallInteger mediumInteger bigInteger  
    unsignedInteger(string $column, bool $autoIncrement = false) | unsignedTinyInteger unsignedSmallInteger unsignedMediumInteger unsignedBigInteger 
    float(string $column, int $total = 8, int $places = 2) 
    decimal(string $column, int $total = 8, int $places = 2) 
    double(string $column, int|null $total = null, int|null $places = null) 
    enum(string $column, array $allowed) 
    json(string $column) 
    jsonb(string $column) 
    date(string $column) 
    dateTime(string $column)   
    binary(string $column) 
    uuid(string $column) 
    ipAddress(string $column) 
    macAddress(string $column)
    ';

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

        return implode(PHP_EOL, $lines);
    }

    protected function getFieldDeclaration($parts)
    {
        $name = $parts[0]['name'];
        $parts[1]['args'] = array_merge(["'{$name}'"], $parts[1]['args']);
        unset($parts[0]);
        $parts = array_map(function($part){
            return '->' . $part['name'] . '(' . implode(', ', $part['args']) . ')';
        }, $parts);
        return "            \$table" . implode('', $parts) . ';';
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
            $key['table'] = str_plural(substr($key['name'], 0, count($key['name']) - 4));
        }

        $constraint = $this->getTemplate('migration/foreign-key')
            ->with([
                'name' => $key['name'],
                'table' => $key['table'],
                'column' => $key['column']
            ])
            ->get();

        if($key['on_delete']){
            $constraint .= PHP_EOL . $this->getTemplate('migration/on-constraint')
                ->with([
                    'event' => 'Delete',
                    'action' => $key['on_delete']
                ])
                ->get();
        }

        if($key['on_update']){
            $constraint .= PHP_EOL . $this->getTemplate('migration/on-constraint')
                ->with([
                    'event' => 'Update',
                    'action' => $key['on_update']
                ])
                ->get();
        }

        return $constraint . ';';
    }

}
