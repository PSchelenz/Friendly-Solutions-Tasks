<?php

namespace Modules\Importer\Http\Controllers;

use App\Modules\Importer\Helpers\CsvExporter;
use App\Modules\Importer\Services\HtmlParser;
use App\Modules\WorkOrder\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Importer\Entities\ImporterLog;
use Modules\Importer\Http\Requests\HtmlFileRequest;

class ImporterController extends Controller
{
    private $htmlParser;

    private $KEYS;

    public function __construct(HtmlParser $htmlParser)
    {
        $this->htmlParser = $htmlParser;

        $this->KEYS = [
            'work_order_number',
            'external_id',
            'priority',
            'received_date',
            'category',
            'fin_loc',
        ];
    }

    public function index()
    {
        $logs = ImporterLog::all();

        return view('importer::index', compact('logs'));
    }

    public function store(HtmlFileRequest $request)
    {
        if($request->hasFile('html_file')) {
            // Get file info
            $file = $request->file('html_file');
            $fileName = public_path($file->getFilename());

            // Extract data
            $this->htmlParser->parseHtml($file);
            $data = $this->htmlParser->getParsedData();

            // Startup data
            $processedEntries = 0;
            $createdEntries = 0;

            // Work order numbers already existing in the DB
            $existingNumbers = WorkOrder::select('work_order_number')->get()->pluck('work_order_number')->toArray();

            //Initialize CSV exporter
            $csvExporter = new CsvExporter($fileName);

            //Iterate over parsed data
            foreach ($data as $index => $order) {
                $processedEntries++;

                $orderData = array_combine($this->KEYS, $order);

                // Check if work order number already exists
                if(!in_array($orderData['work_order_number'], $existingNumbers ?? [])) {
                    $orderData['received_date'] = Carbon::parse($orderData['received_date'])->format('Y-m-d H:i:s');

                    DB::table('work_order')->insert($orderData);

                    $createdEntries++;
                    $order[] = 'created';
                } else {
                    $order[] = 'skipped';
                }

                $csvExporter->loadData($order);
            }

            // Close CSV file
            $csvExporter->end();

            // Create new log
            ImporterLog::create([
                'type' => 'import',
                'run_at' => now(),
                'entries_processed' => $processedEntries,
                'entries_created' => $createdEntries,
            ]);

            return response()->download($fileName, 'work_orders.csv', ['Content-Type' => 'text/csv']);
        }

        return back()->with('error', 'No file was uploaded');
    }
}
