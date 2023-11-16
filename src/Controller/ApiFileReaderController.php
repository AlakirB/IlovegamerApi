<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiFileReaderController extends AbstractController
{
    public function index(Request $request): Response
    {
        $fileName = $request->get('file');
        $fileFormat = $request->get('format');

        // this is not working : $filePath = sprintf("%s/var/data/%s.%s", [$this->getParameter('kernel.project_dir'), $fileName, $fileFormat]);
        $filePath = $this->getParameter('kernel.project_dir')."/var/data/$fileName.$fileFormat";

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found.');
        }

        $fileContent = file_get_contents($filePath);

        return $this->render('api_file_reader/index.html.twig', [
            'file_content' => $fileContent,
        ]);
    }
}
