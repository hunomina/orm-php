<?php

namespace hunomina\Orm\Entity;

/**
 * Class PropertyAnnotation
 * @package hunomina\Entity
 */
class PropertyAnnotation
{
    /** @var array $_annotations */
    private $_annotations = [];

    /**
     * PropertyAnnotation constructor.
     * @param \ReflectionProperty $property
     */
    public function __construct(\ReflectionProperty $property)
    {
        if ($doc = $property->getDocComment()) {
            $this->setAnnotations($doc);
        }
    }

    /**
     * @param string $doc
     * Use the property documentation to extract the annotations (rows starting by @)
     */
    public function setAnnotations(string $doc): void
    {
        $explodeDoc = explode("\n", $doc);

        foreach ($explodeDoc as &$row) {
            $row = trim($row);
            $row = ltrim($row, '/*');
            $row = rtrim($row, '*/');
            $row = trim($row, '*');
            $row = trim($row);

            if (strpos($row, '@') === 0) {
                $this->_annotations[] = $row;
            }
        }
        unset($row);
    }


    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->_annotations;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function getAnnotation(string $name): ?string
    {
        foreach ($this->_annotations as $annotation) {
            if (strpos($annotation, '@' . $name) === 0) {
                return $annotation;
            }
        }

        return null;
    }
}