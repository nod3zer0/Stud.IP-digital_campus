<?php

namespace Studip\OAuth2\Models;

class Scope
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $description;

    /**
     * @param string $id
     * @param string $description
     *
     * @return void
     */
    public function __construct($id, $description)
    {
        $this->id = $id;
        $this->description = $description;
    }

    /**
     * @return static[]
     */
    public static function scopes()
    {
        return [
            'api' => new Scope('api', _('Gewährt vollständigen Lese-/Schreibzugriff auf die API.')),
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
        ];
    }

    /**
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
