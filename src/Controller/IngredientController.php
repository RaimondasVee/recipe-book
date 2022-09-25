<?php

namespace App\Controller;

use App\Entity\Ingredient;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\ProductRepository;

class IngredientController extends AbstractController
{
    #[Route('/ingredient/create', name: 'create_ingredient')]
    public function createProduct(ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();

        $ingredient = new Ingredient();
        $ingredient->setName('ingredient 1');
        $ingredient->setUnit(2);
        $ingredient->setQty(1);

        $errors = $validator->validate($ingredient);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $entityManager->persist($ingredient);
        $entityManager->flush();

        return new Response('Saved new ingredient with id '.$ingredient->getId());
    }

    #[Route('/ingredient/list/{id}', name: 'app_ingredient')]
    public function index(ManagerRegistry $doctrine, int $id): Response
    {
        
        $ingredient = $doctrine->getRepository(Ingredient::class)->findAllDetails($id);

        if (!$ingredient) {
            throw $this->createNotFoundException(
                'No ingredients found with ingredient id: '.$id
            );
        }

        return new Response(json_encode($ingredient));
    }
}
