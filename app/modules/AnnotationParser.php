<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Modules;

/**
 * Description of AnotationParser
 *
 * @author Zvekete
 */
class AnnotationParser
{

    /** @var array */
    private static $parsedMethodAnnotations = [];

    /** @var array */
    private static $parsedClassAnnotations = [];

    /**
     * 
     * @param string $classMethod
     * @param string $annotation
     * @return array
     */
    public function getMethodAnnotation($classMethod, $annotation = null)
    {
        if ($annotation && isset(self::$parsedMethodAnnotations[$classMethod][$annotation])) {
            return self::$parsedMethodAnnotations[$classMethod][$annotation];
        } else if (false == $annotation && isset(self::$parsedMethodAnnotations[$classMethod])) {
            return self::$parsedMethodAnnotations[$classMethod];
        }

        $reflectionMethod = new \ReflectionMethod($classMethod);

        return $this->genericParse($classMethod, $reflectionMethod->getDocComment(), self::$parsedMethodAnnotations);
    }

    /**
     * 
     * @param string $class
     * @param string $annotation
     * @return array
     */
    public function getClassAnnotation($class, $annotation = null)
    {
        if ($annotation && isset(self::$parsedClassAnnotations[$class][$annotation])) {
            return self::$parsedClassAnnotations[$class][$annotation];
        } else if (false == $annotation && isset(self::$parsedClassAnnotations[$class])) {
            return self::$parsedClassAnnotations[$class];
        }

        $reflectionClass = new \ReflectionClass($class);

        return $this->genericParse($class, $reflectionClass->getDocComment(), self::$parsedClassAnnotations);
    }
    /**
     * @param string $class
     * @param $docComment
     * @param string $array
     * @param string|null $annotation
     * @return array
     */
    private function genericParse($class, $docComment, &$array, $annotation = null)
    {
        preg_match_all('#@(.*?)\n#s', $docComment, $annotations);

        $this->parseAnnotations($class, $annotations[1], $array);

        if ($annotation) {
            return empty($array[$class][$annotation]) ? [] : $array[$class][$annotation];
        }

        return empty($array[$class]) ? [] : $array[$class];
    }

    /**
     * 
     * @param string $classMethod
     * @param array $annotations
     */
    private function parseAnnotations($classMethod, array $annotations, &$array)
    {

        foreach ($annotations as $annotation) {
            $annotationArray = explode(' ', $annotation);

            $annotationName = array_shift($annotationArray);
            $annotationValue = trim(implode(' ', $annotationArray));

            if (empty($array[$classMethod][$annotationName])) {
                $array[$classMethod][$annotationName] = [$annotationValue];
            } else {
                $array[$classMethod][$annotationName][] = $annotationValue;
            }
        }
    }

}
