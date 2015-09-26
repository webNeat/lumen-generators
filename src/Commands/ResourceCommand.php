<?php
/*
wn:resource
		{name : Name of the resource.}
        {--fields= : fields description.}
        	name
        	schema
        	attrs: fillable, date
        	rules
        {--has-many= : hasMany relationships.}
        {--has-one= : hasOne relationships.}
        {--belongs-to= : belongsTo relationships.}

wn:migration
	table => str_plural(name)
	--schema => 
wn:route
	resource => name

wn:controller
    model => (namespace from --model-path) ucwords(camel_case(name))
	--no-routes => true

wn:model
    name => ucwords(camel_case(name))
    --fillable => having_fillable_attr(fields)
    --dates => having_date_attr(fields)
    --rules => rules_of(fields)
    --path => --model-path
    --has-many => --has-many
    --has-one => --has-one
    --belongs-to => --belongs-to
*/
