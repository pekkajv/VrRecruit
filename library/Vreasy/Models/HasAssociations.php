<?php

namespace Vreasy\Models;

interface HasAssociations
{
    public function hasMany($field, $class);
    public function hasOne($field, $class);
    public function belongsTo($field, $class);
}
