<?php

namespace CodeDistortion\ClarityLogger\Pipelines;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerPipelineException;
use CodeDistortion\ClarityLogger\Output\TableOutput;
use CodeDistortion\ClarityLogger\Output\OutputInterface;
use CodeDistortion\ClarityLogger\Output\TextOutput;

/**
 * Collects pipeline output.
 */
class PipelineOutput
{
    /** @var OutputInterface[] The output objects that have been used so far. */
    private array $objects = [];

    /** @var mixed|null The last object that was created. */
    private mixed $last = null;



    /**
     * Instantiate an output object.
     *
     * Will continue to use the last object if the same type is asked for.
     * Will generate a new one if the type being asked for is different to the last.
     *
     * @param class-string $class         The class to instantiate.
     * @param boolean      $forceNew      Force a new one to be created, even if a valid one is available.
     * @param boolean      $reusableAfter Whether this object can be reused later.
     * @return object
     * @throws ClarityLoggerPipelineException When a pipe-output class doesn't implement OutputInterface.
     */
    private function use(string $class, bool $forceNew, bool $reusableAfter): object
    {
        if ((!$forceNew) && ($this->last instanceof $class)) {
            return $this->last;
        }

        $output = new $class();
//        if (!$output instanceof OutputInterface) { // the classes are controlled by this class, so can't be invalid
//            throw ClarityLoggerPipelineException::invalidPipeOutputClass($class);
//        }
        /** @var OutputInterface $output */

        $this->objects[] = $output;
        $this->last = $reusableAfter
            ? $output
            : null; // don't allow this one to be re-used

        return $output;
    }

    /**
     * Retrieve a new TableOutput object to use.
     *
     * @param boolean $reusableAfter Whether this object can be reused later.
     * @return TableOutput
     * @throws ClarityLoggerPipelineException If TableOutput doesn't implement OutputInterface.
     */
    public function newTable(bool $reusableAfter = true): TableOutput
    {
        /** @var TableOutput $return */
        $return = $this->use(TableOutput::class, true, $reusableAfter);

        return $return;
    }

    /**
     * Retrieve a TableOutput object to use, reuse if the previous one was a TableOutput as well.
     *
     * @param boolean $reusableAfter Whether this object can be reused later.
     * @return TableOutput
     * @throws ClarityLoggerPipelineException If TableOutput doesn't implement OutputInterface.
     */
    public function reuseTableOrNew(bool $reusableAfter = true): TableOutput
    {
        /** @var TableOutput $return */
        $return = $this->use(TableOutput::class, false, $reusableAfter);

        return $return;
    }

    /**
     * Generate a new Text object to use.
     *
     * @param boolean $reusableAfter Whether this object can be reused later.
     * @return TextOutput
     * @throws ClarityLoggerPipelineException If Text doesn't implement OutputInterface.
     */
    public function newText(bool $reusableAfter = true): TextOutput
    {
        /** @var TextOutput $return */
        $return = $this->use(TextOutput::class, true, $reusableAfter);

        return $return;
    }

    /**
     * Retrieve a Text object to use, reuse if the previous one was a Text as well.
     *
     * @param boolean $reusableAfter Whether this object can be reused later.
     * @return TextOutput
     * @throws ClarityLoggerPipelineException If Text doesn't implement OutputInterface.
     */
    public function reuseTextOrNew(bool $reusableAfter = true): TextOutput
    {
        /** @var TextOutput $return */
        $return = $this->use(TextOutput::class, false, $reusableAfter);

        return $return;
    }



    /**
     * Take the output parts and piece them together.
     *
     * @return string
     * @throws ClarityLoggerPipelineException When the pipeline gave different types of output (e.g. array, string).
     */
    public function getCombinedOutput(): string
    {
        $output = [];
        $types = [];
        foreach ($this->objects as $outputObject) {

            $tempOutput = $outputObject->render();

            if (is_string($tempOutput)) {
                if (!mb_strlen($tempOutput)) {
                    continue;
                }
            } elseif (is_array($tempOutput)) {
                if (!count($tempOutput)) {
                    continue;
                }
            } else {
                throw ClarityLoggerPipelineException::invalidResponseTypeGiven(gettype($tempOutput));
            }

            $output[] = $tempOutput;
            $types[] = gettype($tempOutput);
        }

        if (!count($output)) {
            return '';
        }

        $types = array_values(array_unique($types));
        if (count($types) > 1) {
            throw ClarityLoggerPipelineException::multipleResponseTypesGiven($types);
        }

        if (gettype($output[0]) == 'string') {

            $stringJoin = PHP_EOL . PHP_EOL;

            /** @var string[] $output */
            return $this->mergeStrings($output, $stringJoin);
        }

//        if (gettype($output[0]) == 'array') {

            /** @var array<mixed[]> $output */
            $return = json_encode($this->mergeArrays($output));
            return $return !== false
                ? $return
                : '';
//        }
//
//        return '';
    }

    /**
     * Merge an array of strings together.
     *
     * @param string[] $strings The strings to merge.
     * @param string   $join    The string to join them with.
     * @return string
     */
    private function mergeStrings(array $strings, string $join = PHP_EOL): string
    {
        return implode($join, $strings);
    }

    /**
     * Merge arrays together.
     *
     * @param array<mixed[]> $arrays The arrays to merge.
     * @return mixed[]
     */
    private function mergeArrays(array $arrays): array
    {
        $return = [];
        foreach ($arrays as $array) {
            $return = array_merge($return, $array);
        }
        return $return;
    }
}
