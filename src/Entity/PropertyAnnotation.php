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

        $countRow = \count($explodeDoc);
        for ($i = 0; $i < $countRow; $i++) {

            $explodeDoc[$i] = trim($explodeDoc[$i]);

            if ($i === 0) { // first doc line : doc start with /*
                $explodeDoc[$i] = ltrim($explodeDoc[$i], '/');
            }

            if ($i === $countRow - 1) { // last doc line : doc end with */
                $explodeDoc[$i] = rtrim($explodeDoc[$i], '/');
            }

            $explodeDoc[$i] = trim($explodeDoc[$i], "* \t\n\r\0\x0B"); // basic trim + *

            if (strpos($explodeDoc[$i], '@') === 0) {
                $this->_annotations[] = $explodeDoc[$i];
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