<?php

namespace App\Controller;

use App\Repository\VideoGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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


    // function for merging our datas array into 1 without duplicating (using db 'name' and csv 'Game Title')
    public function mergeDbAndCsvDatas(array $dbData, array $csvData) : array
    {
        // get the list of names from our csv file
        $listNamesCsv = array_map(
            function (array $game)
            {
                return $game['Game Title'];
            },
            $csvData
        );

        $dbDataFiltered = array_filter($dbData, function ($game) use ($listNamesCsv) {
            // Remove games from our dbData where 'name' is already in our csv file
            return !in_array($game['name'], $listNamesCsv);
        });

        // Combine arrays
        return array_merge($dbDataFiltered, $csvData);
    }

    // merge the data in a way that is more precise than the old version (keep the db 'id' for all and is not case-sensitive)
    public function mergeDbAndCsvDatasV2(array $dbData, array $csvData) : array
    {
        $mergedArray = [];

        // write our mergedArray with at least every games from our csv
        foreach($csvData as $csvGame)
        {
            $duplicate = false;

            // search for matching names in our database for every game of our csv
            foreach($dbData as $dbGame)
            {
                // if name is same in both db
                if (strcasecmp($csvGame['Game Title'], $dbGame['name']) === 0)
                {
                    // add to the csv game data the id from our database (only value that can be added from our db game data)
                    $mergedArray[] = array_merge($csvGame, ['id' => $dbGame['id']]);

                    // leave the foreach if we do have a duplicate
                    $duplicate = true;
                    break;
                }
            }

            // if we did not find any duplicate, just add the data from our csv
            if (!$duplicate) 
            {
                $mergedArray[] = $csvGame;
            }
        }

        // dd($mergedArray);

        // add to our mergedArray all the games only in our database
        foreach ($dbData as $dbGame) {
            $duplicate = false;
        
            foreach ($mergedArray as $mergedItem) {
                // if the game is in mergedArray and in our database, indicate he was already in merged array and leave early
                if (strcasecmp($dbGame['name'], $mergedItem['Game Title']) === 0) 
                {
                    $duplicate = true;
                    break;
                }
            }
            
            // if the game is not already in mergedArray,add the game from our database
            if (!$duplicate) 
            {
                // added while changing the key of name into 'Game Title' to keep same key as csv in our final array
                $mergedArray[] = [
                    'id' => $dbGame['id'],
                    'Game Title' => $dbGame['name']
                ];
            }
        }
        
        return $mergedArray;
    }

    /**
     * @deprecated
     */
    #[Route('/all/{file}', name: 'app_api_get_all')]
    public function getAll(Request $request, 
        VideoGameRepository $videoGameRepository, 
        ApiDbDataStoreController $apiDbDataStoreController, 
        ApiCsvDataStoreController $apiCsvDataStoreController
    ) : Response
    {
        // get the parameter from url
        $file = $request->get('file');

        $dbData = $apiDbDataStoreController->convertDbToArray($videoGameRepository);
        $csvData = $apiCsvDataStoreController->convertCsvToArray($file);

        return new JsonResponse($this->mergeDbAndCsvDatas($dbData, $csvData));
    }

    #[Route('/v2/all/{file}', name: 'app_api_v2_get_all')]
    public function getAllV2(Request $request, 
        VideoGameRepository $videoGameRepository, 
        ApiDbDataStoreController $apiDbDataStoreController, 
        ApiCsvDataStoreController $apiCsvDataStoreController
    ) : Response
    {
        // get the parameter from url
        $file = $request->get('file');

        // if using deprecated file, redirect to old version
        if($file == 'games.csv')
        {
            return new RedirectResponse($this->generateUrl('app_api_get_all',['file' => $file]), RedirectResponse::HTTP_MOVED_PERMANENTLY);
        }

        $dbData = $apiDbDataStoreController->convertDbToArray($videoGameRepository);
        $csvData = $apiCsvDataStoreController->convertCsvToArray($file);

        return new JsonResponse($this->mergeDbAndCsvDatasV2($dbData, $csvData));
    }
}
