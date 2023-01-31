<?php

namespace App\Controller;

use App\Entity\Recipe;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use TeamTNT\TNTSearch\TNTSearch;

class MainController extends AbstractController
{
    #[Route('/search/{term}', name: 'app_search')]
    public function searchResults(ManagerRegistry $doctrine, string $term)
    {
        $entityManager = $doctrine->getManager();

        $tnt = new TNTsearch;

        $config = $this->getTNTSearchConfig();

        $tnt->loadConfig($config);
        $tnt->selectIndex('recipes.index');

        $maxResults = 20;
        $results    = $tnt->search($term, $maxResults);
        return new JsonResponse($results);
        $recipeRepo = $entityManager->getRepository(Recipe::class);
        $rows       = [];

        foreach ($results['ids'] as $id) {
            $recipe = $recipeRepo->find($id);

            $rows[] = [
                'id'   => $recipe->getId(),
                'name' => $recipe->getName(),
            ];
        }
        
        return new JsonResponse($rows);
    }

    #[Route('cron/generate/recipes', name: 'cron_gen_recipes')]
    public function generateIndex()
    {
        // Check Auth and Roles
        // ... 

        $tnt = new TNTsearch;

        $config = $this->getTNTSearchConfig();

        $tnt->loadConfig($config);

        $indexer = $tnt->createIndex('recipes.index');

        $indexer->query('SELECT id, name, description FROM recipe;');
        $indexer->run();

        return new Response(
            '<html><body>Index succesfully generated !</body></html>'
        );
    }

    private function getTNTSearchConfig()
    {
        $db       = $_ENV['DATABASE_URL'];
        $dbParams = parse_url($db);


        $config   = [
            'driver'    => $dbParams['scheme'],
            'host'      => $dbParams['host'],
            'database'  => str_replace("/", "", $dbParams['path']),
            'username'  => $dbParams['user'],
            'password'  => $dbParams['pass'],
            'storage'   => 'X:\\Development\\Web development\\Symfony project\\recipe_book_symfony/fuzzy_storage/',
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class
        ];

        return $config;
    }
}