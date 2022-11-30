<?php

class Formatter
{
    public function formatValue($value, $formatter, $option): float|array|string
    {
        return match($formatter) {
            'date' => $this->formatDate($value, $option),
            'cash' => $this->formatCash($value),
            'address' => $this->formatAddress($value),
            'phone' => $this->formatPhoneNumber($value),
            default => '',
        };
    }

    public function formatDate(string $date, string $fromFormat): string
    {
        $dateData = date_parse_from_format($fromFormat, $date);

        return $this->prettyDateTime($dateData);
    }

    private function prettyDateTime(array $dateData): string
    {
        return $dateData['year'] . '-'
            . $this->leadingZero(intval($dateData['month'])) . '-'
            . $this->leadingZero(intval($dateData['day'])) . ' '
            . $this->leadingZero(intval($dateData['hour'])) . ':'
            . $this->leadingZero(intval($dateData['minute']));
    }

    public function leadingZero(int|float $value): float|int|string
    {
        return $value < 10 ? '0' . $value : $value;
    }

    public function formatCash(string $amount): float
    {
        $formattedCash = str_replace(array('$', ','), '', $amount);

        return floatval($formattedCash);
    }

    public function formatAddress(string $address): array
    {
        list($street, $details) = explode(', ', $address);

        list($city, $state, $zip) = explode(' ', $details);

        return array($street, $city, $state, $zip);
    }

    public function formatPhoneNumber(string $phoneNumber): array|string
    {
        return str_replace('-', '', $phoneNumber);
    }
}

class Parser extends Formatter
{
    public function __construct(private readonly string $fileContent, private string|array $parsedValue = '')
    {
    }

    public function parseValueFromHtml(string $id, array|null $options = null)
    {
        $tagContent = $this->findFittingTag($id, $options['tag']);
        $value = $this->getValueFromTag($tagContent);

        if($options['format'] ?? false) {
            $value = $this->formatValue($value, $options['format'], $options['option'] ?? null);
        }

        $this->parsedValue = $value;
    }

    private function findFittingTag(string $id, string $tag)
    {
        preg_match('/<' . $tag . '[^>]+id="' . $id . '".*?<\/' . $tag . '>/', $this->fileContent, $tagContent);

        return $tagContent[0];
    }

    private function getValueFromTag(string $tagContent): string
    {
        return preg_replace('/<\/?.*?>/', '', $tagContent);
    }

    public function getParsedValue(): string|array
    {
        return $this->parsedValue;
    }
}

// Prepare data
$csvHeaders = array('Tracking number', 'PO Number', 'Scheduled Date', 'Customer', 'Trade', 'NTE', 'Store ID', 'Street', 'City', 'State', 'Zip', 'Phone');

$valuesToParse = [
    'wo_number' => ['tag' => 'h3'],
    'po_number' => ['tag' => 'h3'],
    'scheduled_date' => ['tag' => 'h3', 'format' => 'date', 'option' => "F j, Y , G:i A"],
    'customer' => ['tag' => 'h3'],
    'trade' => ['tag' => 'h3'],
    'nte' => ['tag' => 'h3', 'format' => 'cash'],
    'location_name' => ['tag' => 'h3'],
    'location_address' => ['tag' => 'a', 'format' => 'address'],
    'location_phone' => ['tag' => 'a', 'format' => 'phone'],
];

// Prepare file
$htmlFile = file_get_contents('wo_for_parse.html');

$trimmedFile = str_replace(array("\r", "\n", "  ", "<br>"), ['', '', '', ', '], $htmlFile);

$parser = new Parser($trimmedFile);
$csvData = [];

// Find values
foreach ($valuesToParse as $htmlId => $options) {
    $parser->parseValueFromHtml($htmlId, $options);
    $csvItem = $parser->getParsedValue();

    if(is_array($csvItem)) {
        $csvData = array_merge($csvData, $csvItem);
    } else {
        $csvData[] = $csvItem;
    }
}

// Write to CSV
$fp = fopen('data.csv', 'w');

fputcsv($fp, $csvHeaders);
fputcsv($fp, $csvData);

fclose($fp);