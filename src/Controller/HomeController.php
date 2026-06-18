<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $projects = [
            [
                'title' => 'Project Alpha',
                'description' => 'A modern web application built with cutting-edge technologies.',
                'tags' => ['React', 'TypeScript', 'Node.js'],
                'link' => '#'
            ],
            [
                'title' => 'Project Beta',
                'description' => 'Data visualization platform for real-time analytics.',
                'tags' => ['Vue.js', 'D3.js', 'Python'],
                'link' => '#'
            ],
            [
                'title' => 'Project Gamma',
                'description' => 'Enterprise-grade API management system.',
                'tags' => ['Symfony', 'Go', 'Kubernetes'],
                'link' => '#'
            ]
        ];

        return $this->render('home/index.html.twig', [
            'projects' => $projects
        ]);
    }
}
