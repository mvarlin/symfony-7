<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FirstController extends AbstractController
{
    #[Route('/first', name: 'first.list')]
    public function index(Request $request): Response
    {
        return $this->render('first/index.html.twig');
        // return new Response('First');
    }

    #[Route('/first/{name}-{id}', name: 'first.info')]
    public function select(Request $request, string $name, int $id): Response
    {
        return $this->json([
            'Name' => $name,
            'Id' => $id
        ]);
    }
}
