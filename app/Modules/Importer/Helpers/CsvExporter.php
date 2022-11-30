<?php

namespace App\Modules\Importer\Helpers;

class CsvExporter
{
    /**
     * Name of file to be created
     *
     * @var string
     */
    private $filename;

    /**
     * New file resource
     *
     * @var false|resource
     */
    private $fp;

    /**
     * Headers of the CSV file
     *
     * @var string[]
     */
    private $headers;

    public function __construct(string $filename)
    {
        $this->filename = $filename;

        $this->headers = ['Ticket', 'EntityID', 'Urgency', 'Rcvd Date', 'Category', 'Store Name', 'Status'];

        $this->fp = fopen($this->filename, 'w');

        $this->loadData($this->headers);
    }

    public function loadData($data)
    {
        fputcsv($this->fp, $data);
    }

    public function end()
    {
        fclose($this->fp);
    }
}