<?php

namespace App\Controller;

use App\Entity\IngredientUnit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class IngredientUnitController extends AbstractController
{
    #[Route('/ingredient/unit', name: 'app_ingredient_unit')]
    public function index(): Response
    {
        return $this->render('ingredient_unit/index.html.twig', [
            'test' => 'IngredientUnitController',
        ]);
    }

    #[Route('/ingredient/unit/list', name: 'app_ingredient_unit_list')]
    public function list(ManagerRegistry $doctrine): Response
    {
        
        $units = $doctrine->getRepository(IngredientUnit::class)->list();

        if (!$units) {
            throw $this->createNotFoundException(
                'No ingredient units found'
            );
        }

        $return = [];
        foreach($units as $unit){
            $return[$unit->getId()] = [
                'unitName' => $unit->getUnitName(),
                'unitAbb'  => $unit->getUnitAbbreviation()
            ];
        }

        return new Response(json_encode($return));
    }
}
