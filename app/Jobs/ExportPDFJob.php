<?php

namespace App\Jobs;

use App\Http\Controllers\ProtocolController;
use App\Models\Organisations;
use App\Models\Protocol;
use PHPMailer\PHPMailer\PHPMailer;

class ExportPDFJob extends Job
{

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;


    /**
     * ID of the Protocol
     *
     * @var int
     */
    public $protocolID = 1;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($protocolID)
    {
        $this->protocolID = $protocolID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ProtocolController $protocolController, \Aws\S3\S3Client $s3)
    {
        //Check if file exist in s3
        if (!$s3->doesBucketExist(getenv("S3_Bucket"))) {
            $s3->createBucket(["Bucket"=>getenv("S3_Bucket")]);
        }


        $protocol = Protocol::query()->where("id", "=", $this->protocolID)->first();
        $resCategorie = $protocolController->getProtocolDetails($protocol->id);
        $organisation = Organisations::query()->where("id", "=", $protocol->organisation_id)->first();

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $phpWord->addTitleStyle(1, array('size' => 18, 'bold' => true));
        $phpWord->addTitleStyle(2, array('size' => 14, 'bold' => true));
        $phpWord->addTitleStyle(3, array('size' => 10, 'bold' => true));

        $section = $phpWord->addSection();
        $section->addTitle("Protokoll ".$organisation->name." vom ".$protocol->start, 1);


        foreach ($resCategorie as $cat) {
            $section->addTitle($cat->name, 2);
            foreach ($cat->items as $item) {
                $section->addTitle($item->name, 3);
                $section->addText($item->protocol->description);
            }
        }

        $tmpfname = tempnam("/tmp", "PRO");
        unlink($tmpfname);

        $domPdfPath = realpath(__DIR__."/../../vendor/dompdf/dompdf");
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, "PDF");
        $objWriter->save($tmpfname);

        $insert = $s3->putObject([
            'Bucket' => getenv("S3_Bucket"),
            'Key'    => $protocol->id.".pdf",
            'Body'   => file_get_contents($tmpfname)
        ]);

        unlink($tmpfname);
    }
}
