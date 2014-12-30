<?php
namespace Vreasy\Models;

use Vreasy\HasAttributes;
use Vreasy\Initializable;
use Vreasy\Models\Collection;
use Vreasy\Models\One;
use Vreasy\Models\Many;
use Vreasy\Models\HasAssociations;
use Valitron\Validator;

class Base implements \Serializable, \JsonSerializable, HasAttributes, HasAssociations
{

    private $_validator;
    private $_rules = [];

    public function __construct() {}

    public function __set($name, $value)
    {
        $objectVars = get_object_vars($this);
        if (array_key_exists($name, $objectVars)) {
            $this->$name = $value;
            return;
        } else {
            $field = '_assoc_'.$name;
            if (array_key_exists($field, $objectVars)) {
                if ($value instanceof Collection) {
                    // Value is an association already
                    $this->$field->exchangeArray($value);
                    return;
                } else {
                    if ($this->$field instanceof Collection) {
                        if ($this->$field->serializeAsProxy) {
                            if (is_string($value)) {
                                $value = @unserialize($value);
                            }
                        }

                        $toBeAssigned = [];
                        if ($value) {
                            if (is_array($value) || $value instanceof \Traversable) {
                                if ($value = array_filter((array)$value)) {
                                    $toBeAssigned = $value;
                                }
                            } elseif ($this->$field instanceof Many) {
                                $toBeAssigned = $this->$field->getCollection();
                                $toBeAssigned[] = $value;
                            } else {
                                $toBeAssigned = [$value];
                            }
                        }
                        $this->$field->exchangeArray($toBeAssigned);
                        return;
                    }
                }
            }
        }
        // When the property is neither defined nor it is an association the it's dynamic property
        return $this->$name = $value;
    }

    public function __get($name)
    {
        $objectVars = get_object_vars($this);
        if (array_key_exists($name, $objectVars)) {
            return $this->$name;
        }

        $field = '_assoc_'.$name;
        if (array_key_exists($field, $objectVars)) {
            return $this->$field;
        }
    }

    public function __isset($name)
    {
        if ($val = $this->$name) {
            return true;
        }

        $field = '_assoc_'.$name;
        if ($val = $this->$field) {
            return true;
        }
        return false;
    }

    public function __unset($name)
    {
        if (isset($this->$name)) {
            unset($this->$name);
        } else {
            $field = '_assoc_'.$name;
            if (isset($this->$field)) {
                unset($this->$field);
            }
        }
    }

    public function serialize()
    {
        return serialize($this->attributesForDb());
    }

    public function unserialize($data)
    {
        $this->__construct();
        static::hydrate($this, unserialize($data));
    }

    public function attributes($options = [])
    {
        $filterOutAssocations = false;
        $filterOutSerialzed = false;
        $filterOutAllAssociations = false;
        $filterInAllAssociations = false;
        $serialize = false;
        extract($options, EXTR_IF_EXISTS);

        if ($filterOutAllAssociations) {
            $filterOutSerialzed = $filterOutAssocations = true;
        }

        $ref = new \ReflectionClass(get_class($this));
        $properties = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);

