<?php

namespace App\Controller;

use App\Repository\VideoGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/home', name: 'app_api_home')]
    public function home(): Response
    {
        return new JsonResponse(['hello' => 'world']);
    }

    #[Route('/games', name: 'app_api_games')]
    public function games(VideoGameRepository $videoGameRepository): Response
    {
        $games = $videoGameRepository->findAll();

        $data = [];
        foreach($games as $game)
        {
            array_push($data, [
                'id' => $game->getId(),
                'name' => $game->getName()
            ]);
        }
        return new JsonResponse($data);
    }
}
