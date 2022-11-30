<?php

namespace App\Modules\Importer\Services;

use Illuminate\Support\Facades\Log;

class HtmlParser
{
    /**
     * String containing the HTML file content
     *
     * @var string
     */
    private $htmlContent;

    /**
     * Array containing regex expressions that will help find the data we need
     *
     * @var string[]
     */
    private $ruleset;

    /**
     * Data parsed from html file
     *
     * @var array
     */
    private $parsedData;

    /**
     * Indexes of data in file for big tables
     *
     * @var int[]
     */
    private $dataIndexesBig;

    /**
     * Indexes of data in file for small tables
     *
     * @var array
     */
    private $dataIndexesSmall;


    public function __construct()
    {
        $this->htmlContent = '';

        $this->ruleset = [
            '/<tr class="(rgRow|rgAltRow)".*?<\/tr>/',
            '/<td.*?<\/td>/'
        ];

        $this->dataIndexesBig = [0, 3, 4, 8, 10];
        $this->dataIndexesSmall = [0, '-', 1, 5, 7];

        $this->parsedData = [];
    }

    /**
     * Parse data from a html file
     *
     * @param $file
     * @return void
     */
    public function parseHtml($file)
    {
        $this->loadHtml($file);
        $this->trimHtml();
        $this->executeRules();
    }

    /**
     * Load the HTML file content
     *
     * @param $file
     * @return void
     */
    private function loadHtml($file)
    {
        $this->htmlContent = $file->get();
    }

    /**
     * Remove unnecessary characters from the file content to simplify further parsing
     *
     * @return void
     */
    private function trimHtml()
    {
        $this->htmlContent = str_replace(array("\r", "\n", "  ", "<br>"), ['', '', '', ', '], $this->htmlContent);
    }

    /**
     * Execute parsing ruleset
     *
     * @return void
     */
    private function executeRules()
    {
        $startingData = $this->executeRule($this->htmlContent, $this->ruleset[0]);

        foreach ($startingData as $content) {
            $data = $this->executeRule($content, $this->ruleset[1]);

            $this->parseSpecificRows($data);
        }
    }

    /**
     * Execute a single rule
     *
     * @param string $content
     * @param string $rule
     * @return array
     */
    private function executeRule(string $content, string $rule): array
    {
        preg_match_all($rule, $content, $subContent);

        return $subContent[0];
    }

    /**
     * Get the data from html table rows by given indexes
     *
     * @return void
     */
    private function parseSpecificRows($rows)
    {
        $workOrderData = [];

        $indexes = count($rows) <= 10 ? $this->dataIndexesSmall : $this->dataIndexesBig;

        // To keep the CSV order
        $workOrderData[] = $this->extractRowValue($rows[$indexes[0]]);
        $workOrderData[] = $this->extractEntityId($rows[$indexes[0]]);

        foreach (array_slice($indexes, 1) as $chosenRow) {
            $workOrderData[] = $chosenRow !== '-' ? $this->extractRowValue($rows[$chosenRow]) : '';
        }

        $this->parsedData[] = $workOrderData;
    }

    /**
     * Extract value from a table row
     *
     * @param string $row
     * @return string
     */
    private function extractRowValue(string $row): string
    {
        return preg_replace('/<.*?>/', '', $row);
    }

    /**
     * Extract entity id attribute from a given string
     *
     * @param string $row
     * @return string
     */
    private function extractEntityId(string $row): string
    {
        preg_match('/entityid=[a-zA-Z0-9]+"/', $row, $dirtyEntity);

        return str_replace('"', '', explode('=', $dirtyEntity[0])[1]);
    }

    /**
     * Get parsed data
     *
     * @return array
     */
    public function getParsedData(): array
    {
        return $this->parsedData;
    }
}