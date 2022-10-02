<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\IngredientUnit;
use App\Entity\Steps;
use App\Entity\Recommendations;
use App\Form\RecipeType;
use App\Form\IngredientType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipe', name: 'app_recipes')]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    #[Route('/recipe/show/{id}', name: 'app_recipe')]
    public function show(Request $request, ValidatorInterface $validator, ManagerRegistry $doctrine, int $id): Response
    {
        // Database Objects
        $recipe             = $doctrine->getRepository(Recipe::class)->find($id);
        $ingredient         = $doctrine->getRepository(Ingredient::class);
        $steps              = $doctrine->getRepository(Steps::class);
        $recommendations    = $doctrine->getRepository(Recommendations::class);
        $unit               = $doctrine->getRepository(IngredientUnit::class);

        if (!$recipe) {
            throw $this->createNotFoundException(
                'No recipes found with recipe id: '.$id
            );
        }

        $recipeData = $recipe->getAll();
        $ingredientData = [];
        $stepsData = [];
        $recommendationsData = [];

        // Do Author
        $recipeData['author'] = 'dev';

        // Build Ingredients
        if ($recipeData['ingredients'] != "") {
            $recipeIngredients = explode(',', $recipeData['ingredients']);
    
            foreach ($recipeIngredients as $key => $value) {
                // Fetch Ingredient Unit by Ingredient ID
                $units = $unit->find($ingredient->find($value)->getId());

                $ingredientData[$ingredient->find($value)->getName()] = [
                    'id' =>  $ingredient->find($value)->getId(),
                    'qty' =>  $ingredient->find($value)->getQty(),
                    'unitName' => $units->getUnitName(),
                    'unitAbb'  => $units->getUnitAbbreviation()
                ];
            }
        }

        // Build Steps
        if ($recipeData['steps'] != "") {
            $recipeSteps = explode(',', $recipeData['steps']);
    
            foreach ($recipeSteps as $key => $value) {
                $stepsData[$key] = [
                    'id'   => $steps->find($value)->getId(),
                    'text' => $steps->find($value)->getText(),
                ];
                
                // 
                if ($steps->find($value)->getRecommendations() != "") {
                    $stepsRec = explode(',', $steps->find($value)->getRecommendations());

                    foreach ($stepsRec as $stepKey => $stepValue) {
                        $stepsData[$key]['recommendations'][$stepKey] = $recommendations->find($stepValue)->getRecText();
                    }
                }
            }
        }

        // Build Recommendations
        if ($recipeData['recommendations'] != "") {
            $recipeRecommendations = explode(',', $recipeData['recommendations']);
    
            foreach ($recipeRecommendations as $key => $value) {
                $recommendationsData[$key] = $recommendations->find($value)->getRecText();
            }
        }

        // Form Building...
        $entityManager = $doctrine->getManager();

        // Ingredient Form
        $ingredient = new Ingredient();
        
        $ingredient->setName('');
        // $ingredient->setQty(0);
        // $ingredient->setUnit(0);
        
        $form['ingredient'] = $this->createForm(IngredientType::class, $ingredient);
        
        $form['ingredient']->handleRequest($request);
        if ($form['ingredient']->isSubmitted() && $form['ingredient']->isValid()) {

            $errors = $validator->validate($ingredient);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $recipe = $form['ingredient']->getData();

            $entityManager->persist($ingredient);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }



















        



        return $this->renderForm('recipe/show.html.twig', [
            'recipe'            => $recipeData,
            'ingredients'       => $ingredientData,
            'steps'             => $stepsData,
            'recommendations'   => $recommendationsData,
            'formIngredient'    => $form['ingredient'],
        ]);
    }
    
    #[Route('/recipe/new', name: 'app_recipe_new')]
    public function new(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        
        $recipe = new Recipe();
        $entityManager = $doctrine->getManager();
        
        $recipe->setStatus('');
        $recipe->setName('');
        $recipe->setDescription('');
        $recipe->setDisclaimer('');
        $recipe->setAuthor(1);
        $recipe->setCreated(new \DateTime('now'));
        $recipe->setUpdated(new \DateTime('now'));
        
        $form = $this->createForm(RecipeType::class, $recipe);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($recipe);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $recipe = $form->getData();

            $entityManager->persist($recipe);
            $entityManager->flush();

            $id = $recipe->getId();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        return $this->renderForm('recipe/new.html.twig', [
            'form' => $form,
        ]);
    }
}
