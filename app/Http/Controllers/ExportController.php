<?php

namespace App\Http\Controllers;

use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Jobs\ExportDocxJob;
use App\Jobs\ExportOdtJob;
use App\Jobs\ExportPDFJob;
use App\Models\Organisations;
use App\Models\Protocol;
use App\Models\UserOrganisations;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class ExportController extends BaseController
{
    public function exportPDF($organisation_id, $protocol_id, \Aws\S3\S3Client $s3, Response $response)
    {
        Log::debug("Export PDF");
        $organisation = Organisations::getById($organisation_id);

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->orderBy("id", "DESCp")
                ->first();

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        //Check if file exist in s3
        if (!$s3->doesBucketExist(getenv("S3_Bucket"))) {
            Log::info("Create Bucket");
            $s3->createBucket(["Bucket"=>getenv("S3_Bucket")]);
        }

        $protocol = Protocol::query()->where("id", "=", $protocol_id)->first();
        Log::debug("Protocol: ".$protocol->id);
        $objectExistCheck = $s3->doesObjectExist(getenv("S3_Bucket"), (string)$protocol->id.".pdf");
        Log::debug("Object Exists: ".(int)$objectExistCheck);
        if (!$objectExistCheck) {
            Log::warning("Protocol not found, regenerate");
            #throw new HTTPException("Protokol wird noch generiert, probiere es später nochmal", 400);
            $job = new ExportPDFJob($protocol->id);
            $job->onConnection("sync");
            dispatch($job);
        }

        Log::debug("Export Document");

        $command = $s3->getCommand('GetObject', [
            'Bucket' => getenv("S3_Bucket"),
            'Key'    => $protocol->id.".pdf",
        ]);

        $presignedRequest = $s3->createPresignedRequest($command, '+10 minutes');
        $presignedUrl =  (string)  $presignedRequest->getUri();

        Log::debug("URL: ".$presignedUrl);

        return $response->withData(["url"=>$presignedUrl]);
    }

    public function exportDOCX($organisation_id, $protocol_id, \Aws\S3\S3Client $s3, Response $response)
    {
        Log::debug("Export docx");
        $organisation = Organisations::getById($organisation_id);

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->orderBy("id", "DESCp")
                ->first();

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        //Check if file exist in s3
        if (!$s3->doesBucketExist(getenv("S3_Bucket"))) {
            Log::info("Create Bucket");
            $s3->createBucket(["Bucket"=>getenv("S3_Bucket")]);
        }

        $protocol = Protocol::query()->where("id", "=", $protocol_id)->first();
        Log::debug("Protocol: ".$protocol->id);
        $objectExistCheck = $s3->doesObjectExist(getenv("S3_Bucket"), (string)$protocol->id.".docx");
        Log::debug("Object Exists: ".(int)$objectExistCheck);
        if (!$objectExistCheck) {
            Log::warning("Protocol not found, regenerate");
            #throw new HTTPException("Protokol wird noch generiert, probiere es später nochmal", 400);
            $job = new ExportDocxJob($protocol->id);
            $job->onConnection("sync");
            dispatch($job);
        }

        Log::debug("Export Document");

        $command = $s3->getCommand('GetObject', [
            'Bucket' => getenv("S3_Bucket"),
            'Key'    => $protocol->id.".docx",
        ]);

        $presignedRequest = $s3->createPresignedRequest($command, '+10 minutes');
        $presignedUrl =  (string)  $presignedRequest->getUri();

        Log::debug("URL: ".$presignedUrl);

        return $response->withData(["url"=>$presignedUrl]);
    }

    public function exportODT($organisation_id, $protocol_id, \Aws\S3\S3Client $s3, Response $response)
    {
        Log::debug("Export ODT");
        $organisation = Organisations::getById($organisation_id);

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->orderBy("id", "DESCp")
                ->first();

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        //Check if file exist in s3
        if (!$s3->doesBucketExist(getenv("S3_Bucket"))) {
            Log::info("Create Bucket");
            $s3->createBucket(["Bucket"=>getenv("S3_Bucket")]);
        }

        $protocol = Protocol::query()->where("id", "=", $protocol_id)->first();
        Log::debug("Protocol: ".$protocol->id);
        $objectExistCheck = $s3->doesObjectExist(getenv("S3_Bucket"), (string)$protocol->id.".odt");
        Log::debug("Object Exists: ".(int)$objectExistCheck);
        if (!$objectExistCheck) {
            Log::warning("Protocol not found, regenerate");
            #throw new HTTPException("Protokol wird noch generiert, probiere es später nochmal", 400);
            $job = new ExportOdtJob($protocol->id);
            $job->onConnection("sync");
            dispatch($job);
        }

        Log::debug("Export Document");

        $command = $s3->getCommand('GetObject', [
            'Bucket' => getenv("S3_Bucket"),
            'Key'    => $protocol->id.".odt",
        ]);

        $presignedRequest = $s3->createPresignedRequest($command, '+10 minutes');
        $presignedUrl =  (string)  $presignedRequest->getUri();

        Log::debug("URL: ".$presignedUrl);

        return $response->withData(["url"=>$presignedUrl]);
    }
}
