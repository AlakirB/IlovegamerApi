<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiDbDataStoreController extends AbstractController
{
    // convert the data from our database to an array
    static public function convertDbToArray(VideoGameRepository $videoGameRepository) : array
    {
        return array_map(
            function (VideoGame $videoGame)
            {
                return [
                    'id' => $videoGame->getId(),
                    'name' => $videoGame->getName()
                ];
            },
            $videoGameRepository->findAll()
        );
    }

    #[Route('/db-reader/games', name: 'app_api_db_reader_games')]
    public function games(VideoGameRepository $videoGameRepository): Response
    {
        return new JsonResponse($this->convertDbToArray($videoGameRepository));
    }
}