        return array_reduce($properties, function($v, $w) use($filterOutAssocations, $filterOutSerialzed, $filterInAllAssociations, $serialize) {
            $name = $w->getName();
            $value = $this->$name;

            if ($value instanceof Collection && $value->serializeAsProxy) {
                if ($value instanceof One) {
                    $v[$name] = $value->getAssociation();
                } elseif ($value instanceof Many) {
                    $v[$name] = $value->getCollection();
                }
                if ($serialize) {
                    $v[$name] = $value->isPresent() ? serialize($v[$name]) : null;
                }
            } else {
                $v[$name] = (ctype_digit($value) ? (int)$value : $value);
            }

            // Filter out all the assocations so they don get "saved"
            if ($filterOutAssocations) {
                if ($value instanceof Collection && !$value->serializeAsProxy) {
                    unset($v[$name]);
                }
            }

            if ($filterOutSerialzed) {
                if ($value instanceof Collection && $value->serializeAsProxy) {
                    unset($v[$name]);
                }
            }

            if ($filterInAllAssociations) {
                if (!$value instanceof Collection) {
                    unset($v[$name]);
                }
            }

            return $v;
        }, []);
    }

    public function attributesForDb()
    {
        return $this->attributes(['filterOutAssocations' => true, 'serialize' => true]);
    }

    protected function validator($reset = false)
    {
        if($this->_validator && !$reset) {
            return $this->_validator;
        }
        else {
            return $this->_validator = $this->_initValidator();
        }
    }

    protected function _initValidator()
    {
        return new Validator($this->attributes());
    }

    public function validates($rule, $fields, $params = null)
    {
        $this->_rules[] = [$rule, $fields, $params];
    }

    public function isValid()
    {
        $validator = $this->validator(true);
        foreach ($this->_rules as $value) {
            // Unfold and suppress errors because $params could not be there
            @list($rule, $attr, $params) = $value;
            $validator->rule($rule, $attr, $params);
        }
        return $validator->validate();
    }

    public function errors()
    {
        return $this->validator() ? $this->validator()->errors() : [];
    }

    /**
     * Delegates methods to Zend_db for easy db layer access
     */
    public static function __callStatic($name, $arguments)
    {
        // TODO: Stop coupling the models against the DB
        return call_user_func_array([\Zend_Registry::get('Zend_Db'), $name], $arguments);
    }

    public static function instanceWith($params, $classType = null)
    {
        if ($classType) {
            $object = (new \ReflectionClass($classType))->newInstance();
        } else {
            $object = new static();
            $classType = get_class($object);
        }

        if (method_exists($classType, 'hydrate')) {
            $object = call_user_func([$classType, 'hydrate'], $object, $params);
        } else {
            $object = static::hydrate($object, $params);
        }

        if ($object instanceof Initializable) {
            $object->initialize();
        }
        return $object;
    }

    public static function hydrate($instance, $params)
    {
        $params = (array)$params;
        foreach ($params as $k => $v) {
            // A collection where the value is not serialized
            if ($instance->$k instanceof Collection && !is_string($v) &&
                !$v instanceof $instance->$k->classType && $v
            ) {
                if ($instance->$k instanceof One) {
                    $instance->$k->buildAssociation($v);
                } elseif ($instance->$k instanceof Many) {
                    $instance->$k->buildCollection($v);
                }
            } else {
                // Attempts to parse integer values
                $instance->$k = ctype_digit($v) ? (int)$v : $v;
            }
        }
        return $instance;
    }

    public function isNew()
    {
        return !$this->id;
    }

    public function jsonSerialize()
    {
        return $this->attributes();
    }

    public static function attributeNames()
    {
        $ref = new \ReflectionClass(get_called_class());
        $properties = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);
        $v = [];
        return array_reduce($properties, function($v, $w) {
            $v[] = $w->getName();
            return $v;
        }, []);
    }

    public function __toString()
    {
        // Some array methods need to convert the values to strings for comparition
        return spl_object_hash($this);
    }

    public function serializeOne($field, $classOrInstance)
    {
        $assoc = $this->hasOne($field, $classOrInstance);
        $assoc->serializeAsProxy = true;
        return $assoc;
    }

    public function serializeMany($field, $class)
    {
        $assoc = $this->hasMany($field, $class);
        $assoc->serializeAsProxy = true;
        return $assoc;
    }


    public function hasMany($property, $class)
    {
        // Unsetting the propery will force to call the magic methods afterwards
        // so we can hook in and do our stuff
        unset($this->$property);
        $field = '_assoc_'.$property;
        $this->$field = new Many($property, "$class");
        return $this->$field;
    }

    public function hasOne($property, $classOrInstance)
    {
        // Unsetting the propery will force to call the magic methods afterwards
        // so we can hook in and do our stuff
        unset($this->$property);
        $field = '_assoc_'.$property;
        if (is_object($classOrInstance)) {
            $classType = get_class($classOrInstance);
            $this->$field = new One($property, $classType, $classOrInstance);
        } else {
            // An empty One association
            $one = new One($property, $classOrInstance);
            $this->$field = $one;
        }
        return $this->$field;
    }

    public function belongsTo($field, $class)
    {
        // FIXME: As of now it is the same as the hasOne,
        // but logically it isn't the same. The difference is in which side of the
        // association  the "foreign key" is.
        // Explanation: http://guides.rubyonrails.org/association_basics.html
        return $this->hasOne($field, $class);
    }

    public static function orderById($a, $b)
    {
        return ((int) $a->id) - ((int) $b->id);
    }

    public static function compareObject($a, $b)
    {
        if ($a->isNew() && $b->isNew()) {
            return strcasecmp((string)$a, (string)$b);
        }
        elseif ($a->id == $b->id) {
            return 0;
        }
        elseif($a->id > $b->id) {
            return 1;
        }
        else {
            return -1;
        }
    }

    public function __clone()
    {
        $this->id = null;
        $ref = new \ReflectionClass(get_class($this));
        $properties = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);
        $clones = array_reduce(
            $properties,
            function($v, $w) {
                $name = $w->getName();
                $value = $this->$name;
                if ($value instanceof Collection) {
                    $v[$name] = clone $value;
                }
                return $v;
            },
            []
        );

        foreach ($clones as $name => $v) {
            if ($v instanceof Many) {
                if ($v->serializeAsProxy) {
                    $this->serializeMany($v->field, $v->classType);
                } else {
                    $this->hasMany($v->field, $v->classType);
                }
            } elseif ($v instanceof One) {
                if ($v->serializeAsProxy) {
                    $this->serializeOne($v->field, $v->classType);
                } else {
                    $this->hasOne($v->field, $v->classType);
                }
            }

            $this->$name = $v;
        }
    }
}
