<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

#[Route('/api')]
class ApiCsvDataStoreController extends AbstractController
{
    // convert the data from our csv to an array
    public function convertCsvToArray(string $csvFileName) : array
    {
        // Replace 'var/data/your_csv_file.csv' with the actual path to your CSV file
        $csvFilePath = $this->getParameter('kernel.project_dir') . '/var/data/'.$csvFileName;

        // Check if the file exists
        if (!file_exists($csvFilePath)) {
            throw $this->createNotFoundException('CSV file not found.');
        }

        // Read the CSV file content
        $csvContent = file_get_contents($csvFilePath);

        // Decode the CSV content
        return (new CsvEncoder())->decode($csvContent, 'csv');
    }

    #[Route('/csv-reader/{file}', name: 'app_api_csv_reader')]
    public function csvReader(Request $request) : Response
    {
        $file = $request->get('file');

        return new JsonResponse($this->convertCsvToArray($file));
    }
}
