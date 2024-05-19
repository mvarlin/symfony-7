<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class RecipeController extends AbstractController
{
    #[Route('/recipe', name: 'recipe.all')]
    public function index(Request $request, RecipeRepository $repository, EntityManagerInterface $em): Response
    {
        /* Listes de toutes les recettes */
        // $recipes = $repository->findAll();

        /* Listes des recettes filtrer part temps */
        $recipes = $repository->findByDurationTime(60);

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]); 
    }

    #[Route('/recipe/{slug}-{id}', name: 'recipe.info', requirements: ['id'=>'\d+', 'slug'=>'[a-z0-9-]+'])]
    public function show(RecipeRepository $repository, string $slug, int $id): Response
    {
        $recipe = $repository->find($id); // where id =
        if($recipe->getSlug() != $slug){
            return $this->redirectToRoute('recipe.show', ['slug'=>$recipe->getSlug(), 'id'=>$recipe->getId()]);
        }

        return $this->render('recipe/show.html.twig', [
            'recipes'=> $recipe
        ]);
    }

    #[Route('/recipe/nouvelle-recette', name: 'recipe.create')]
    public function add(Request $request, EntityManagerInterface $emi): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe, [
            'validator_type' => 'Ajouter']); //création dur formulaire via Entity
        $form->handleRequest($request);  //Récupération des valeurs du formulaire
        
        if ($form->isSubmitted() && $form->isValid()) {
            if(is_null($recipe->getSlug())){
                $slugger = new AsciiSlugger();
                $slug = $slugger->slug($recipe->getTitle());
                $recipe->setSlug(strtolower($slug));
            }
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $emi->persist($recipe);
            $emi->flush(); //insert en base
            $this->addFlash('success', 'Recette Ajouté');
            return $this->redirectToRoute('recipe.all'); //retour au menu des recettes
        }

        return $this->render('recipe/add.html.twig',[
            'form' => $form,
        ]);
    }

    #[Route('/recipe/{id}/edit', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RecipeRepository $repository, EntityManagerInterface $emi, int $id): Response
    {
        $recipe = $repository->find($id); // where id =
        $form = $this->createForm(RecipeType::class, $recipe); //création dur formulaire via Entity
        $form->handleRequest($request);  //Récupération des valeurs du formulaire
        if ($form->isSubmitted() && $form->isValid()) { 
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $emi->flush(); //insert en base
            $this->addFlash('success', 'Recette modifié');
            return $this->redirectToRoute('recipe.all'); //retour au menu des recettes
        }

        return $this->render('recipe/edit.html.twig',[
            'recipes' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/recipe/{id}', name: 'recipe.delete', methods: ['DELETE'])]
    public function delete(Recipe $recipe, EntityManagerInterface $emi): Response
    {
        // $recipe = $repository->find($id); // where id =
        // if ($form->isSubmitted() && $form->isValid()) { //condition si la validation est OK 
            $emi->remove($recipe); //insert en base
            $emi->flush(); //insert en base
            $this->addFlash('success', 'Recette supprimé');
            return $this->redirectToRoute('recipe.all'); //retour au menu des recettes
        // }
    }
    
}
