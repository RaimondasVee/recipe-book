<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function index(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $personal = $request->get('personal');
        $ingredient = $doctrine->getRepository(Ingredient::class)->findAllDetails($id);

        if (!$ingredient) {
            throw $this->createNotFoundException(
                'No ingredients found with ingredient id: '.$id
            );
        }

        return new Response(json_encode($ingredient));
    }

    #[Route('/ingredient/delete/{recipeId}/{ingredientId}', name: 'app_ingredient_delete')]
    public function delete(ManagerRegistry $doctrine, int $recipeId, int $ingredientId) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        var_dump($owner);
        return false;
        $ingredient = $doctrine->getRepository(Ingredient::class)->find($ingredientId);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($ingredient);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
    }

    private function checkRecipeOwner($id, ManagerRegistry $doctrine): mixed
    {
        $user   = $this->getUser();
        if ($user === null) {
            return false;
        }

        $author = $doctrine->getRepository(Recipe::class)->find($id)->getAuthor();

        if ($author == $user->getId()) {
            return true;
        }

        return false;
    }
}
