<?php

namespace CodeDistortion\ClarityLogger\Output;

use CodeDistortion\ClarityLogger\Helpers\VarExportHelper;

/**
 * Render data in a two column table format.
 */
class TableOutput implements OutputInterface
{
    /** @var array<integer, array<string, string|mixed>|null> The rows of content, ready for outputting. */
    private array $rows = [];



//    /**
//     * Constructor.
//     *
//     * @param array<string, string|mixed[]|null> $rows Rows to add straight away.
//     */
//    public function __construct(array $rows = [])
//    {
//        foreach ($rows as $title => $content) {
//            $this->row($title, $content);
//        }
//    }
//
//    /**
//     * Alternative constructor.
//     *
//     * @param array<string, string|mixed[]|null> $rows Rows to add straight away.
//     * @return self
//     */
//    public static function new(array $rows = []): self
//    {
//        return new self($rows);
//    }



    /**
     * Add a row of content, ready to output.
     *
     * @param string              $title   The title.
     * @param string|mixed[]|null $content The content.
     * @return $this
     */
    public function row(string $title, string|array|null $content): self
    {
        if (is_null($content)) {
            return $this;
        }
        if ((is_array($content)) && (!count($content))) {
            return $this;
        }
        if ((is_string($content)) && (!mb_strlen($content))) {
            return $this;
        }

        $this->rows[] = ['title' => $title, 'content' => $content];

        return $this;
    }

//    /**
//     * Add rows of content, ready to output.
//     *
//     * @param array<string, array<string|mixed[]|null>>|null $rows An associative array containing the titles and rows
//     *                                                             (or data) to add.
//     * @return $this
//     */
//    public function rows(array|null $rows): self
//    {
//        if (is_null($rows)) {
//            return $this;
//        }
//
//        foreach ($rows as $title => $row) {
//            $this->row($title, $row);
//        }
//
//        return $this;
//    }

    /**
     * Add a blank row of content, ready to output.
     *
     * @return $this
     */
    public function blankRow(): self
    {
        $this->rows[] = null;

        return $this;
    }



    /**
     * Render the content held within this object.
     *
     * @return string
     */
    public function render(): string
    {
        $titleLength = $this->longestTitle() + 2;

        $outputRows = [];
        foreach ($this->rows as $row) {

            if (is_null($row)) {
                $outputRows[] = '';
                continue;
            }

            /** @var string $title */
            $title = $row['title'];

            foreach ($this->breakDownRow($row['content']) as $internalRow) {
                $outputRows[] = str_pad($title, $titleLength) . $internalRow;
                $title = '';
            }
        }

        return implode(PHP_EOL, $outputRows);
    }

    /**
     * Work out what the longest title is.
     *
     * @return integer
     */
    private function longestTitle(): int
    {
        $max = 0;
        foreach ($this->rows as $row) {
            if (is_null($row)) {
                continue;
            }
            /** @var string $title */
            $title = $row['title'];
            $max = max(mb_strlen($title), $max);
        }
        return $max;
    }

    /**
     * Break down a row into parts, ready to piece together when formatting the output.
     *
     * @param mixed $row One row of content.
     * @return string[]
     */
    private function breakDownRow(mixed $row): array
    {
        $rows = is_string($row)
            ? $row
            : VarExportHelper::niceExport($row);
        return explode(PHP_EOL, $rows);
    }
}
