<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\IngredientUnit;
use App\Entity\Steps;
use App\Entity\Recommendations;
use App\Form\RecipeType;
use App\Form\IngredientType;
use App\Form\StepsType;
use App\Form\RecommendationsType;
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
        $recipeIngredients = $ingredient->findByRecipeId($id);

        if (!empty($recipeIngredients)){
            foreach ($recipeIngredients as $key => $value) {
                $ingredientUnit = $unit->find($value->getUnit());

                $ingredientData[ucwords($value->getName())] = [
                    'id'       => $value->getId(),
                    'qty'      => $value->getQty(),
                    'unitName' => $ingredientUnit->getUnitName(),
                    'unitAbb'  => $ingredientUnit->getUnitAbbreviation(),
                ];
            }
        }

        // Build Steps
        $recipeSteps = $steps->findByRecipeId($id);

        if (!empty($recipeSteps)){
            foreach ($recipeSteps as $key => $value) {
                $stepNumber = $value->getStep();

                $stepsData[$value->getStep()] = [
                    'id'   => $value->getId(),
                    'text' => $value->getText(),
                ];

                // Build Step Recommendations
                $recipeRecommendations = $recommendations->findByTypeAndId('step', $value->getId());

                if(!empty($recipeRecommendations)) {
                    foreach ($recipeRecommendations as $rkey => $rvalue) {
                        $stepsData[$value->getStep()]['recommendations'][$rkey] = $rvalue->getRecText();
                    }
                }
            }
        }

        // Build Recipe Recommendations
        $recipeRecommendations = $recommendations->findByTypeAndId('recipe', $id);

        if(!empty($recipeRecommendations)) {
            foreach ($recipeRecommendations as $key => $value) {
                $recommendationsData[] = $value->getRecText();
            }
        }

        // Create all required recipe forms
        $entityManager = $doctrine->getManager();

        // Ingredient Form
        $ingredient = new Ingredient();
        
        // $ingredient->setName('');
        // $ingredient->setQty(0);
        $ingredient->setRecipeId($id);
        
        $form['ingredient'] = $this->createForm(IngredientType::class, $ingredient);
        
        $form['ingredient']->handleRequest($request);
        if ($form['ingredient']->isSubmitted() && $form['ingredient']->isValid()) {

            $errors = $validator->validate($ingredient);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $ingredient = $form['ingredient']->getData();

            $entityManager->persist($ingredient);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        // Steps Form
        $steps = new Steps();
        $steps->setRecipeId($id);
        $steps->setStep($stepNumber+1);

        $form['steps'] = $this->createForm(StepsType::class, $steps);

        $form['steps']->handleRequest($request);
        if ($form['steps']->isSubmitted() && $form['steps']->isValid()) {

            $errors = $validator->validate($steps);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $steps = $form['steps']->getData();

            $entityManager->persist($steps);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        // Recipe Recommendation Form
        $recommendations = new Recommendations();
        $recommendations->setType('recipe');
        $recommendations->setTypeId($id);

        $form['recommendations'] = $this->createForm(RecommendationsType::class, $recommendations);

        $form['recommendations']->handleRequest($request);
        if ($form['recommendations']->isSubmitted() && $form['recommendations']->isValid()) {

            $errors = $validator->validate($recommendations);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $steps = $form['recommendations']->getData();

            $entityManager->persist($recommendations);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }
        
        return $this->renderForm('recipe/show.html.twig', [
            'recipe'              => $recipeData,
            'ingredients'         => $ingredientData,
            'steps'               => $stepsData,
            'recommendations'     => $recommendationsData,
            'formIngredient'      => $form['ingredient'],
            'formSteps'           => $form['steps'],
            'formRecommendations' => $form['recommendations'],
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
